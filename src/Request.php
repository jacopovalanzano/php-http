<?php

declare(strict_types = 1);

namespace Tundra\Http;

/**
 * Class Request
 *
 * Handles HTTP request methods.
 *
 * Part of this class was forked from the Laravel 8 core.
 *
 * @package TundraCMS
 * @version 1.2.203
 * @category \Tundra\Http
 */

// Help opcache.preload discover always-needed symbols
//class_exists(AcceptHeader::class);
class_exists(FileBag::class);
class_exists(HeaderBag::class);
//class_exists(HeaderUtils::class);
class_exists(InputBag::class);
class_exists(ParameterBag::class);
class_exists(ServerBag::class);

use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\ServerBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\FileBag;

class Request extends \Symfony\Component\HttpFoundation\Request
{

    /**
     * @var array
     */
    private const FORWARDED_PARAMS = [
        self::HEADER_X_FORWARDED_FOR => 'for',
        self::HEADER_X_FORWARDED_HOST => 'host',
        self::HEADER_X_FORWARDED_PROTO => 'proto',
        self::HEADER_X_FORWARDED_PORT => 'host',
    ];

    /**
     * Names for headers that can be trusted when
     * using trusted proxies.
     *
     * The FORWARDED header is the standard as of rfc7239.
     *
     * The other headers are non-standard, but widely used
     * by popular reverse proxies (like Apache mod_proxy or Amazon EC2).
     */
    private const TRUSTED_HEADERS = [
        self::HEADER_FORWARDED => 'FORWARDED',
        self::HEADER_X_FORWARDED_FOR => 'X_FORWARDED_FOR',
        self::HEADER_X_FORWARDED_HOST => 'X_FORWARDED_HOST',
        self::HEADER_X_FORWARDED_PROTO => 'X_FORWARDED_PROTO',
        self::HEADER_X_FORWARDED_PORT => 'X_FORWARDED_PORT',
        //self::HEADER_X_FORWARDED_PREFIX => 'X_FORWARDED_PREFIX',
    ];

    /**
     * @var string
     */
    public $requestUri;

    /**
     * @var string
     */
    public $requestMethod;

    /**
     * @var string
     */
    public $serverProtocol;

