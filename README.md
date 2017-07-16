# Basic Authentication handler
This plugin adds Basic Authentication to a WordPress site.

Note that this plugin requires sending your username and password with every
request, and should only be used for development and testing. We strongly
recommend using the [OAuth 1.0a][oauth] authentication handler for production.

## Installing
1. Download the plugin into your plugins directory
2. Enable in the WordPress admin

## Using
This plugin adds support for Basic Authentication, as specified in [RFC2617][].
Most HTTP clients will allow you to use this authentication natively. Some
examples are listed below.

### cURL

```sh
curl --user admin:password http://example.com/wp-json/
```

### WP_Http

```php
$args = array(
	'headers' => array(
		'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
	),
);
```

[oauth]: https://github.com/WP-API/OAuth1
[RFC2617]: https://tools.ietf.org/html/rfc2617

### How to use with Angular 
(tested with Angular 4.1.3)

```ts

        // Create Wordpress User

        let headers = new Headers();
        headers.append('Content-Type', 'application/x-www-form-urlencoded');
        let userPassEncoded = btoa('username' + ":" + 'password');
        let data = `${uriData}&Authorization=Basic ${userPassEncoded}`;
        return this.http.post('http://wordpress-site-domain/wp-json' + '/users', data, {headers: headers})
```


### CHANGELOG

*2017-07-15*
1. Added option to read data form $_REQUEST["Authorization"]
2. Added option to log values to file, for easy debugging. The log file is written in the plugin folder.


