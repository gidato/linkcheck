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

class NewScansTest extends TestCase
{
    use RefreshDatabase;

    private $site;

    protected function setUp() : void
    {
        parent::setUp();
        User::create([
            'name'=>'David',
            'email'=> 'test@example.com',
            'password'=>'anything'
        ]);

        $this->site = Site::create([
            'url' => new Url('http://localhost'),
            'throttle' => new Throttle('default:default'),
            'validation_code' => Uuid::Uuid4()
        ]);

        $filter = new Filter;
        $filter->filterable_type = Site::class;
        $filter->filterable_id = $this->site->id;
        $filter->key = 'internal-only';
        $filter->on = true;
        $filter->save();
    }

    /** @test */
    public function a_new_scan_can_be_triggered_for_site()
    {
        $this->withoutExceptionHandling();
        Queue::fake();

        $user = User::find(1);
        $this->be($user);

        $response = $this->post('/scans', ['site_id' => $this->site->id]);
        $response->assertRedirect();

        $this->assertCount(1, Scan::all());
        $this->assertCount(1, Page::all());

        /* get the new scan created to make sure it has been sent to job */
        $scan = (Scan::all())[0];
        $this->assertEquals(1, $scan->site->id);
        $this->assertEquals('queued', $scan->status);

        /* get the only page in the scan to cehck set up OK */
        $page = (Page::all())[0];
        $this->assertEquals($scan->id, $page->scan->id);
        $this->assertEquals('http://localhost/', (string) $page->url);
        $this->assertEquals('get', $page->method);
        $this->assertFalse((bool) $page->checked);
        $this->assertFalse((bool) $page->is_external);

        $filter = $scan->filters[0];
        $this->assertEquals($scan->id, $filter->filterable_id);
        $this->assertEquals(Scan::class, $filter->filterable_type);
        $this->assertEquals('internal-only', $filter->key);
        $this->assertEquals(1, $filter->on);
        $this->assertEmpty($filter->parameters);

        Queue::assertPushed(ProcessScan::class, function ($job) use ($scan) {
            /* accessing a private vaule within job to make sure correct scan was passed in */
            $reflectionClass = new \ReflectionClass(ProcessScan::class);
            $reflectionProperty = $reflectionClass->getProperty('scan');
            $reflectionProperty->setAccessible(true);
            return $reflectionProperty->getValue($job)->id = $scan->id;
        });



    }
}
