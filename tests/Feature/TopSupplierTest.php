<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TopSupplierTest extends TestCase
{
    /**
     *  feature test for top suppliers for seller.
     *
     * @return void
     */
    public function testSellerTopSuppliers()
    {
        $response = $this->getJson('api/user/top-suppliers');

        $response->assertStatus(200);
        $data = $response->getData();
         
    }
    
    /**
     *  feature test for top suppliers for user.
     *
     * @return void
     */
    public function testUserTopSuppliers()
    {
        $response = $this->getJson('api/user/get/top-suppliers');

        $response->assertStatus(200);
        $data = $response->getData();
        //dd($data);
    }
}
