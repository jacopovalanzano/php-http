<?php

namespace Http;
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    /**
     * @return void
     */
    public function testResponse()
    {
        $response = new \Tundra\Http\Response("Hello World", 200, ["Content-Type" => "text/plain"]);
        $this->assertEquals("Hello World", $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("text/plain", $response->headers->get("Content-Type"));
    }

    /**
     * @return void
     */
    public function testSetContent()
    {
        $response = new \Tundra\Http\Response();
        $this->assertEquals("Hello World", $response->setContent("Hello World")->getContent());
        $this->assertEquals('["Hello World"]', $response->setContent(["Hello World"])->getContent()); // Test array
        $this->assertEquals('["Hello World"]', $response->setContent('["Hello World"]')->getContent()); // Test json
    }
}