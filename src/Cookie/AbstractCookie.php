<?php


namespace Tundra\Http\Cookie;


use InvalidArgumentException;

/**
 * AbstractCookie manager.
 */
abstract class AbstractCookie implements CookieInterface
{
    /**
     * The cookie name
     *
     * @var string
     */
    protected $name;

    /**
     * The cookie value
     *
     * @var string
     */
    protected $value = "";

    /**
     * Path=<string>
     *
     * @var string
     */
    protected $path = "/";

    /**
     * Domain=<string>
     *
     * @var string|bool
     */
    protected $domain = false;

    /**
     * Secure;
     *
     * @var bool
     */
    protected $secure = false;

    /**
     * HttpOnly;
     *
     * @var bool
     */
    protected $httpOnly = true;

    /**
     * Expires=<date>
     *
     * 0 = never expires
     *
     * @var int
     */
    protected $expires = null;

    /**
     * Max-Age=<int>
     *
     * @var int|null
     */
    protected $maxAge = null;

    /**
     * Partitioned;
     *
     * @var bool
     */
    protected $partitioned = false;

    /**
     * SameSite=<Strict|Lax|None; Secure>
     *
     * If "None" is used, the "Secure" attribute must also be set.
     *
     * @var string|null
     */
    protected $sameSite = 'Lax';

    /**
     * AbstractCookie constructor.
     *
     * @param $name
     * @param string $value
     * @param int|null $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     */
    public function __construct(
        $name,
        string $value = '',
        int $expires = null,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true
    )
    {
        if(empty($name)) {
            throw new \InvalidArgumentException(\get_class() . " Cookie name must not be empty.");
        }

        $this->setName($name);
        $this->setValue($value);
        $this->setExpires($expires);
        $this->setPath($path);
        $this->setDomain($domain);
        $this->setSecure($secure);
        $this->setHttpOnly($httpOnly);
    }

    /**
     * @param string $name
     * @param string $value
     * @param int|null $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param string $sameSite
     * @param bool $partitioned
     * @return mixed
     */
    abstract public function set(
        string $name,
        string $value,
        int $expires = null,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax',
        bool $partitioned = false
    );

    /**
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
    abstract public function create(
        string $name,
        string $value,
        int $expires = null,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = true,
        string $sameSite = 'Lax',
        bool $partitioned = false
    ): Cookie;

    /**
     * @return void
     */
    public function send()
    {
        \header( $this->build() );
    }

    /**
     * Build cookie header string
     *
     * @return string
     */
    public function build(): string
    {
        return "Set-Cookie: "
            . ( $this->name() . "=" . \rawurlencode( $this->value() ) . "; ")
            . ( ($this->expires() !== null) ? ("Expires=" . gmdate("D, d M Y H:i:s", $this->expires()) . " GMT; ") : "" )
            . ( ($this->maxAge() !== null) ? ("Max-Age=" . $this->maxAge() . "; ") : "" )
            . ( "Path=" . $this->path() . "; ")
            . ( (! empty($this->domain())) ? ("Domain=" . $this->domain() . "; ") : "")
            . ( ($this->httpOnly() === true) ? "HttpOnly; " : "")
            . ( (! empty($this->sameSite())) ? ("SameSite=" . $this->sameSite() . "; ") : "")
            . ( ($this->secure() === true) ? "Secure; " : "")
            . ( ($this->partitioned() === true) ? "Partitioned" : "")
            ;
    }

    /**
     * Expire the cookie.
     *
     * @return void
     */
    public function expire()
    {
        $this->set(
            $this->name,
            "",
            mktime(0, 0, 0, 1, 1, 1970),
            $this->path,
            $this->domain,
            $this->secure,
            $this->httpOnly
        );
    }

    /**
     * Delete cookie. The cookie should be expired by cookie name.
     *
     * @return void
     */
    public function delete(): void
    {
        $this->expire();

        $this->send();
    }