    /**
     * @param array                $query      The GET parameters
     * @param array                $request    The POST parameters
     * @param array                $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array                $cookies    The COOKIE parameters
     * @param array                $files      The FILES parameters
     * @param array                $server     The SERVER parameters
     * @param string|resource|null $content    The raw body data
     */
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server);
        $this->initialize($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * @return static
     * @throws BadRequestException
     */
    public function capture(): Request
    {
        $this->enableHttpMethodParameterOverride();
        return $this->createFromBase( $this->createFromGlobals() );
    }

    /**
     * Create a new request from a Symfony instance.
     *
     * @param  \Symfony\Component\HttpFoundation\Request  $request
     * @return static
     */
    public static function createFromBase(\Symfony\Component\HttpFoundation\Request $request)
    {
        $newRequest = (new static)->duplicate(
            $request->query->all(), $request->request->all(), $request->attributes->all(),
            $request->cookies->all(), $request->files->all(), $request->server->all()
        );

        $newRequest->headers->replace($request->headers->all());

        $newRequest->content = $request->content;

        $newRequest->request = $request->request;

        return $newRequest;
    }

    /**
     * Sets the parameters for this request.
     *
     * This method also re-initializes all properties.
     *
     * @param array                $query      The GET parameters
     * @param array                $request    The POST parameters
     * @param array                $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array                $cookies    The COOKIE parameters
     * @param array                $files      The FILES parameters
     * @param array                $server     The SERVER parameters
     * @param string|resource|null $content    The raw body data
     */
    public function initialize(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->request = new InputBag($request);
        $this->query = new InputBag($query);
        $this->attributes = new ParameterBag($attributes);
        $this->cookies = new InputBag($cookies);
        $this->files = new FileBag($files);
        $this->server = new ServerBag($server);
        $this->headers = new HeaderBag($this->server->getHeaders());

        $this->content = $content;
        $this->languages = null;
        $this->charsets = null;
        $this->encodings = null;
        $this->acceptableContentTypes = null;
        $this->pathInfo = null;
        $this->requestUri = $this->getRequestUri();
        $this->requestMethod = $this->getMethod();
        $this->serverProtocol = $this->getProtocolVersion();
        $this->baseUrl = null;
        $this->basePath = null;
        $this->method = null;
        $this->format = null;

        parent::initialize($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    /**
     * Returns the request method.
     */
    public function method(): string
    {
        return $this->getMethod();
    }

    /**
     * Returns the POST super-global, or a specific value.
     *
     * @param mixed $key key
     * @return mixed the key's value or nothing
     */
    public  function post($key = null)
    {

        if (isset($key)) {
            if (isset($_POST[$key])) {
                return $_POST[$key];
            }
        } else {
            return $_POST;
        }
    }

    /**
     * gets/returns PUT
     * @param mixed $key key
     * @return mixed the key's value or nothing
     */
    public  function put($key = null)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {

            $return = array(); // Initialize

            \parse_str(\file_get_contents("php://input"), $return);

            if (isset($key)) {
                if (isset($return[$key])) {
                    return $return[$key];
                }
            } else {
                return $return;
            }
        }
    }

    /**
     * @param mixed $key key
     * @return mixed the key's value or nothing
     */
    public static function delete($key = null)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

            $return = array(); // Initialize

            \parse_str(\file_get_contents("php://input"), $return);

            if (isset($key)) {
                if (isset($return[$key])) {
                    return $return[$key];
                }
            } else {
                return $return;
            }
        }
    }

    /**
     * Returns the value of a specific key of the COOKIE super-global
     * @param mixed $key key
     * @return mixed the key's value or nothing
     */
    public static function cookie($key = null)
    {
        if (isset($key)) {
            if (isset($_COOKIE[$key])) {
                return $_COOKIE[$key];
            }
        } else {
            return $_COOKIE;
        }
    }

    /**
     * Returns the requested URI (path and query string).
     *
     * @return string The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri(): string
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->prepareRequestUri();
        }

        return $this->requestUri;
    }

    /**
     * Gets the request "intended" method.
     *
     * If the X-HTTP-Method-Override header is set, and if the method is a POST,
     * then it is used to determine the "real" intended HTTP method.
     *
     * The _method request parameter can also be used to determine the HTTP method,
     * but only if enableHttpMethodParameterOverride() has been called.
     *
     * The method is always an uppercased string.
     *
     * @return string The request method
     *
     * @see getRealMethod()
     */
    public function getMethod(): string
    {
        if (isset($this->method)) {
            return $this->method;
        }

        $this->method = \strtoupper($this->server->get('REQUEST_METHOD', 'GET'));

        if ('POST' !== $this->method) {
            return $this->method;
        }

        $method = $this->headers->get('X-HTTP-METHOD-OVERRIDE');

        if (! $method && self::$httpMethodParameterOverride) {
            $method = $this->request->get('_method', $this->query->get('_method', 'POST'));
        }

        if (! \is_string($method)) {
            return $this->method;
        }

        $method = \strtoupper($method);

        if (\in_array($method, ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'PATCH', 'PURGE', 'TRACE'], true)) {
            return $this->method = $method;
        }

        if (! \preg_match('/^[A-Z]++$/D', $method)) {
            throw new SuspiciousOperationException(\sprintf('Invalid method override "%s".', $method));
        }

        return $this->method = $method;
    }

    /**
     * Returns the protocol version.
     *
     * If the application is behind a proxy, the protocol version used in the
     * requests between the client and the proxy and between the proxy and the
     * server might be different. This returns the former (from the "Via" header)
     * if the proxy is trusted (see "setTrustedProxies()"), otherwise it returns
     * the latter (from the "SERVER_PROTOCOL" server parameter).
     *
     * @return string|null
     */
    public function getProtocolVersion(): ?string
    {
        return $this->server->get('SERVER_PROTOCOL');
    }

    /**
     * I've only added minor changes - Jacopo Valanzano.
     *
     * The following methods are derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (https://framework.zend.com/license).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (https://www.zend.com/)
     */
    protected function prepareRequestUri()
    {
        $requestUri = ''; // Initialize

        if ('1' == $this->server->get('IIS_WasUrlRewritten') && '' != $this->server->get('UNENCODED_URL')) {
            // IIS7 with URL Rewrite: make sure we get the unencoded URL (double slash problem)
            $requestUri = $this->server->get('UNENCODED_URL');
            $this->server->remove('UNENCODED_URL');
            $this->server->remove('IIS_WasUrlRewritten');
        } elseif ($this->server->has('REQUEST_URI')) {
            $requestUri = $this->server->get('REQUEST_URI');

            if ('' !== $requestUri && '/' === $requestUri[0]) {
                // To only use path and query remove the fragment.
                if (false !== $pos = \strpos($requestUri, '#')) {
                    $requestUri = \substr($requestUri, 0, $pos);
                }
            } else {
                // HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path,
                // only use URL path.
                $uriComponents = \parse_url($requestUri);

                if (isset($uriComponents['path'])) {
                    $requestUri = $uriComponents['path'];
                }

                if (isset($uriComponents['query'])) {
                    $requestUri .= '?' . $uriComponents['query'];
                }
            }
        } elseif ($this->server->has('ORIG_PATH_INFO')) {
            // IIS 5.0, PHP as CGI
            $requestUri = $this->server->get('ORIG_PATH_INFO');
            if ('' != $this->server->get('QUERY_STRING')) {
                $requestUri .= '?' . $this->server->get('QUERY_STRING');
            }
            $this->server->remove('ORIG_PATH_INFO');
        } else {
            $requestUri = \parse_url("/" . \ltrim($_SERVER["REQUEST_URI"] ?: $_SERVER["PHP_SELF"],"/"), \PHP_URL_PATH);
        }

        // normalize the request URI to ease creating sub-requests from this request
        $this->server->set('REQUEST_URI', $requestUri);

        return $requestUri;
    }

    /**
     * @param $name
     * @param $args
     * @return void
     */
    public function __call($name, $args)
    {
        if(\method_exists(parent::class, $name)) {
            return [parent::class, $name](...$args);
        }
    }
}
