<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Filter;
use App\Scan;
use App\Support\Service\Scan\Filter\FilterManager;
use App\Support\Service\Scan\Filter\ScanFilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Psr\Http\Message\ResponseInterface as HttpResponse;
use InvalidArgumentException;

class FilterManagerTest extends TestCase
{
    /** @test */
    public function filters_should_implement_Interface()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Filters must implement ' . ScanFilterInterface::class . '. string given');
        $filterManager = new FilterManager([
            'depth' => 'none'
        ]);
    }

    /** @test */
    public function filter_keys_should_be_set()
    {
        $depth = Mockery::mock(ScanFilterInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Key for filter must be set');
        $filterManager = new FilterManager([
            '' => $depth
        ]);
    }

    /** @test */
    public function filter_keys_should_be_string()
    {
        $depth = Mockery::mock(ScanFilterInterface::class);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Key for filter must be a string');
        $filterManager = new FilterManager([
            99 => $depth
        ]);
    }

    /** @test */
    public function should_erturn_query_if_filter_is_not_on()
    {
        $scan = Mockery::mock(Scan::class);
        $depth = Mockery::mock(ScanFilterInterface::class);
        $filter = Mockery::mock(Filter::class);
        $filter->shouldReceive('getAttribute')->with('on')->andReturn(0);
        $query = Mockery::mock(Builder::class);

        $filterManager = new FilterManager(['depth' => $depth]);
        $response = $filterManager->filter($query, $scan, $filter);
        $this->assertSame($query, $response);
    }

    /** @test */
    public function should_throw_exception_content_if_no_processor_available_and_filter_is_on()
    {
        $scan = Mockery::mock(Scan::class);
        $depth = Mockery::mock(ScanFilterInterface::class);
        $filter = Mockery::mock(Filter::class);
        $filter->shouldReceive('getAttribute')->with('on')->andReturn(1);
        $filter->shouldReceive('getAttribute')->with('key')->andReturn('other');
        $query = Mockery::mock(Builder::class);

        $filterManager = new FilterManager(['depth' => $depth]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown filter key "other"');

        $response = $filterManager->filter($query, $scan, $filter);
    }

    /** @test */
    public function should_pass_query_and_parameters_to_filter()
    {
        $scan = Mockery::mock(Scan::class);
        $filter = Mockery::mock(Filter::class);
        $filter->shouldReceive('getAttribute')->with('on')->andReturn(1);
        $filter->shouldReceive('getAttribute')->with('key')->andReturn('depth');
        $parms = ['a'=>'b'];
        $filter->shouldReceive('getAttribute')->with('parameters')->andReturn($parms);
        $query = Mockery::mock(Builder::class);
        $newQuery = Mockery::mock(Builder::class);

        $depth = Mockery::mock(ScanFilterInterface::class);
        $depth->shouldReceive('filter')->with($query, $scan, $parms)->once()->andReturn($newQuery);

        $filterManager = new FilterManager(['depth' => $depth]);

        $response = $filterManager->filter($query, $scan, $filter);

        $this->assertSame($newQuery, $response);
    }

}
