<?php

namespace Tests\Unit\Facades;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PinIndia\Facades\PinIndia;
use PinIndia\Services\PinIndiaService;
use Tests\TestCase;

class PinIndiaFacadeTest extends TestCase
{
    /** @test */
    public function it_resolves_to_pinindia_service()
    {
        $this->assertInstanceOf(PinIndiaService::class, PinIndia::getFacadeRoot());
    }

    /** @test */
    public function it_proxies_find_by_pincode_method()
    {
        $mock = $this->mock(PinIndiaService::class);
        $mock->shouldReceive('findByPincode')
            ->once()
            ->with(123456)
            ->andReturn('result');

        $this->app->instance('pinindia', $mock);

        $result = PinIndia::findByPincode(123456);

        $this->assertEquals('result', $result);
    }

    /** @test */
    public function it_proxies_find_by_post_office_method()
    {
        $mock = $this->mock(PinIndiaService::class);
        $mock->shouldReceive('findByPostOffice')
            ->once()
            ->with('Test', 10)
            ->andReturn('result');

        $this->app->instance('pinindia', $mock);

        $result = PinIndia::findByPostOffice('Test', 10);

        $this->assertEquals('result', $result);
    }

    /** @test */
    public function it_proxies_get_nearest_by_coordinates_method()
    {
        $mock = $this->mock(PinIndiaService::class);
        $mock->shouldReceive('getNearestByCoordinates')
            ->once()
            ->with(12.9716, 77.5946, 10)
            ->andReturn('result');

        $this->app->instance('pinindia', $mock);

        $result = PinIndia::getNearestByCoordinates(12.9716, 77.5946, 10);

        $this->assertEquals('result', $result);
    }

    /** @test */
    public function it_proxies_get_nearest_by_pincode_method()
    {
        $mock = $this->mock(PinIndiaService::class);
        $mock->shouldReceive('getNearestByPincode')
            ->once()
            ->with(123456, 10)
            ->andReturn('result');

        $this->app->instance('pinindia', $mock);

        $result = PinIndia::getNearestByPincode(123456, 10);

        $this->assertEquals('result', $result);
    }

    /** @test */
    public function it_proxies_get_nearest_by_post_office_method()
    {
        $mock = $this->mock(PinIndiaService::class);
        $mock->shouldReceive('getNearestByPostOffice')
            ->once()
            ->with('Test', 10)
            ->andReturn('result');

        $this->app->instance('pinindia', $mock);

        $result = PinIndia::getNearestByPostOffice('Test', 10);

        $this->assertEquals('result', $result);
    }
}
