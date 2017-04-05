<?php

namespace Tests\Feature;

use Symfony\Component\VarDumper\VarDumper;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('');
        VarDumper::dump($response);
        $response->assertStatus(200);
    }
}
