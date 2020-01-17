<?php

namespace Tests\Feature\Http\ScanController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\User;
use App\Scan;
use App\Page;
use App\Site;
use App\Filter;
use App\Jobs\ProcessScan;
use App\Support\Value\Url;
use App\Support\Value\Throttle;
use Illuminate\Support\Facades\Queue;
use Ramsey\Uuid\Uuid;

class RescanErrorsTest extends TestCase
{
    use RefreshDatabase;

    private $scan;

    protected function setUp() : void
    {
        parent::setUp();
        factory(\App\User::class)->create();

        $site = factory(\App\Site::class)->create();

        $this->scan = factory(\App\Scan::class)->create(['site_id' => $site->id]);
        factory(\App\Page::class,10)->create(['scan_id' => $this->scan->id]);
        factory(\App\Page::class)->create(['scan_id' => $this->scan->id, 'status_code' => 404]);
        factory(\App\Page::class,10)->create(['scan_id' => $this->scan->id]);
    }

    /** @test */
    public function a_rescan_errored_pages_can_be_triggered_for_site()
    {
        $this->withoutExceptionHandling();
        Queue::fake();

        $user = User::first();
        $this->be($user);

        $response = $this->post('/scans/'.$this->scan->id.'/rescan-errors');
        $response->assertRedirect();

        $this->assertCount(2, Scan::all());
        $scan = Scan::where('id','<>',$this->scan->id)->first();
        $this->assertEquals(21, $scan->pages->count());
        $this->assertEquals(20, $scan->pages->where('checked',1)->count()); // one has been set to not checked
        $this->assertEquals(1, $scan->pages->where('checked',0)->count()); // one has been set to not checked

        Queue::assertPushed(ProcessScan::class, function ($job) use ($scan) {
            /* accessing a private vaule within job to make sure correct scan was passed in */
            $reflectionClass = new \ReflectionClass(ProcessScan::class);
            $reflectionProperty = $reflectionClass->getProperty('scan');
            $reflectionProperty->setAccessible(true);
            return $reflectionProperty->getValue($job)->id = $scan->id;
        });



    }
}
