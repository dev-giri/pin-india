<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PinIndia\Models\Circle;
use PinIndia\Models\Region;
use Tests\TestCase;

class CircleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_table_name()
    {
        $circle = new Circle();
        $this->assertEquals(config('pinindia.table_prefix') . '_circles', $circle->getTable());
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $circle = new Circle();
        $this->assertEquals(['name'], $circle->getFillable());
    }

    /** @test */
    public function it_has_no_timestamps()
    {
        $circle = new Circle();
        $this->assertFalse($circle->timestamps);
    }

    /** @test */
    public function it_has_regions_relationship()
    {
        $circle = new Circle();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $circle->regions());
        $this->assertEquals('circle_id', $circle->regions()->getForeignKeyName());
    }

    /** @test */
    public function it_can_create_circle()
    {
        $circle = Circle::create([
            'name' => 'Test Circle'
        ]);

        $this->assertDatabaseHas(config('pinindia.table_prefix') . '_circles', [
            'name' => 'Test Circle'
        ]);
    }

    /** @test */
    public function it_can_retrieve_regions()
    {
        $circle = Circle::create([
            'name' => 'Test Circle'
        ]);

        $region = Region::create([
            'circle_id' => $circle->id,
            'name' => 'Test Region'
        ]);

        $this->assertCount(1, $circle->regions);
        $this->assertEquals('Test Region', $circle->regions->first()->name);
    }
}
