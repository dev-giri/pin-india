<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PinIndia\Models\Circle;
use PinIndia\Models\Region;
use PinIndia\Models\Division;
use PinIndia\Models\Pincode;
use Tests\TestCase;

class DivisionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_table_name()
    {
        $division = new Division();
        $this->assertEquals(config('pinindia.table_prefix') . '_divisions', $division->getTable());
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $division = new Division();
        $this->assertEquals(['region_id', 'name'], $division->getFillable());
    }

    /** @test */
    public function it_has_no_timestamps()
    {
        $division = new Division();
        $this->assertFalse($division->timestamps);
    }

    /** @test */
    public function it_belongs_to_region()
    {
        $division = new Division();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $division->region());
        $this->assertEquals('region_id', $division->region()->getForeignKeyName());
    }

    /** @test */
    public function it_has_pincodes_relationship()
    {
        $division = new Division();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $division->pincodes());
        $this->assertEquals('division_id', $division->pincodes()->getForeignKeyName());
    }

    /** @test */
    public function it_can_create_division()
    {
        $circle = Circle::create([
            'name' => 'Test Circle'
        ]);

        $region = Region::create([
            'circle_id' => $circle->id,
            'name' => 'Test Region'
        ]);

        $division = Division::create([
            'region_id' => $region->id,
            'name' => 'Test Division'
        ]);

        $this->assertDatabaseHas(config('pinindia.table_prefix') . '_divisions', [
            'region_id' => $region->id,
            'name' => 'Test Division'
        ]);
    }

    /** @test */
    public function it_can_retrieve_region()
    {
        $circle = Circle::create([
            'name' => 'Test Circle'
        ]);

        $region = Region::create([
            'circle_id' => $circle->id,
            'name' => 'Test Region'
        ]);

        $division = Division::create([
            'region_id' => $region->id,
            'name' => 'Test Division'
        ]);

        $this->assertEquals('Test Region', $division->region->name);
    }

    /** @test */
    public function it_can_retrieve_pincodes()
    {
        $circle = Circle::create([
            'name' => 'Test Circle'
        ]);

        $region = Region::create([
            'circle_id' => $circle->id,
            'name' => 'Test Region'
        ]);

        $division = Division::create([
            'region_id' => $region->id,
            'name' => 'Test Division'
        ]);

        $state = \PinIndia\Models\State::create(['name' => 'Test State']);
        $district = \PinIndia\Models\District::create(['state_id' => $state->id, 'name' => 'Test District']);

        $pincode = Pincode::create([
            'pincode' => 123456,
            'district_id' => $district->id,
            'division_id' => $division->id
        ]);

        $this->assertCount(1, $division->pincodes);
        $this->assertEquals(123456, $division->pincodes->first()->pincode);
    }
}
