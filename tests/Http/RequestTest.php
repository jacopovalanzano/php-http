<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testResponse()
    {
        $this->assertTrue(true);
    }

    /**
     * @return void
     */
    public function testCapture()
    {
        $_SERVER["REQUEST_URI"] = "/";

        $request = new \Tundra\Http\Request();
        $this->assertTrue($request->capture() instanceof \Tundra\Http\Request);
    }

    /**
     * @return void
     */
    public function testCreateFromBase()
    {
        $request = new \Tundra\Http\Request();
        $this->assertTrue($request->createFromBase(new \Symfony\Component\HttpFoundation\Request(
                array(), array(), array(), array(), array(), array()
            )) instanceof \Tundra\Http\Request);
    }

    /**
     * @return void
     */
    public function testInitialize()
    {
        $request = new \Tundra\Http\Request();

        $this->assertEmpty($request->request);
        $this->assertEmpty($request->query);
        $this->assertEmpty($request->attributes);
        $this->assertEmpty($request->cookies);
        $this->assertEmpty($request->files);
        $this->assertEmpty($request->server);
        $this->assertEmpty($request->headers);

        $request->initialize(array(), array(), array(), array(), array(), array());

        $this->assertTrue($request->request instanceof \Symfony\Component\HttpFoundation\InputBag);
        $this->assertTrue($request->query instanceof \Symfony\Component\HttpFoundation\InputBag);
        $this->assertTrue($request->attributes instanceof \Symfony\Component\HttpFoundation\ParameterBag);
        $this->assertTrue($request->cookies instanceof \Symfony\Component\HttpFoundation\InputBag);
        $this->assertTrue($request->files instanceof \Symfony\Component\HttpFoundation\FileBag);
        $this->assertTrue($request->server instanceof \Symfony\Component\HttpFoundation\ServerBag);
        $this->assertTrue($request->headers instanceof \Symfony\Component\HttpFoundation\HeaderBag);
    }

    /**
     * @return void
     */
    public function testGetMethod()
    {
        $request = new \Tundra\Http\Request();
        $this->assertEquals("GET", $request->getMethod());
    }

    /**
     * @return void
     */
    public function testDeleteWithKey()
    {
        $query = ['key' => 'value']; // For example, parameters sent with the URL like /path?key=value
        $request = []; // No POST data here since DELETE generally doesn’t use it
        $attributes = []; // You can use this if needed for route parameters
        $cookies = []; // Simulate cookies, if necessary
        $files = []; // Simulate files if necessary
        $server = [
            'REQUEST_METHOD' => 'DELETE', // This is important for simulating the DELETE request
            'HTTP_HOST' => 'localhost', // Typically the host
            'REQUEST_URI' => '/my-path', // Simulate the URL being requested
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded', // This is often used for form-based input
        ];

        $content = http_build_query(['key' => 'value']); // Simulate the content of the DELETE request

        // Create a new Request object for the DELETE request
        $requestObject = new \Tundra\Http\Request($query, $request, $attributes, $cookies, $files, $server, $content);
        
        // Use the $requestObject to get query or content data
        $this->assertEquals('value', $requestObject->query->get('key')); // Test query data
        $this->assertEquals('key=value', $requestObject->getContent()); // Test content data

    }
}