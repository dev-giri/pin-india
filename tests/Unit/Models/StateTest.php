<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PinIndia\Models\State;
use PinIndia\Models\District;
use Tests\TestCase;

class StateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_table_name()
    {
        $state = new State();
        $this->assertEquals(config('pinindia.table_prefix') . '_states', $state->getTable());
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $state = new State();
        $this->assertEquals(['name'], $state->getFillable());
    }

    /** @test */
    public function it_has_no_timestamps()
    {
        $state = new State();
        $this->assertFalse($state->timestamps);
    }

    /** @test */
    public function it_has_districts_relationship()
    {
        $state = new State();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $state->districts());
        $this->assertEquals('state_id', $state->districts()->getForeignKeyName());
    }

    /** @test */
    public function it_can_create_state()
    {
        $state = State::create([
            'name' => 'Test State'
        ]);

        $this->assertDatabaseHas(config('pinindia.table_prefix') . '_states', [
            'name' => 'Test State'
        ]);
    }

    /** @test */
    public function it_can_retrieve_districts()
    {
        $state = State::create([
            'name' => 'Test State'
        ]);

        $district = District::create([
            'state_id' => $state->id,
            'name' => 'Test District'
        ]);

        $this->assertCount(1, $state->districts);
        $this->assertEquals('Test District', $state->districts->first()->name);
    }
}
