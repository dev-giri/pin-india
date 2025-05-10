<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PinIndia\Models\State;
use PinIndia\Models\District;
use PinIndia\Models\Pincode;
use Tests\TestCase;

class DistrictTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_table_name()
    {
        $district = new District();
        $this->assertEquals(config('pinindia.table_prefix') . '_districts', $district->getTable());
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $district = new District();
        $this->assertEquals(['state_id', 'name'], $district->getFillable());
    }

    /** @test */
    public function it_has_no_timestamps()
    {
        $district = new District();
        $this->assertFalse($district->timestamps);
    }

    /** @test */
    public function it_belongs_to_state()
    {
        $district = new District();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $district->state());
        $this->assertEquals('state_id', $district->state()->getForeignKeyName());
    }

    /** @test */
    public function it_has_pincodes_relationship()
    {
        $district = new District();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $district->pincodes());
        $this->assertEquals('district_id', $district->pincodes()->getForeignKeyName());
    }

    /** @test */
    public function it_can_create_district()
    {
        $state = State::create([
            'name' => 'Test State'
        ]);

        $district = District::create([
            'state_id' => $state->id,
            'name' => 'Test District'
        ]);

        $this->assertDatabaseHas(config('pinindia.table_prefix') . '_districts', [
            'state_id' => $state->id,
            'name' => 'Test District'
        ]);
    }

    /** @test */
    public function it_can_retrieve_state()
    {
        $state = State::create([
            'name' => 'Test State'
        ]);

        $district = District::create([
            'state_id' => $state->id,
            'name' => 'Test District'
        ]);

        $this->assertEquals('Test State', $district->state->name);
    }

    /** @test */
    public function it_can_retrieve_pincodes()
    {
        $state = State::create([
            'name' => 'Test State'
        ]);

        $district = District::create([
            'state_id' => $state->id,
            'name' => 'Test District'
        ]);

        // Create a division for the pincode
        $circle = \PinIndia\Models\Circle::create(['name' => 'Test Circle']);
        $region = \PinIndia\Models\Region::create(['circle_id' => $circle->id, 'name' => 'Test Region']);
        $division = \PinIndia\Models\Division::create(['region_id' => $region->id, 'name' => 'Test Division']);

        $pincode = Pincode::create([
            'pincode' => 123456,
            'district_id' => $district->id,
            'division_id' => $division->id
        ]);

        $this->assertCount(1, $district->pincodes);
        $this->assertEquals(123456, $district->pincodes->first()->pincode);
    }
}
