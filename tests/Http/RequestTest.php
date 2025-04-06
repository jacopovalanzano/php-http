<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Tundra\Http\Request;

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

        $request = new Request();
        $this->assertTrue($request->capture() instanceof \Tundra\Http\Request);
    }

    /**
     * @return void
     */
    public function testCreateFromBase()
    {
        $request = new Request();
        $this->assertTrue($request->createFromBase(new \Symfony\Component\HttpFoundation\Request(
                array(), array(), array(), array(), array(), array()
            )) instanceof \Tundra\Http\Request);
    }

    /**
     * @return void
     */
    public function testInitialize()
    {
        $request = new Request();

        $this->assertEmpty($request->request);
        $this->assertEmpty($request->query);
        $this->assertEmpty($request->attributes);
        $this->assertEmpty($request->cookies);
        $this->assertEmpty($request->files);
        $this->assertEmpty($request->server);
        $this->assertEmpty($request->headers);

        $request->initialize(
            $_GET,
            $_POST,
            $_REQUEST,
            $_COOKIE,
            $_FILES,
            $_SERVER
        );

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
        $request = new Request(
            $_GET,
            $_POST,
            $_REQUEST,
            $_COOKIE,
            $_FILES,
            $_SERVER
        );
        $this->assertEquals("GET", $request->getMethod());
    }

    /**
     * @return void
     */
    public function testGetMethodWithOverride()
    {
        // Simulate a DELETE request with a method override
        $request = Request::create(
            '/path',
            'POST',
            [],
            [],
            [],
            ['REQUEST_METHOD' => 'POST'],
            ''
        );

        $request->headers->set('X-HTTP-METHOD-OVERRIDE', 'DELETE');

        // Assert that the method is correctly overridden to DELETE
        $this->assertEquals('DELETE', $request->getMethod());
    }

    /**
     * @return void
     */
    public function testGetMethodWithPostMethod()
    {
        // Simulate a POST request
        $request = Request::create('/path', 'POST');

        // Assert that the method is POST
        $this->assertEquals('POST', $request->getMethod());
    }

    /**
     * @return void
     */
    public function testGetMethodWithGetMethod()
    {
        // Simulate a GET request with REQUEST_METHOD being GET
        $request = Request::create('/path', 'GET');

        // Assert that the method is GET
        $this->assertEquals('GET', $request->getMethod());
    }

    /**
     * @return void
     */
    public function testDeleteWithKey()
    {
        $query = ['key' => 'value']; // For example, parameters sent with the URL like /path?key=value
        $request = $_POST;
        $attributes = $_REQUEST;
        $cookies = $_COOKIE;
        $files = $_FILES;
        $server = [
            'REQUEST_METHOD' => 'DELETE', // This is important for simulating the DELETE request
            'HTTP_HOST' => 'localhost', // Typically the host
            'REQUEST_URI' => '/my-path', // Simulate the URL being requested
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded', // This is often used for form-based input
        ];

        $content = http_build_query(['key' => 'value']); // Simulate the content of the DELETE request

        // Create a new Request object for the DELETE request
        $requestObject = new Request($query, $request, $attributes, $cookies, $files, $server, $content);

        // Use the $requestObject to get query or content data
        $this->assertEquals('value', $requestObject->query->get('key')); // Test query data
        $this->assertEquals('key=value', $requestObject->getContent()); // Test content data
    }

    /**
     * @return void
     */
    public function testGetProtocolVersion()
    {
        if(! isset($_SERVER["SERVER_PROTOCOL"])) {
            $_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";
        }

        $request = new Request(
            $_GET,
            $_POST,
            $_REQUEST,
            $_COOKIE,
            $_FILES,
            $_SERVER
        );

        $this->assertEquals("HTTP/1.1", $request->getProtocolVersion());
    }
}