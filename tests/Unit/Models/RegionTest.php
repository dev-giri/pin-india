<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PinIndia\Models\Circle;
use PinIndia\Models\Region;
use PinIndia\Models\Division;
use Tests\TestCase;

class RegionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_table_name()
    {
        $region = new Region();
        $this->assertEquals(config('pinindia.table_prefix') . '_regions', $region->getTable());
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $region = new Region();
        $this->assertEquals(['circle_id', 'name'], $region->getFillable());
    }

    /** @test */
    public function it_has_no_timestamps()
    {
        $region = new Region();
        $this->assertFalse($region->timestamps);
    }

    /** @test */
    public function it_belongs_to_circle()
    {
        $region = new Region();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $region->circle());
        $this->assertEquals('circle_id', $region->circle()->getForeignKeyName());
    }

    /** @test */
    public function it_has_divisions_relationship()
    {
        $region = new Region();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $region->divisions());
        $this->assertEquals('region_id', $region->divisions()->getForeignKeyName());
    }

    /** @test */
    public function it_can_create_region()
    {
        $circle = Circle::create([
            'name' => 'Test Circle'
        ]);

        $region = Region::create([
            'circle_id' => $circle->id,
            'name' => 'Test Region'
        ]);

        $this->assertDatabaseHas(config('pinindia.table_prefix') . '_regions', [
            'circle_id' => $circle->id,
            'name' => 'Test Region'
        ]);
    }

    /** @test */
    public function it_can_retrieve_circle()
    {
        $circle = Circle::create([
            'name' => 'Test Circle'
        ]);

        $region = Region::create([
            'circle_id' => $circle->id,
            'name' => 'Test Region'
        ]);

        $this->assertEquals('Test Circle', $region->circle->name);
    }

    /** @test */
    public function it_can_retrieve_divisions()
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

        $this->assertCount(1, $region->divisions);
        $this->assertEquals('Test Division', $region->divisions->first()->name);
    }
}
