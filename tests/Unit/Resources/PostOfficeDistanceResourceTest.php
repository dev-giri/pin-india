<?php

namespace Tests\Unit\Resources;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PinIndia\Models\State;
use PinIndia\Models\District;
use PinIndia\Models\Circle;
use PinIndia\Models\Region;
use PinIndia\Models\Division;
use PinIndia\Models\Pincode;
use PinIndia\Models\PostOffice;
use PinIndia\Resources\PostOfficeDistanceResource;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Model;

class PostOfficeDistanceResourceTest extends TestCase
{
    use RefreshDatabase;

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
    public function it_transforms_post_office_with_distance_to_array()
    {
        // Load relationships
        $this->postOffice->load('pincode.district.state');

        // Add distance attribute
        $postOfficeWithDistance = $this->postOffice;
        $postOfficeWithDistance->distance = 5.25;

        $resource = new PostOfficeDistanceResource($postOfficeWithDistance);
        $array = $resource->toArray(request());

        $this->assertEquals([
            'name' => 'Test Post Office',
            'pincode' => 123456,
            'distance' => 5.25,
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'district' => 'Test District',
            'state' => 'Test State',
        ], $array);
    }

    /** @test */
    public function it_transforms_collection_of_post_offices_with_distance()
    {
        // Create another post office
        $postOffice2 = PostOffice::create([
            'pincode_id' => $this->pincode->id,
            'name' => 'Another Post Office',
            'office' => 'Another Post Office S.O',
            'type' => 'S.O',
            'delivery' => 'Delivery',
            'latitude' => 13.9716,
            'longitude' => 78.5946
        ]);

        // Load relationships and add distance
        $postOffices = PostOffice::with('pincode.district.state')->get();
        $postOffices[0]->distance = 5.25;
        $postOffices[1]->distance = 10.5;

        $collection = PostOfficeDistanceResource::collection($postOffices);
        $array = $collection->toArray(request());

        // Check if the collection has the expected structure
        if (isset($array['data'])) {
            // Laravel 8+ format with 'data' key
            $this->assertCount(2, $array['data']);
            $this->assertEquals(5.25, $array['data'][0]['distance']);
            $this->assertEquals(10.5, $array['data'][1]['distance']);
        } else {
            // Direct array format
            $this->assertCount(2, $array);
            $this->assertEquals(5.25, $array[0]['distance']);
            $this->assertEquals(10.5, $array[1]['distance']);
        }
    }

    /** @test */
    public function it_inherits_from_post_office_resource()
    {
        // Load relationships
        $this->postOffice->load('pincode.district.state');

        // Add distance attribute
        $postOfficeWithDistance = $this->postOffice;
        $postOfficeWithDistance->distance = 5.25;

        $resource = new PostOfficeDistanceResource($postOfficeWithDistance);
        $array = $resource->toArray(request());

        // Check that it has all the fields from PostOfficeResource plus distance
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('pincode', $array);
        $this->assertArrayHasKey('latitude', $array);
        $this->assertArrayHasKey('longitude', $array);
        $this->assertArrayHasKey('district', $array);
        $this->assertArrayHasKey('state', $array);
        $this->assertArrayHasKey('distance', $array);
    }
}
