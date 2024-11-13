# Zhylon Bridge for Laravel Mailer

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zhylon/laravel-mailer.svg?style=flat-square)](https://packagist.org/packages/zhylon/laravel-mailer)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/zhylon/laravel-mailer/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/zhylon/laravel-mailer/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/zhylon/laravel-mailer/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/zhylon/laravel-mailer/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/zhylon/laravel-mailer.svg?style=flat-square)](https://packagist.org/packages/zhylon/laravel-mailer)

Provides Zhylon integration for Symfony Mailer.
We have used the Postmark API and Bridge as a base for this package.
See the [Postmark Bridge](https://github.com/symfony/postmark-mailer) or [Postmark API](https://postmarkapp.com/developer/api/overview) for more information.

## Installation

You can install the package via composer:

```bash
composer require zhylon/laravel-mailer
```

## Configuration

Configuration example for `config/mail.php`:

```php
'zhylon-mail' => [
    'transport' => 'zhylon-mail',
    // 'message_stream_id' => env('ZHYLONMAIL_MESSAGE_STREAM_ID'),
    // 'client' => [
    //     'timeout' => 5,
    // ],
],
```

Also you need to add the zhylon-mailer transport to your `config/services.php`:

```php
'zhylon-mail' => [
        'token' => env('ZHYLONMAIL_TOKEN'),
    ],
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [TobyMaxham](https://github.com/TobyMaxham)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
