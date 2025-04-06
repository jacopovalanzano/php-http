# `Tundra\Http\Cookie`

The `Tundra\Http\Cookie` allows you to manage cookies with ease. It supports the `Parted`, `MaxAge` and `SameSite`
cookie attributes, which are not supported by PHP's `setcookie()` and `setrawcookie()` functions.

Cookie headers must be sent before any other output is sent to the client.
You can send multiple cookies.

### Cookie attributes

Except for `name`, all attributes are optional. The SameSite `None` attribute requires the `Secure` attribute to be set.

Cookies cannot have the same "name", "path" and "domain". If they do, the cookie will be overridden.

* `name`: The name of the cookie. It must not be empty.
* `value`: The value of the cookie. (?string)
* `path`: The path of the cookie. If unset, defaults to `/`. (string)
* `domain`: The domain of the cookie. (?string)
* `expires`: The expiration date of the cookie in Unix timestamp. (int)
* `max-age`: The maximum age of the cookie in seconds. (int)
* `secure`: Whether the cookie should only be sent over HTTPS. (bool)
* `httponly`: Whether the cookie should only be accessible via HTTP. (bool)
* `samesite`: The same-site policy of the cookie. Can be `Lax`, `Strict` or `None`. (string)
* `partitioned`: Whether the cookie is partitioned. (bool)

### Using the `Tundra\Http\Cookie`

Create a new cookie:
```php
$cookie = new \Tundra\Http\Cookie\Cookie("cookie_name", "cookie_value");
```

Cookies should be created and managed using the `CookieHandler` class:

```php
$handler = new \Tundra\Http\Cookie\CookieHandler();
$handler->create("cookie_name", "cookie_value");
```

When a cookie is created (set), it is not sent immediately to the client.
To send the cookie header to the client, call the `send()` method:

```php
$handler->cookie("cookie_name", "/cookie_path", "cookie_domain")->send();
```

To avoid sending headers immediately, you can use the `defer()` method. This way, the cookie header will be sent at the
end of the `Cookie` lifecycle, when the `destructor` is called.
You can still use the `send()` method to send the cookie manually:

```php
$handler->cookie("cookie_name", "/cookie_path", "cookie_domain")->defer();
```

To delete a cookie, call the `delete()` method. The cookie will be deleted by setting an expiration date in the past.
The cookie header will be sent immediately:

```php
$handler->cookie("cookie_name", "/cookie_path", "cookie_domain")->delete();
```

If you want to let the cookie expire without sending the header immediately, call the `expire()` method:

```php
$handler->cookie("cookie_name", "/cookie_path", "cookie_domain")->expire();
```

Use the `destroy()` method if you also want to get rid of the `Cookie` object.
The method supports the "defer" flag:

```php
$handler->destroy("cookie_name", "/cookie_path", "cookie_domain"); // Delete the cookie immediately
$handler->destroy("cookie_name", "/cookie_path", "cookie_domain", true); // Let the cookie be deleted at the end of the `Cookie` lifecycle
```

### Install

```bash
$ cd php-http
$ composer install
```

### Tests

```bash
$ phpunit tests/Http/Cookie/CookieTest.php --stderr
```
