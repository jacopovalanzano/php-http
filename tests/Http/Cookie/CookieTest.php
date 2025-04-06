<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

function parseSetCookieHeader($cookieHeader)
{
    $cookie = [];

    // Split the string by semicolon to separate the individual cookie parts
    $parts = explode(';', $cookieHeader);

    // The first part should be the cookie name and value (e.g., 'name=value')
    $cookieData = explode('=', trim($parts[0]), 2);

    if (count($cookieData) == 2) {
        $cookie['name'] = preg_replace('/^Set-Cookie:\s*/', '', $cookieData[0]);;
        $cookie['value'] = $cookieData[1];
    }

    // Loop through the other parts to capture attributes like Path, Domain, Expires, etc.
    foreach (array_slice($parts, 1) as $part) {
        // Remove any leading and trailing spaces
        $part = trim($part);

        if (stripos($part, 'path=') === 0) {
            $cookie['path'] = substr($part, 5);
        } elseif (stripos($part, 'domain=') === 0) {
            $cookie['domain'] = substr($part, 7);
        } elseif (stripos($part, 'expires=') === 0) {
            $cookie['expires'] = substr($part, 8);
        } elseif (stripos($part, 'secure') === 0) {
            $cookie['secure'] = true;
        } elseif (stripos($part, 'httponly') === 0) {
            $cookie['httponly'] = true;
        } elseif (stripos($part, 'samesite=') === 0) {
            $cookie['samesite'] = substr($part, 9);
        } elseif (stripos($part, 'partitioned') === 0) {
            $cookie['partitioned'] = true;
        } elseif(stripos($part, 'max-age=') === 0) {
            $cookie['max-age'] = (int) substr($part, 8);
        }
    }

    return $cookie;
}


class CookieTest extends TestCase
{

    /**
     * Tests the `defer()` method
     *
     * @todo Test headers sent with \header
     * @return void
     */
    public function testDefer()
    {
        // Create cookie
        $cookie = new \Tundra\Http\Cookie\Cookie("my_cookie");

        // Cookie expiration
        $expires = time() + 3600;
        
        // Set cookie (values only, doesnt send)
        $cookie->set(
            "my_cookie",
            "my_value",
            $expires,
            "/",
            "test.com",
            true,
            true
        );

        $cookieString = $cookie->toString();

        $cookie->defer(true);

        $this->assertEquals([], $_COOKIE);

        unset($cookie); // Triggers the __destruct() method

        $this->assertEquals([], $_COOKIE);

        $this->assertEquals([
            "name" => "my_cookie",
            "value" => "my_value",
            "expires" => gmdate("D, d M Y H:i:s", $expires) . " GMT",
            "path" => "/",
            "domain" => "test.com",
            "secure" => true,
            "httponly" => true,
            "samesite" => "Lax" // SameSite default ("Lax")
        ], parseSetCookieHeader($cookieString));
    }

    /**
     * Tests all possible cookie attributes at once.
     *
     * @return void
     */
    public function testAllAttributesWithData()
    {

        // Cookie attributes
        $name = "test_cookie";
        $path = "/";
        $domain = "test.com";
        $value = "test_value";
        $expires = time() + 3600;
        $maxAge = 3600;
        $secure = true;
        $httpOnly = true;
        $sameSite = "Lax";
        $partitioned = true;

        // Create cookie
        $cookie = new \Tundra\Http\Cookie\Cookie($name, $value);

        $cookie->setPath($path);
        $cookie->setDomain($domain);
        $cookie->setExpires($expires);
        $cookie->setMaxAge($maxAge);
        $cookie->setSecure($secure);
        $cookie->setHttpOnly($httpOnly);
        $cookie->setSameSite($sameSite);
        $cookie->setPartitioned($partitioned);

        $cookieString = $cookie->toString();

        $this->assertEquals([
            "name" => $name,
            "value" => $value,
            "expires" => gmdate("D, d M Y H:i:s", $expires) . " GMT",
            "path" => $path,
            "domain" => $domain,
            "secure" => $secure,
            "httponly" => $httpOnly,
            "samesite" => $sameSite,
            "partitioned" => $partitioned,
            "max-age" => $maxAge
        ], parseSetCookieHeader($cookieString));

        unset($cookie);
    }

