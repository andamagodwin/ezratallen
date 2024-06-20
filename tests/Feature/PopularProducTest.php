<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PopularProducTest extends TestCase
{
    /**
     * A basic feature test example.
     *@test
     * @return void
     */
    public function testPopularProduct()
    {
        $response = $this->getJson('api/seller/test/get-product-by-popular');

        $response->assertStatus(200);
    }
}
