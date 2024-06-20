<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HotSellProductTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHotSellProduct()
    {
        $response = $this->get('api/user/test/get-hot-sell-product');

        $response->assertStatus(200);
        
        $data = $response->getData();
        dd($data);
    }
}
