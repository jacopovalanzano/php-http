<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Tundra\Http\Cookie\Cookie;

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


class CookieHandlerTest extends TestCase
{
    /**
     * @return void
     */
    public function testCookie()
    {
        $cookieName = "my_cookie_name";
        $cookieValue = "my_cookie_value";

        $handler = new \Tundra\Http\Cookie\CookieHandler();
        $handler->create(
            $cookieName,
            $cookieValue
        );

        $this->assertEquals([
            "name" => $cookieName,
            "value" => $cookieValue,
            "path" => "/",
            "httponly" => true,
            "samesite" => "Lax"
        ], parseSetCookieHeader($handler->cookie($cookieName)->toString()));

        // Test with 3 arguments
        $this->assertEquals([
            "name" => $cookieName,
            "value" => $cookieValue,
            "path" => "/",
            "httponly" => true,
            "samesite" => "Lax"
        ], parseSetCookieHeader($handler->cookie($cookieName, "/", "")->toString()));
    }

    /**
     * @return void
     */
    public function testCreate()
    {
        // The CookieHandler
        $cookieHandler = new \Tundra\Http\Cookie\CookieHandler();

        $cookieName = "my_cookie_name";
        $cookieValue = "my_cookie_value";
        $expires = time() + 3600; // 1 hour from now
        $path = "/";
        $domain = "test.com";
        $sameSite = "Lax";
        $secure = false;
        $httpOnly = true;
        $partitioned = false;

        // Create cookie
        $cookieHandler->create(
            $cookieName,
            $cookieValue,
            $expires,
            $path,
            $domain,
            $sameSite,
            $secure,
            $httpOnly,
            $partitioned
        );

        $this->assertEquals([
            "name" => $cookieName,
            "value" => $cookieValue,
            "path" => $path,
            "domain" => $domain,
            "expires" => gmdate("D, d M Y H:i:s T", $expires),
            "httponly" => $httpOnly,
            "samesite" => $sameSite,
        ], parseSetCookieHeader($cookieHandler->cookie($cookieName, $path, $domain)->toString()));

        unset($cookie);
    }

    /**
     * @return void
     */
    public function testDestroy()
    {
        // The CookieHandler
        $cookieHandler = new \Tundra\Http\Cookie\CookieHandler();

        $cookieName = "my_cookie_name";
        $cookieValue = "my_cookie_value";
        $expires = time() + 3600; // 1 hour from now
        $path = "/";
        $domain = "test.com";
        $sameSite = "Lax";
        $secure = false;
        $httpOnly = true;
        $partitioned = false;

        // Create cookie
        $cookieHandler->create(
            $cookieName,
            $cookieValue,
            $expires,
            $path,
            $domain,
            $sameSite,
            $secure,
            $httpOnly,
            $partitioned
        );

        $this->assertEquals([
            "name" => $cookieName,
            "value" => $cookieValue,
            "path" => $path,
            "domain" => $domain,
            "expires" => gmdate("D, d M Y H:i:s T", $expires),
            "httponly" => $httpOnly,
            "samesite" => $sameSite,
        ], parseSetCookieHeader($cookieHandler->cookie($cookieName, $path, $domain)->toString()));

        // Destroy cookie
        $cookieHandler->destroy($cookieName, $path, $domain, true); // Deferred

        $this->assertEquals([
            "name" => $cookieName,
            "value" => "",
            "path" => $path,
            "domain" => $domain,
            "expires" => 'Wed, 31 Dec 1969 23:00:00 GMT',
            "httponly" => $httpOnly,
            "samesite" => $sameSite,
        ], parseSetCookieHeader($cookieHandler->cookie($cookieName, $path, $domain)->toString()));

        // Destroy cookie
        $cookieHandler->destroy($cookieName, $path, $domain); // Not deferred

        // Expect an exception: the cookie does not exist
        $this->expectException(\RuntimeException::class);
        $cookieHandler->cookie($cookieName, $path, $domain);
    }

    /**
     * @return void
     */
    public function testUpdate()
    {
        // The CookieHandler
        $cookieHandler = new \Tundra\Http\Cookie\CookieHandler();

        // Cookie details
        $cookieName = "my_cookie_name";
        $cookieValue = "my_cookie_value";
        $expires = time() + 3600; // 1 hour from now
        $path = "/";
        $domain = "test.com";
        $sameSite = "Lax";
        $secure = false;
        $httpOnly = true;
        $partitioned = false;

        // Create cookie
        $cookieHandler->create(
            $cookieName,
            $cookieValue,
            $expires,
            $path,
            $domain,
            $sameSite,
            $secure,
            $httpOnly,
            $partitioned
        );

        $this->assertEquals([
            "name" => $cookieName,
            "value" => $cookieValue,
            "path" => $path,
            "domain" => $domain,
            "expires" => gmdate("D, d M Y H:i:s T", $expires),
            "httponly" => $httpOnly,
            "samesite" => $sameSite,
        ], parseSetCookieHeader($cookieHandler->cookie($cookieName, $path, $domain)->toString()));

        // Get the cookie
        $cookie = $cookieHandler->cookie($cookieName, $path, $domain);

        // Edit the cookie
        $cookie->setValue("new_cookie_value");
        $cookie->setExpires($expires + 3600);
        $cookie->setSameSite("Strict");

        // Update cookie
        $cookieHandler->update($cookie);

        $this->assertEquals([
            "name" => $cookieName,
            "value" => "new_cookie_value",
            "path" => $path,
            "domain" => $domain,
            "expires" => gmdate("D, d M Y H:i:s T", $expires + 3600),
            "httponly" => $httpOnly,
            "samesite" => "Strict",
        ], parseSetCookieHeader($cookieHandler->cookie($cookieName, $path, $domain)->toString()));
    }

    /**
     * @return void
     */
    public function testAddAndGet()
    {
        // The CookieHandler
        $cookieHandler = new \Tundra\Http\Cookie\CookieHandler();

        // Cookie details
        $cookieName = "my_cookie_name";
        $cookieValue = "my_cookie_value";
        $expires = time() + 3600; // 1 hour from now
        $path = "/";
        $domain = "";
        $sameSite = "Lax";
        $secure = false;
        $httpOnly = true;
        $partitioned = false;

        // Create cookie
        $cookie = new Cookie(
            $cookieName,
            $cookieValue,
            [
                "expires" => $expires,
                "path" => $path,
                "domain" => $domain,
                "sameSite" => $sameSite,
                "httpOnly" => $httpOnly,
                "secure" => $secure,
                "partitioned" => $partitioned
            ]
        );

        // Add cookie
        $cookieHandler->add($cookie);

        // Get the cookie
        $this->assertEquals([
            "name" => $cookieName,
            "value" => $cookieValue,
            "path" => $path,
            "expires" => gmdate("D, d M Y H:i:s T", $expires),
            "httponly" => $httpOnly,
            "samesite" => $sameSite,
        ], parseSetCookieHeader($cookieHandler->get($cookieName, $path, $domain)->toString()));

        // Get the (the default way)
        $this->assertEquals([
            "name" => $cookieName,
            "value" => $cookieValue,
            "path" => $path,
            "expires" => gmdate("D, d M Y H:i:s T", $expires),
            "httponly" => $httpOnly,
            "samesite" => $sameSite,
        ], parseSetCookieHeader($cookieHandler->get($cookieName)->toString()));
    }
}