    /**
     * @param string $value
     */
    public function setValue(string $value) {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return null|string
     */
    public function domain(): ?string
    {
        return $this->domain;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path)
    {
        // Default to '/'
        if(empty($path)) {
            $path = '/';
        }

        $this->path = $path;
    }

    /**
     * @return string
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * @param bool $secure
     */
    public function setSecure(bool $secure)
    {
        $this->secure = $secure;
    }

    /**
     * @return bool
     */
    public function secure(): bool
    {
        return $this->secure;
    }

    /**
     * @param bool $partitioned
     * @return void
     */
    public function setPartitioned(bool $partitioned) {
        $this->partitioned = $partitioned;
    }

    /**
     * @return bool
     */
    public function partitioned(): bool {
        return $this->partitioned;
    }

    /**
     * @param bool $http
     */
    public function setHttpOnly(bool $http)
    {
        $this->httpOnly = $http;
    }

    /**
     * @return bool
     */
    public function httpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * @param int|null $expires
     */
    public function setExpires(?int $expires)
    {
        $this->expires = $expires;
    }

    /**
     * @return ?int
     */
    public function expires(): ?int
    {
        return $this->expires;
    }

    /**
     * @param int|null $maxAge
     * @return void
     */
    public function setMaxAge(?int $maxAge) {
        $this->maxAge = $maxAge;
    }

    /**
     * @return int|null
     */
    public function maxAge(): ?int {
        return $this->maxAge;
    }

    /**
     * @param string|null $sameSite
     * @return void
     * @throws InvalidArgumentException
     */
    public function setSameSite(?string $sameSite) {

        switch ($sameSite) {
            case "strict":
            case "Strict":
            case "lax":
            case "Lax":
            case "none":
            case "None":
                break;
            case null;
                $this->sameSite = null;
                return;
            default:
                throw new InvalidArgumentException("Invalid SameSite value '" . $sameSite . "'");
        }

        $attribute = ucfirst($sameSite);

        if($attribute === "None") {
            if($this->secure() === false) {
                throw new InvalidArgumentException("'SameSite: None;' requires 'Secure;' attribute");
            }
        }

        $this->sameSite = $sameSite;
    }

    /**
     * @return string|null
     */
    public function sameSite(): ?string {
        return $this->sameSite;
    }

    /**
     * @return bool
     */
    public function isExpired(): bool
    {
        if ($this->maxAge() !== null) {
            return $this->maxAge() <= time();
        }

        if ($this->expires() === 0) {
            return false;
        }

        return $this->expires() <= time();
    }

    /**
     * @param array $options
     * @throws InvalidArgumentException
     * @return void
     */
    public function setCookieOptions(array $options)
    {
        foreach ($options as $flagName => $flagValue) {

            $flagName = strtolower($flagName);

            switch ($flagName) {
                case "expire":
                case "expires":
                    $this->setExpires($flagValue);
                    break;

                case "path":
                    $this->setPath($flagValue);
                    break;

                case "domain":
                    $this->setDomain($flagValue);
                    break;

                case "secure":
                    $this->setSecure($flagValue);
                    break;

                case "httponly":
                case "httpOnly":
                    $this->setHttpOnly($flagValue);
                    break;
                case "partitioned":
                    $this->setPartitioned($flagValue);
                    break;

                case "maxAge":
                case "maxage":
                    $this->setMaxAge($flagValue);
                    break;

                case "sameSite":
                case "samesite":
                    $this->setSameSite($flagValue);
                    break;

                default:
                    throw new InvalidArgumentException("Invalid flag '$flagName'");
            }
        }
    }

    /**
     * @return string
     */
    public function toString(): string {
        return $this->build();
    }

    /**
     * @return array
     */
    public function toArray(): array {
        return [
            "name" => $this->name(),
            "value" => $this->value(),
            "expires" => $this->expires(),
            "path" => $this->path(),
            "domain" => $this->domain(),
            "secure" => $this->secure(),
            "httponly" => $this->httpOnly(),
            "samesite" => $this->sameSite(),
            "partitioned" => $this->partitioned(),
            "maxage" => $this->maxAge()
        ];
    }

    /**
     * @return string
     */
    public function __toString(): string {
        return $this->build();
    }
}