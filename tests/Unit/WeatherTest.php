<?php

namespace Tests\Unit;

use Tests\TestCase;

class WeatherTest extends TestCase
{

    public function test_without_city(): void
    {
        $response = $this->json('GET', 'api/weather');
        $response->assertStatus(201);
    }

    public function test_with_city(): void
    {
        $response = $this->json('GET', 'api/weather?city=Gyumri');
        $response->assertStatus(201);
    }
}
