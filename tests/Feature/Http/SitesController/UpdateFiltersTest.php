<?php

namespace Tests\Feature\Http\OwnersController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateFiltersTest extends TestCase
{
    use RefreshDatabase;

    private $site;

    public function setup() : void
    {
        parent::setup();

        $this->user = factory(\App\User::class)->create();
        $this->be($this->user);

        $this->site = factory(\App\Site::class)->create();
    }

    /** @test */
    public function load_edit_form()
    {
        $response = $this->get('sites/'. $this->site->id.'/filters');
        $response->assertOk();
        $response->assertViewIs('sites.settings.edit-filters');
        $response->assertSeeText('Only check internal pages');
        $response->assertSeeText('Limit depth of scan');
        $response->assertSeeText('Limit number of pages checked');
    }

    /** @test */
    public function store_with_no_data_into_db_with_no_filters()
    {
        $response = $this->patch('sites/'. $this->site->id.'/filters', []);
        $response->assertRedirect();
        $site = \App\Site::find($this->site->id);
        $this->assertEquals(0, \App\Filter::count());
    }

    /** @test */
    public function store_valid_data_into_db_with_no_existing_filters()
    {
        $response = $this->patch('sites/'. $this->site->id.'/filters', [
            'on'=> [
                'internal-only' => 'internal-only',
                'max-depth' => 'max-depth',
                'max-pages' => 'max-pages',
            ],
            'max-depth' => [
                'max' => 5,
            ],
            'max-pages' => [
                'max' => 5000,
            ]
        ]);
        $response->assertRedirect('sites/'. $this->site->id);
        $site = \App\Site::find($this->site->id);
        $this->assertEquals(3, \App\Filter::count());
        \App\Filter::all()->each(function($filter) {
            $this->assertTrue((bool) $filter->on);
        });
    }

    /** @test */
    public function update_valid_data_into_existing_filters()
    {
        $filter = new \App\Filter;
        $filter->filterable_type = \App\Site::class;
        $filter->filterable_id = $this->site->id;
        $filter->key = 'internal-only';
        $filter->parameters = [];
        $filter->on = false;
        $filter->save();

        $filter = new \App\Filter;
        $filter->filterable_type = \App\Site::class;
        $filter->filterable_id = $this->site->id;
        $filter->key = 'max-depth';
        $filter->parameters = [];
        $filter->on = false;
        $filter->save();

        $filter = new \App\Filter;
        $filter->filterable_type = \App\Site::class;
        $filter->filterable_id = $this->site->id;
        $filter->key = 'max-pages';
        $filter->parameters = [];
        $filter->on = false;
        $filter->save();

        $response = $this->patch('sites/'. $this->site->id.'/filters', [
            'on'=> [
                'internal-only' => 'internal-only',
                'max-depth' => 'max-depth',
                'max-pages' => 'max-pages',
            ],
            'max-depth' => [
                'max' => 5,
            ],
            'max-pages' => [
                'max' => 5000,
            ]
        ]);
        $response->assertRedirect('sites/'. $this->site->id);
        $site = \App\Site::find($this->site->id);
        $this->assertEquals(3, \App\Filter::count());
        \App\Filter::all()->each(function($filter) {
            $this->assertTrue((bool) $filter->on);
        });
    }

    /** @test */
    public function turn_off_existing_filters()
    {
        $filter = new \App\Filter;
        $filter->filterable_type = \App\Site::class;
        $filter->filterable_id = $this->site->id;
        $filter->key = 'internal-only';
        $filter->parameters = [];
        $filter->on = true;
        $filter->save();

        $filter = new \App\Filter;
        $filter->filterable_type = \App\Site::class;
        $filter->filterable_id = $this->site->id;
        $filter->key = 'max-depth';
        $filter->parameters = [];
        $filter->on = true;
        $filter->save();

        $filter = new \App\Filter;
        $filter->filterable_type = \App\Site::class;
        $filter->filterable_id = $this->site->id;
        $filter->key = 'max-pages';
        $filter->parameters = [];
        $filter->on = true;
        $filter->save();

        $response = $this->patch('sites/'. $this->site->id.'/filters', []);
        $response->assertRedirect('sites/'. $this->site->id);
        $site = \App\Site::find($this->site->id);
        $this->assertEquals(3, \App\Filter::count());
        \App\Filter::all()->each(function($filter) {
            $this->assertFalse((bool) $filter->on);
        });
    }

    /** @test */
    public function turn_off_non_existant_filters()
    {
        $response = $this->patch('sites/'. $this->site->id.'/filters', []);
        $response->assertRedirect('sites/'. $this->site->id);
        $site = \App\Site::find($this->site->id);
        $this->assertEquals(0, \App\Filter::count());
    }

    /** @test */
    public function pass_data_with_errors_when_activated()
    {
        $response = $this->patch('sites/'. $this->site->id.'/filters', [
            'on'=> [
                'internal-only' => 'internal-only',
                'max-depth' => 'max-depth',
                'max-pages' => 'max-pages',
            ],
            'max-depth' => [
                'max' => -1,
            ],
            'max-pages' => [
                'max' => -1,
            ]
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors(['max'], null, 'maxDepth');
        $response->assertSessionHasErrors(['max'], null, 'maxPages');
    }

    /** @test */
    public function pass_data_with_errors_when_not_activated()
    {
        $response = $this->patch('sites/'. $this->site->id.'/filters', [
            'on'=> [
                'internal-only' => 'internal-only',
            ],
            'max-depth' => [
                'max' => -1,
            ],
            'max-pages' => [
                'max' => -1,
            ]
        ]);
        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors(['max'], null, 'maxDepth');
        $response->assertSessionDoesntHaveErrors(['max'], null, 'maxPages');
    }
}
