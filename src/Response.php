<?php

namespace Tundra\Http;

use ArrayObject;
use Tundra\Support\Contracts\Arrayable;
use Tundra\Support\Contracts\Jsonable;
use Tundra\Support\Contracts\Renderable;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class Response extends SymfonyResponse
{
    /**
     * Create a new HTTP response.
     *
     * @param  mixed  $content
     * @param  int $status
     * @param  array  $headers
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($content = '', int $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, $headers);
        $this->headers = new ResponseHeaderBag($headers);

        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setProtocolVersion('1.0');
    }

    /**
     * Set the content on the response.
     *
     * @param  mixed  $content
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setContent($content): Response
    {
        // If the content is "JSONable" we will set the appropriate header and convert
        // the content to JSON. This is useful when returning something like models
        // from routes that will be automatically transformed to their JSON form.
        if ($this->shouldBeJson($content)) {

            $this->headers->replace(['Content-Type' => 'application/json']);

            $content = $this->morphToJson($content);

            if ($content === false) {
                throw new \InvalidArgumentException( \json_last_error_msg() );
            }
        }

        // If this content implements the "Renderable" interface then we will call the
        // render method on the object so we will avoid any "__toString" exceptions
        // that might be thrown and have their errors obscured by PHP's handling.
        elseif ($content instanceof Renderable) {
            $content = $content->render();
        }

        elseif (\is_callable($content)) {
            $content = $content();
        }

        parent::setContent($content);

        return $this;
    }

    /**
     * Determine if the given content should be turned into JSON.
     *
     * @param  mixed  $content
     * @return bool
     */
    protected function shouldBeJson($content): bool
    {

        if(\is_array($content)) {
            return true;
        }

        return
            ( (@ \json_decode($content)) ?? \json_last_error() === \JSON_ERROR_NONE )
            || $content instanceof ArrayObject
            || \is_array($content);
    }

    /**
     * Morph the given content into JSON.
     *
     * @param  mixed $content
     * @return string|null
     */
    protected function morphToJson($content): ?string
    {
        if ($content instanceof Jsonable) {

            return $content->toJson();

        } elseif ($content instanceof Arrayable) {

            return \json_encode( $content->toArray() );

        }
        return \is_array($content) ? \json_encode($content) : $content;
    }
}
