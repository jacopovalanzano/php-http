<?php

namespace Tundra\Http\Cookie;

class CookieHandler
{

    /**
     * Contains all the cookies created
     *
     * @var array
     */
    public $cookies = [];

    /**
     * @param string $name
     * @param string $path
     * @param string $domain
     * @throws \RuntimeException
     * @return Cookie
     */
    public function cookie(string $name, string $path = '/', string $domain = ''): Cookie
    {
        if(isset($this->cookies[$name])) {
            if(isset($this->cookies[$name][$path])) {
                if(isset($this->cookies[$name][$path][$domain])) {
                    return $this->cookies[$name][$path][$domain];
                }
            }
        }

        throw new \RuntimeException("Cookie does not exist.");
    }

    /**
     * @param string $name
     * @param string $value
     * @param int|null $expires
     * @param string $path
     * @param string $domain
     * @param string $sameSite
     * @param bool $secure
     * @param bool $httpOnly
     * @param bool $partitioned
     * @return Cookie
     */
    public function create(
        string $name,
        string $value = "",
        int $expires = null,
        string $path = '/',
        string $domain = '',
        string $sameSite = 'Lax',
        bool $secure = false,
        bool $httpOnly = true,
        bool $partitioned = false
    ): Cookie
    {
        if(isset($this->cookies[$name])) {
            if(isset($this->cookies[$name][$path])) {
                if(isset($this->cookies[$name][$path][$domain])) {
                    throw new \RuntimeException("Cookie already exists.", 1);
                }
            }
        }

        $cookie = new Cookie($name, $value, [
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httpOnly,
            'partitioned' => $partitioned,
            'samesite' => $sameSite
        ]);

        $name = $cookie->name();
        $path = $cookie->path();
        $domain = $cookie->domain();

        $this->cookies[$name][$path][$domain] = $cookie;

        return $cookie;
    }

    /**
     * Destroys a cookie
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param bool $defer
     * @throws \RuntimeException
     * @return void
     */
    public function destroy(string $name, string $path = '/', string $domain = '', bool $defer = false): void
    {
        if(isset($this->cookies[$name])) {
            if(isset($this->cookies[$name][$path])) {
                if(isset($this->cookies[$name][$path][$domain])) {

                    $cookie = $this->cookies[$name][$path][$domain];

                    // Destroy the cookie immediately
                    if($defer === false) {
                        $cookie->defer(true);
                        $cookie->expire();
                        unset($this->cookies[$name][$path][$domain]);
                        return;
                    }

                    // Let the cookie lifecycle expire
                    $cookie->defer(true);
                    $cookie->expire();
                    return;
                }
            }
        }

        throw new \RuntimeException("Cookie does not exist.", 2);
    }

    /**
     * Updates a cookie in the stack.
     *
     * @param Cookie $cookie
     * @throws \RuntimeException
     * @return void
     */
    public function update(Cookie $cookie)
    {
        $name = $cookie->name();
        $path = $cookie->path();
        $domain = $cookie->domain();

        if(isset($this->cookies[$name])) {
            if(isset($this->cookies[$name][$path])) {
                if(isset($this->cookies[$name][$path][$domain])) {
                    $this->cookies[$name][$path][$domain] = $cookie;
                    return;
                }
            }
        }

        throw new \RuntimeException("Cookie does not exist.", 1);
    }

    /**
     * Adds a cookie to the stack.
     *
     * @param Cookie $cookie
     * @throws \RuntimeException
     * @return void
     */
    public function add (Cookie $cookie)
    {
        $name = $cookie->name();
        $path = $cookie->path();
        $domain = $cookie->domain();

        if(isset($this->cookies[$name])) {
            if(isset($this->cookies[$name][$path])) {
                if(isset($this->cookies[$name][$path][$domain])) {
                    throw new \RuntimeException("Cookie already exists.");
                }
            }
        }

        $this->cookies[$name][$path][$domain] = $cookie;
    }

    /**
     * Gets a cookie from the stack.
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     * @throws \RuntimeException
     * @return Cookie
     */
    public function get(string $name, string $path = '/', string $domain = '') {
        if(isset($this->cookies[$name])) {
            if(isset($this->cookies[$name][$path])) {
                if(isset($this->cookies[$name][$path][$domain])) {
                    return $this->cookies[$name][$path][$domain];
                }
            }
        }

        throw new \RuntimeException("Cookie does not exist.");
    }
}