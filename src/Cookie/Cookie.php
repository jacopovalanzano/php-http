<?php

namespace Tundra\Http\Cookie;

use InvalidArgumentException;

class Cookie extends AbstractCookie
{

    /**
     * Whether to automatically send cookie.
     * If set to true, the cookie header will be sent when the object is destructed.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Whether the cookie has been sent.
     *
     * @var bool
     */
    protected $sent = false;

    /**
     * Cookie constructor.
     * Upon instantiation, instructs the CookieHandler to create a new cookie, and returns an instance of self.
     *
     * @param string $name
     * @param string $value
     * @param array $options
     * @throws InvalidArgumentException
     */
    public function __construct(string $name, string $value = "", array $options = [])
    {
        if(empty($name)) {
            throw new \InvalidArgumentException(\get_class(). "Cookie name must not be empty.");
        }

        parent::__construct($name, $value);

        $this->setCookieOptions($options);
    }

    /**
     * Creates a new (cookie) instance of self.
     *
     * @param string $name
     * @param string $value
     * @param int|null $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param string $sameSite
     * @param bool $partitioned
     * @return Cookie
     */
    public function create(
        string $name,
        string $value = "",
        int    $expires = null,
        string $path = '/',
        string $domain = '',
        bool   $secure = false,
        bool   $httpOnly = true,
        string $sameSite = 'Lax',
        bool   $partitioned = false
    ): Cookie
    {
        return new self($name, $value, [
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
            'partitioned' => $partitioned
        ]);
    }

    /**
     * Sends the cookie header to the client.
     *
     * @return void
     */
    public function send(): void
    {
        if($this->sent === true) {
            throw new \RuntimeException("Cookie '" . $this->name . "' has already been sent.");
        }

        $this->sent = true;

        parent::send();
    }

    /**
     * Set the cookie. The cookie is set locally and not sent to the client.
     *
     * @param string $name
     * @param string $value
     * @param int|null $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param string $sameSite
     * @param bool $partitioned
     * @return Cookie
     */
    public function set(
        string $name,
        string $value = "",
        int $expires = null,
        string $path = "",
        string $domain = "",
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = "Lax",
        bool $partitioned = false
    ): Cookie
    {
        $this->name = $name;
        $this->value = $value;
        $this->expires = $expires;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $sameSite;
        $this->partitioned = $partitioned;

        return $this;
    }

    /**
     * @throws \RuntimeException
     * @return Cookie
     */
    public function destroy(): Cookie
    {
        $this->delete();

        return $this;
    }

    /**
     * Resets the cookie.
     *
     * @return Cookie
     */
    public function reset(): Cookie
    {
        $this->name = "";
        $this->value = "";
        $this->expires = null;
        $this->path = "/";
        $this->domain = "";
        $this->secure = false;
        $this->httpOnly = true;
        $this->sameSite = "Lax";
        $this->partitioned = false;
        $this->maxAge = null;

        return $this;
    }

    /**
     * @return Cookie
     */
    public function clear(): Cookie
    {
        return $this->reset();
    }

    /**
     * See http://php.net/setrawcookie
     *
     * @return bool
     */
    public function setRawCookie(): bool
    {
        return \setrawcookie(
            $this->name(),
            \rawurlencode( $this->value() ),
            $this->expires(),
            $this->path(),
            $this->domain(),
            $this->secure(),
            $this->httpOnly()
        );
    }

    /**
     * See http://php.net/setcookie
     *
     * @return bool
     */
    public function setCookie(): bool
    {
        return \setcookie(
            $this->name(),
            $this->value(),
            $this->expires(),
            $this->path(),
            $this->domain(),
            $this->secure(),
            $this->httpOnly()
        );
    }

    /**
     * @param bool $defer
     * @return $this
     */
    public function defer(bool $defer = true): Cookie
    {
        $this->defer = $defer;

        return $this;
    }

    /**
     * Send cookie
     */
    public function __destruct()
    {
        if(($this->defer === true) && ($this->sent === false)) {
            $this->send();
        }
    }
}