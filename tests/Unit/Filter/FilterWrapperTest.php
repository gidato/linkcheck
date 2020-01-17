<?php

namespace Tests\Unit\Filter;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Support\Service\Scan\Filter\ScanFilterInterface;
use App\Support\Service\Scan\Filter\FilterWrapper;
use App\Site;
use App\Filter;
use App\Support\Value\Url;
use App\Support\Value\Throttle;
use Ramsey\Uuid\Uuid;
use Illuminate\Validation\Validator;
use Mockery;
use InvalidArgumentException;

class FilterWrapperTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function with_filter_and_handler()
    {
        $filter = Mockery::mock(Filter::class);
        $filter->shouldReceive('getAttribute')->with('on')->andReturn(1);
        $filter->shouldReceive('getAttribute')->with('parameters')->andReturn(['parm' => 'val']);


        $validator = Mockery::mock(Validator::class);

        $handler = Mockery::mock(ScanFilterInterface::class);
        $handler->shouldReceive('getName')->andReturn('handler-name');
        $handler->shouldReceive('getDescription')->with($filter)->andReturn('handler-description');
        $handler->shouldReceive('getInputValidator')->with(['a'=>1])->andReturn($validator);

        $filterWrap = new FilterWrapper('key-val', $handler, $filter);

        $this->assertEquals(true,$filterWrap->on);
        $this->assertEquals('handler-description', $filterWrap->description);
        $this->assertEquals('handler-name', $filterWrap->name);
        $this->assertEquals('key-val', $filterWrap->key);
        $this->assertEquals(['parm'=>'val'], $filterWrap->parameters);

        $this->assertEquals($validator, $filterWrap->getValidator(['a'=>1]));

        // update values with existing filter / turn on
        $site = Mockery::mock(Site::class);
        $filter->shouldReceive('setAttribute')->with('on', true);
        $filter->shouldReceive('setAttribute')->with('parameters', ['a'=>1]);
        $filter->shouldReceive('save');
        $filterWrap->updateFilterValues($site, ['a'=>1]);

        // turn off filter with existing filter
        $filter->shouldReceive('setAttribute')->with('on', false);
        $filter->shouldReceive('save');
        $filterWrap->turnFilterOff();
    }

    /** @test */
    public function with_handler_only()
    {
        $validator = Mockery::mock(Validator::class);

        $handler = Mockery::mock(ScanFilterInterface::class);
        $handler->shouldReceive('getName')->andReturn('handler-name');
        $handler->shouldReceive('getDescription')->with(null)->andReturn('handler-description');
        $handler->shouldReceive('getInputValidator')->with(['a'=>1])->andReturn($validator);

        $filterWrap = new FilterWrapper('key-val', $handler, null);

        $this->assertEquals(false, $filterWrap->on);
        $this->assertEquals('handler-description', $filterWrap->description);
        $this->assertEquals('handler-name', $filterWrap->name);
        $this->assertEquals('key-val', $filterWrap->key);
        $this->assertEquals([], $filterWrap->parameters);

        $this->assertEquals($validator, $filterWrap->getValidator(['a'=>1]));

        $site = Site::create([
            'url' => new Url('http://localhost'),
            'throttle' => new Throttle('default:default'),
            'validation_code' => Uuid::Uuid4()
        ]);

        // update values WITHOUT existing filter / turn on
        $filterWrap->updateFilterValues($site, ['a'=>1]);
        $site->refresh();
        $this->assertEquals(1, $site->filters->count());
        $filter = $site->filters[0];
        $this->assertEquals(1, $filter->on);
        $this->assertEquals('key-val', $filter->key);
        $this->assertEquals(['a' => 1], $filter->parameters);
    }

    /** @test */
    public function turn_off_with_handler_only_nothing_should_be_done()
    {
        // turn off filter WITHOUT existing filter
        $handler = Mockery::mock(ScanFilterInterface::class);
        $filterWrap = new FilterWrapper('key-val', $handler, null);
        $filterWrap->turnFilterOff();
        $this->assertEquals(0,Filter::count()); // no filters are added
    }

}
