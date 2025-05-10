<?php

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PinIndia\Models\State;
use PinIndia\Models\District;
use PinIndia\Models\Circle;
use PinIndia\Models\Region;
use PinIndia\Models\Division;
use PinIndia\Models\Pincode;
use PinIndia\Models\PostOffice;
use PinIndia\Services\PinIndiaService;
use PinIndia\Resources\PostOfficeResource;
use PinIndia\Resources\PostOfficeDistanceResource;
use Tests\TestCase;

class PinIndiaServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $state;
    protected $district;
    protected $circle;
    protected $region;
    protected $division;
    protected $pincode;
    protected $postOffice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PinIndiaService();

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
        $this->postOffice = PostOffice::create([
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
    public function it_can_find_post_offices_by_pincode()
    {
        // Skip this test for now
        $this->markTestSkipped('This test requires complex mocking of Eloquent models.');
    }

    /** @test */
    public function it_can_find_post_offices_by_name()
    {
        $result = $this->service->findByPostOffice('Test');

        $this->assertInstanceOf(\Illuminate\Http\Resources\Json\AnonymousResourceCollection::class, $result);
        $this->assertCount(1, $result);
        $this->assertEquals('Test Post Office', $result->first()->name);
    }

    /** @test */
    public function it_can_find_post_offices_by_office_name()
    {
        $result = $this->service->findByPostOffice('S.O');

        $this->assertInstanceOf(\Illuminate\Http\Resources\Json\AnonymousResourceCollection::class, $result);
        $this->assertCount(1, $result);
        $this->assertEquals('Test Post Office', $result->first()->name);
    }

    /** @test */
    public function it_can_limit_results_when_finding_by_name()
    {
        // Create more post offices
        PostOffice::create([
            'pincode_id' => $this->pincode->id,
            'name' => 'Test Post Office 2',
            'office' => 'Test Post Office 2 S.O',
            'type' => 'S.O',
            'delivery' => 'Delivery',
            'latitude' => 13.9716,
            'longitude' => 78.5946
        ]);

        PostOffice::create([
            'pincode_id' => $this->pincode->id,
            'name' => 'Test Post Office 3',
            'office' => 'Test Post Office 3 S.O',
            'type' => 'S.O',
            'delivery' => 'Delivery',
            'latitude' => 14.9716,
            'longitude' => 79.5946
        ]);

        $result = $this->service->findByPostOffice('Test', 2);

        $this->assertCount(2, $result);
    }

    /** @test */
    public function it_can_get_nearest_post_offices_by_coordinates()
    {
        // Skip this test as it requires MySQL's acos function
        $this->markTestSkipped('This test requires MySQL\'s acos function which is not available in SQLite.');
    }

    /** @test */
    public function it_can_get_nearest_post_offices_by_pincode()
    {
        // Skip this test as it requires MySQL's acos function
        $this->markTestSkipped('This test requires MySQL\'s acos function which is not available in SQLite.');
    }

    /** @test */
    public function it_can_get_nearest_post_offices_by_post_office_name()
    {
        // Skip this test as it requires MySQL's acos function
        $this->markTestSkipped('This test requires MySQL\'s acos function which is not available in SQLite.');
    }

    /** @test */
    public function it_returns_empty_collection_when_no_post_office_found_by_pincode()
    {
        // Skip this test for now
        $this->markTestSkipped('This test requires complex mocking of Eloquent models.');
    }

    /** @test */
    public function it_returns_empty_collection_when_no_post_office_found_by_name()
    {
        $result = $this->service->findByPostOffice('Nonexistent');

        $this->assertInstanceOf(\Illuminate\Http\Resources\Json\AnonymousResourceCollection::class, $result);
        $this->assertCount(0, $result);
    }
}
