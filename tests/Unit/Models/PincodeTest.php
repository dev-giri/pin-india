<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PinIndia\Models\State;
use PinIndia\Models\District;
use PinIndia\Models\Circle;
use PinIndia\Models\Region;
use PinIndia\Models\Division;
use PinIndia\Models\Pincode;
use PinIndia\Models\PostOffice;
use Tests\TestCase;

class PincodeTest extends TestCase
{
    use RefreshDatabase;

    protected $state;
    protected $district;
    protected $circle;
    protected $region;
    protected $division;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->state = State::create(['name' => 'Test State']);
        $this->district = District::create(['state_id' => $this->state->id, 'name' => 'Test District']);
        $this->circle = Circle::create(['name' => 'Test Circle']);
        $this->region = Region::create(['circle_id' => $this->circle->id, 'name' => 'Test Region']);
        $this->division = Division::create(['region_id' => $this->region->id, 'name' => 'Test Division']);
    }

    /** @test */
    public function it_has_correct_table_name()
    {
        $pincode = new Pincode();
        $this->assertEquals(config('pinindia.table_prefix') . '_pincodes', $pincode->getTable());
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $pincode = new Pincode();
        $this->assertEquals(['pincode', 'district_id', 'division_id'], $pincode->getFillable());
    }

    /** @test */
    public function it_has_no_timestamps()
    {
        $pincode = new Pincode();
        $this->assertFalse($pincode->timestamps);
    }

    /** @test */
    public function it_belongs_to_district()
    {
        $pincode = new Pincode();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $pincode->district());
        $this->assertEquals('district_id', $pincode->district()->getForeignKeyName());
    }

    /** @test */
    public function it_belongs_to_division()
    {
        $pincode = new Pincode();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $pincode->division());
        $this->assertEquals('division_id', $pincode->division()->getForeignKeyName());
    }

    /** @test */
    public function it_has_post_offices_relationship()
    {
        $pincode = new Pincode();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $pincode->postOffices());
        $this->assertEquals('pincode_id', $pincode->postOffices()->getForeignKeyName());
    }

    /** @test */
    public function it_can_create_pincode()
    {
        $pincode = Pincode::create([
            'pincode' => 123456,
            'district_id' => $this->district->id,
            'division_id' => $this->division->id
        ]);

        $this->assertDatabaseHas(config('pinindia.table_prefix') . '_pincodes', [
            'pincode' => 123456,
            'district_id' => $this->district->id,
            'division_id' => $this->division->id
        ]);
    }

    /** @test */
    public function it_can_retrieve_district()
    {
        $pincode = Pincode::create([
            'pincode' => 123456,
            'district_id' => $this->district->id,
            'division_id' => $this->division->id
        ]);

        $this->assertEquals('Test District', $pincode->district->name);
    }

    /** @test */
    public function it_can_retrieve_division()
    {
        $pincode = Pincode::create([
            'pincode' => 123456,
            'district_id' => $this->district->id,
            'division_id' => $this->division->id
        ]);

        $this->assertEquals('Test Division', $pincode->division->name);
    }

    /** @test */
    public function it_can_retrieve_post_offices()
    {
        $pincode = Pincode::create([
            'pincode' => 123456,
            'district_id' => $this->district->id,
            'division_id' => $this->division->id
        ]);

        $postOffice = PostOffice::create([
            'pincode_id' => $pincode->id,
            'name' => 'Test Post Office',
            'office' => 'Test Post Office S.O',
            'type' => 'S.O',
            'delivery' => 'Delivery',
            'latitude' => 12.9716,
            'longitude' => 77.5946
        ]);

        $this->assertCount(1, $pincode->postOffices);
        $this->assertEquals('Test Post Office', $pincode->postOffices->first()->name);
    }
}
