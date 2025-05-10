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

class PostOfficeTest extends TestCase
{
    use RefreshDatabase;

    protected $state;
    protected $district;
    protected $circle;
    protected $region;
    protected $division;
    protected $pincode;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->state = State::create(['name' => 'Test State']);
        $this->district = District::create(['state_id' => $this->state->id, 'name' => 'Test District']);
        $this->circle = Circle::create(['name' => 'Test Circle']);
        $this->region = Region::create(['circle_id' => $this->circle->id, 'name' => 'Test Region']);
        $this->division = Division::create(['region_id' => $this->region->id, 'name' => 'Test Division']);
        $this->pincode = Pincode::create([
            'pincode' => 123456,
            'district_id' => $this->district->id,
            'division_id' => $this->division->id
        ]);
    }

    /** @test */
    public function it_has_correct_table_name()
    {
        $postOffice = new PostOffice();
        $this->assertEquals(config('pinindia.table_prefix') . '_post_offices', $postOffice->getTable());
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $postOffice = new PostOffice();
        $this->assertEquals(['pincode_id', 'name', 'office', 'type', 'delivery', 'latitude', 'longitude'], $postOffice->getFillable());
    }

    /** @test */
    public function it_has_no_timestamps()
    {
        $postOffice = new PostOffice();
        $this->assertFalse($postOffice->timestamps);
    }

    /** @test */
    public function it_belongs_to_pincode()
    {
        $postOffice = new PostOffice();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $postOffice->pincode());
        $this->assertEquals('pincode_id', $postOffice->pincode()->getForeignKeyName());
    }

    /** @test */
    public function it_can_create_post_office()
    {
        $postOffice = PostOffice::create([
            'pincode_id' => $this->pincode->id,
            'name' => 'Test Post Office',
            'office' => 'Test Post Office S.O',
            'type' => 'S.O',
            'delivery' => 'Delivery',
            'latitude' => 12.9716,
            'longitude' => 77.5946
        ]);

        $this->assertDatabaseHas(config('pinindia.table_prefix') . '_post_offices', [
            'pincode_id' => $this->pincode->id,
            'name' => 'Test Post Office',
            'office' => 'Test Post Office S.O',
            'type' => 'S.O',
            'delivery' => 'Delivery',
            'latitude' => 12.9716,
            'longitude' => 77.5946
        ]);
    }

    /** @test */
    public function it_can_retrieve_pincode()
    {
        $postOffice = PostOffice::create([
            'pincode_id' => $this->pincode->id,
            'name' => 'Test Post Office',
            'office' => 'Test Post Office S.O',
            'type' => 'S.O',
            'delivery' => 'Delivery',
            'latitude' => 12.9716,
            'longitude' => 77.5946
        ]);

        $this->assertEquals(123456, $postOffice->pincode->pincode);
    }

    /** @test */
    public function it_can_access_district_through_pincode()
    {
        $postOffice = PostOffice::create([
            'pincode_id' => $this->pincode->id,
            'name' => 'Test Post Office',
            'office' => 'Test Post Office S.O',
            'type' => 'S.O',
            'delivery' => 'Delivery',
            'latitude' => 12.9716,
            'longitude' => 77.5946
        ]);

        $this->assertEquals('Test District', $postOffice->pincode->district->name);
    }

    /** @test */
    public function it_can_access_state_through_pincode_district()
    {
        $postOffice = PostOffice::create([
            'pincode_id' => $this->pincode->id,
            'name' => 'Test Post Office',
            'office' => 'Test Post Office S.O',
            'type' => 'S.O',
            'delivery' => 'Delivery',
            'latitude' => 12.9716,
            'longitude' => 77.5946
        ]);

        $this->assertEquals('Test State', $postOffice->pincode->district->state->name);
    }
}