    /**
     * Tests all possible cookie attributes at once, using null or empty data.
     *
     * @return void
     */
    public function testAllAttributesWithoutData()
    {
        // Cookie attributes
        $name = "cookie_name"; // Name cannot be empty!
        $path = ""; // Must be string
        $domain = ""; // Must be string
        $value = ""; // Must be string
        $expires = null;
        $maxAge = null;
        $secure = null;
        $httpOnly = null;
        $sameSite = null;
        $partitioned = null;

        // Create cookie
        $cookie = new \Tundra\Http\Cookie\Cookie($name, $value);

        $cookie->setPath($path);
        $cookie->setDomain($domain);
        $cookie->setExpires($expires);
        $cookie->setMaxAge($maxAge);
        //$cookie->setSecure($secure); (bool) cannot be null
        //$cookie->setHttpOnly($httpOnly); (bool)annot be null
        $cookie->setSameSite($sameSite);
        //$cookie->setPartitioned($partitioned); (bool) cannot be null

        $cookieString = $cookie->toString();

        $this->assertEquals([
            "name" => $name,
            "value" => $value,
            "path" => "/",
            "httponly" => true
        ], parseSetCookieHeader($cookieString));

        unset($cookie);
    }

    /**
     * Tests all possible cookie attributes at once, using negative values.
     *
     * @return void
     */
    public function testAllAttributesWithNegativeValues()
    {

        // Cookie attributes
        $name = "cookie_name"; // Name cannot be empty!
        $path = "/"; // Must be string
        $domain = "test.com"; // Must be string
        $value = " "; // 1 space
        $expires = -1;
        $maxAge = -1;
        $secure = false;
        $httpOnly = false;
        $sameSite = "Strict";
        $partitioned = false;

        // Create cookie
        $cookie = new \Tundra\Http\Cookie\Cookie($name, $value);

        $cookie->setPath($path);
        $cookie->setDomain($domain);
        $cookie->setExpires($expires);
        $cookie->setMaxAge($maxAge);
        $cookie->setSecure($secure);
        $cookie->setHttpOnly($httpOnly);
        $cookie->setSameSite($sameSite);
        $cookie->setPartitioned($partitioned);

        $cookieString = $cookie->toString();

        $this->assertEquals([
            "name" => $name,
            "value" => '%20',
            "expires" => gmdate("D, d M Y H:i:s", $expires) . " GMT",
            "path" => $path,
            "domain" => $domain,
            "samesite" => $sameSite,
            "max-age" => $maxAge
        ], parseSetCookieHeader($cookieString));

        unset($cookie);
    }

    /**
     * Test an empty cookie name.
     *
     * @return void
     */
    public function testEmptyCookieName()
    {
        // Create cookie
        $this->expectException(\InvalidArgumentException::class);
        $cookie = new \Tundra\Http\Cookie\Cookie("", "my_value");
    }

    /**
     * Test an empty cookie Path.
     *
     * @return void
     */
    public function testEmptyCookiePath()
    {
        // Create cookie
        $cookie = new \Tundra\Http\Cookie\Cookie("my_cookie", "my_value", [
            "path" => "",
        ]);

        $this->assertEquals([
            "name" => "my_cookie",
            "value" => "my_value",
            "path" => "/", // Path defaults to "/"
            "samesite" => "Lax",
            "httponly" => true
        ], parseSetCookieHeader($cookie->toString()));

        unset($cookie);
    }

    /**
     * Test an empty cookie Domain.
     *
     * @return void
     */
    public function testEmptyCookieDomain()
    {
        // Create cookie
        $cookie = new \Tundra\Http\Cookie\Cookie("my_cookie", "my_value", [
            "domain" => ""
        ]);

        $this->assertEquals([
            "name" => "my_cookie",
            "value" => "my_value",
            "path" => "/",
            "samesite" => "Lax", // SameSite always defaults to lax
            "httponly" => true
        ], parseSetCookieHeader($cookie->toString()));

        unset($cookie);
    }

    /**
     * Test an empty cookie value.
     *
     * @return void
     */
    public function testEmptyCookieValue()
    {
        // Create cookie
        $cookie = new \Tundra\Http\Cookie\Cookie("my_cookie", "");

        $this->assertEquals([
            "name" => "my_cookie",
            "value" => "",
            "path" => "/",
            "samesite" => "Lax",
            "httponly" => true
        ], parseSetCookieHeader($cookie->toString()));

        unset($cookie);
    }

    /**
     * Test an empty cookie SameSite.
     *
     * @return void
     */
    public function testEmptyCookieSameSite()
    {
        // Create cookie
        $cookie = new \Tundra\Http\Cookie\Cookie("my_cookie", "my_value", [
            "samesite" => null, // An empty string is not allowed
        ]);

        $this->assertEquals([
            "name" => "my_cookie",
            "value" => "my_value",
            "path" => "/", // Path defaults to "/"
            "httponly" => true
        ], parseSetCookieHeader($cookie->toString()));

        unset($cookie);
    }
}
