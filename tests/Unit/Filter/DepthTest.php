<?php

namespace Tests\Unit\Filter;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Support\Service\Scan\Filter\Depth;
use App\Page;
use App\Scan;
use App\Site;
use App\Support\Value\Url;
use App\Support\Value\Throttle;
use Ramsey\Uuid\Uuid;
use Mockery;
use InvalidArgumentException;

class DepthTest extends TestCase
{
    use RefreshDatabase;

    public function setup() : void
    {
        parent::setup();
        $this->url = new Url('http://localhost');

        $this->site = Site::create([
            'url' => new Url('http://localhost'),
            'throttle' => new Throttle('default:default'),
            'validation_code' => Uuid::Uuid4()
        ]);

        $this->scan = Scan::create([
            'site_id' => $this->site->id,
            'status' => 'processing',
        ]);

        $page = Page::create([
            'scan_id' => $this->scan->id,
            'url' => $this->url,
            'method' => 'get',
            'depth' => 3,
        ]);

        $page = Page::create([
            'scan_id' => $this->scan->id,
            'url' => new Url('http://localhost/page2'),
            'method' => 'get',
            'depth' => 3,
        ]);

        $page = Page::create([
            'scan_id' => $this->scan->id,
            'url' => new Url('http://localhost/page3'),
            'method' => 'get',
            'depth' => 3,
        ]);

        $page = Page::create([
            'scan_id' => $this->scan->id,
            'url' => new Url('http://localhost/page4'),
            'method' => 'get',
            'depth' => 3,
        ]);

    }

    /** @test */
    public function first_page_returned_when_not_filtered()
    {
        // first prove that without the filter, the first page is returned;
        $scan = $this->scan;
        $query = Page::where('scan_id',$scan->id);
        $page = $query->first();
        $this->assertEquals($this->url, $page->url);
    }

    /** @test */
    public function none_returned_when_depth_is_bigger_than_max()
    {
        $scan = $this->scan;
        $query = Page::where('scan_id',$scan->id);
        $filter = new Depth(app('validator'));
        $response = $filter->filter($query, $scan, ['max' => 2]);
        $page = $query->first();
        $this->assertEquals(null, $page);
    }

    /** @test */
    public function first_returned_when_depth_is_less_than_max()
    {
        $scan = $this->scan;
        $query = Page::where('scan_id',$scan->id);
        $filter = new Depth(app('validator'));
        $response = $filter->filter($query, $scan, ['max' => 5]);
        $page = $query->first();
        $this->assertEquals($this->url, $page->url);
    }

    /** @test */
    public function throws_exception_if_max_not_set()
    {
        $scan = Mockery::mock(Scan::class);
        $query = Page::where('scan_id',1);
        $filter = new Depth(app('validator'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Requires parameter "max" to be set');
        $response = $filter->filter($query, $scan, null);
    }

    /** @test */
    public function throws_exception_if_max_not_numeric()
    {
        $scan = Mockery::mock(Scan::class);
        $query = Page::where('scan_id',1);
        $filter = new Depth(app('validator'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Requires parameter "max" to be an integer');
        $response = $filter->filter($query, $scan, ['max'=>'b']);
    }

    /** @test */
    public function throws_exception_if_max_not_integer()
    {
        $scan = Mockery::mock(Scan::class);
        $query = Page::where('scan_id',1);
        $filter = new Depth(app('validator'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Requires parameter "max" to be an integer');
        $response = $filter->filter($query, $scan, ['max'=>99.2]);
    }

    /** @test */
    public function throws_exception_if_max_less_than_1()
    {
        $scan = Mockery::mock(Scan::class);
        $query = Page::where('scan_id',1);
        $filter = new Depth(app('validator'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Requires parameter "max" to be at least 1');
        $response = $filter->filter($query, $scan, ['max'=>0]);
    }

    /** @test */
    public function check_name_and_description_valid()
    {
        $filter = new Depth(app('validator'));
        $this->assertEquals('Limit depth of scan', $filter->getName());

        $filterRecord = new \App\Filter;
        $filterRecord->parameters = ['max' => 1];
        $this->assertEquals('Limit depth of scan to 1 level', $filter->getDescription($filterRecord));

        $filterRecord->parameters = ['max' => 2];
        $this->assertEquals('Limit depth of scan to 2 levels', $filter->getDescription($filterRecord));

        $this->assertEquals('Limit depth of scan to <em>"not-set"</em> levels', $filter->getDescription(null));
    }

    /** @test */
    public function check_validation_for_input_parms()
    {
        $filter = new Depth(app('validator'));
        $validator = $filter->getInputValidator([]);
        $this->assertTrue($validator->fails());
        $this->assertEquals('The depth field is required.', $validator->errors()->messages()['max'][0]);

        $validator = $filter->getInputValidator(['max' => 'a']);
        $this->assertTrue($validator->fails());
        $this->assertEquals('The depth must be an integer.', $validator->errors()->messages()['max'][0]);

        $validator = $filter->getInputValidator(['max' => -1]);
        $this->assertTrue($validator->fails());
        $this->assertEquals('The depth must be at least 1.', $validator->errors()->messages()['max'][0]);
    }
}
