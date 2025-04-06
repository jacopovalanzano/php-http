<?php


namespace Tundra\Http\Cookie;

interface CookieInterface
{
    /**
     * Sets a cookie.
     *
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     */
    public function set(
        string $name,
        string $value,
        int $expires = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = false
    );

    /**
     * Sends the cookie header.
     *
     * @return mixed
     */
    public function send();

    /**
     * Builds the "Set-Cookie: ..." header string
     *
     * @return string
     */
    public function build(): string; // Returns the "Set-Cookie: ..." header string

    /**
     * Deletes the cookie; must delete the cookie by sending it with expiration in the past.
     *
     * @return mixed
     */
    public function delete(); // Sends a cookie with expiration in the past

    /**
     * Returns the name of the cookie.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Returns the value of the cookie.
     *
     * @return string
     */
    public function value(): string;

    /**
     * Returns the cookie path.
     *
     * @return string
     */
    public function path(): string;

    /**
     * Returns the cookie domain.
     *
     * @return string|null
     */
    public function domain(): ?string;

    /**
     * Returns the cookie expiration time.
     *
     * @return int|null
     */
    public function expires(): ?int; // Unix timestamp or null

    /**
     * Returns the cookie max age.
     *
     * @return int|null
     */
    public function maxAge(): ?int;  // In seconds

    /**
     * Whether the cookie secure flag is set.
     *
     * @return bool
     */
    public function secure(): bool;

    /**
     * Whether the cookie httponly flag is set.
     *
     * @return bool
     */
    public function httpOnly(): bool;

    /**
     * Returns the cookie SameSite attribute.
     *
     * @return string|null
     */
    public function sameSite(): ?string; // 'Lax', 'Strict', 'None', or null

    /**
     * Whether the cookie is expired.
     *
     * @return bool
     */
    public function isExpired(): bool;

    /**
     * Whether the cookie partitioned flag is set.
     *
     * @return bool
     */
    public function partitioned(): bool;

    /**
     * Returns an array representation of the cookie.
     *
     * @return array
     */
    public function toArray(): array; // For debugging/logging/etc.

    /**
     * Returns a string representation of the cookie.
     *
     * @return string
     */
    public function toString(): string;
}
