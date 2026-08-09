# Toast Bootstrap5

Bootstrap 5 toast notifications for [Yii framework 3.0](http://www.yiiframework.com/), backed by
[`yiisoft/session`](https://github.com/yiisoft/session) flash messages. Queue a message from a
controller, render `$toast` in your layout, done - no manual partials, no custom JS.

[![Packagist Version](https://img.shields.io/packagist/v/yiirocks/toast-bootstrap5.svg)](https://packagist.org/packages/yiirocks/toast-bootstrap5)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/yiirocks/toast-bootstrap5.svg)](https://php.net/)
[![Packagist](https://img.shields.io/packagist/dt/yiirocks/toast-bootstrap5.svg)](https://packagist.org/packages/yiirocks/toast-bootstrap5)
[![GitHub License](https://img.shields.io/github/license/yiirocks/toast-bootstrap5.svg)](https://github.com/yiirocks/toast-bootstrap5/blob/main/LICENSE.md)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/yiirocks/toast-bootstrap5/build.yml?branch=main)](https://github.com/yiirocks/toast-bootstrap5/actions)

Stats for Nerds

[![Coverage](https://img.shields.io/endpoint?url=https%3A%2F%2Fraw.githubusercontent.com%2Fyiirocks%2Ftoast-bootstrap5%2Fbadges%2Fcoverage.json)](https://github.com/yiirocks/toast-bootstrap5/tree/badges)
[![MSI](https://img.shields.io/endpoint?url=https%3A%2F%2Fraw.githubusercontent.com%2Fyiirocks%2Ftoast-bootstrap5%2Fbadges%2Fmsi.json)](https://github.com/yiirocks/toast-bootstrap5/tree/badges)
[![Tests](https://img.shields.io/endpoint?url=https%3A%2F%2Fraw.githubusercontent.com%2Fyiirocks%2Ftoast-bootstrap5%2Fbadges%2Ftests.json)](https://github.com/yiirocks/toast-bootstrap5/tree/badges)
[![Assertions](https://img.shields.io/endpoint?url=https%3A%2F%2Fraw.githubusercontent.com%2Fyiirocks%2Ftoast-bootstrap5%2Fbadges%2Fassertions.json)](https://github.com/yiirocks/toast-bootstrap5/tree/badges)

## Requirements

- PHP 8.3+
- Bootstrap 5's JS bundle loaded on the page (for dismiss/autohide behavior)

## Installation

The package could be installed via composer:

```bash
composer require yiirocks/toast-bootstrap5
```

Optionally, add [Bootstrap Icons](https://icons.getbootstrap.com/) per message type:

```bash
composer require yiirocks/svg-inline-bootstrap
```

## Usage

Queue a message from a controller or anywhere else `FlashToastInterface` is available:

```php
use YiiRocks\ToastBootstrap5\ToastType;

public function __construct(private FlashToastInterface $toast) {}

public function actionSave(): ResponseInterface
{
    // ...
    $this->toast->add(ToastType::SUCCESS, 'Changes saved.');

    return $this->redirect('...');
}
```

Then render `$toast` once in your layout, passing the current view (`$this`) - the default
configuration injects `$toast` into every view:

```php
<?= $toast->render($this) ?>
```

Nothing renders until a message is pending. Four `ToastType` cases are available (`Success`,
`Error`, `Warning`, `Info`), each mapped to a Bootstrap 5 `text-bg-*` color, and multiple messages
of the same type stack as separate toasts.

Available options (auto-hide delay per type, icon per type, container position) can be found in
the [documentation](https://www.yii.rocks/toast-bootstrap5/).

## Unit testing

The package is tested with [Psalm](https://psalm.dev/), [PHPUnit](https://phpunit.de/) and
[Infection](https://infection.github.io/) mutation testing. To run tests:

```bash
composer psalm
composer phpunit
composer infection
```
