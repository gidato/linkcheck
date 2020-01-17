<?php

namespace Tests\Unit\Filter;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Support\Service\Scan\Filter\InternalOnly;
use App\Page;
use App\Scan;
use App\Site;
use App\Support\Value\Url;
use App\Support\Value\Throttle;
use Ramsey\Uuid\Uuid;

use Mockery;

class InternalOnlyTest extends TestCase
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
            'depth' => 1,
        ]);
        $page->checked = true;
        $page->save();

        $page = Page::create([
            'scan_id' => $this->scan->id,
            'url' => new Url('http://localhost/page2'),
            'method' => 'get',
            'depth' => 2,
        ]);
        $page->checked = true;
        $page->save();

        $page = Page::create([
            'scan_id' => $this->scan->id,
            'url' => new Url('http://example.com'),
            'method' => 'get',
            'depth' => 2,
        ]);

        $page = Page::create([
            'scan_id' => $this->scan->id,
            'url' => new Url('http://localhost/page3'),
            'method' => 'get',
            'depth' => 2,
        ]);

    }

    /** @test */
    public function first_page_returned_when_not_filtered()
    {
        // first prove that without the filter, the first page is returned;
        $scan = $this->scan;
        $query = Page::where('scan_id',$scan->id)->where('checked',0);
        $page = $query->first();
        $this->assertEquals(new Url('http://example.com'), $page->url);
    }

    /** @test */
    public function none_returned_when_depth_is_bigger_than_max()
    {
        $scan = $this->scan;
        $query = Page::where('scan_id',$scan->id)->where('checked',0);
        $filter = new InternalOnly(app('validator'));
        $response = $filter->filter($query, $scan, null);
        $page = $query->first();
        $this->assertEquals(new Url('http://localhost/page3'), $page->url);
    }

    /** @test */
    public function check_name_and_description_valid()
    {
        $filter = new InternalOnly(app('validator'));
        $this->assertEquals('Only check internal pages', $filter->getName());

        $filterRecord = new \App\Filter;
        $this->assertEquals('Limit scan to internal pages only', $filter->getDescription($filterRecord));
        $this->assertEquals('Limit scan to internal pages only', $filter->getDescription(null));
    }

    /** @test */
    public function check_validation_for_input_parms()
    {
        $filter = new InternalOnly(app('validator'));
        $validator = $filter->getInputValidator([]);
        $this->assertFalse($validator->fails());
    }
}
