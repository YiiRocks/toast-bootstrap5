<?php

declare(strict_types=1);

use YiiRocks\ToastBootstrap5\ToastInjections;
use YiiRocks\ToastBootstrap5\ToastInterface;
use YiiRocks\ToastBootstrap5\ToastType;
use Yiisoft\Definitions\Reference;

/** @var array $params */

return [
    'yiirocks/toast-bootstrap5' => [
        // Milliseconds before a toast auto-hides; `null` leaves it up until the user dismisses it.
        'delay' => [
            ToastType::Success->value => 4000,
            ToastType::Error->value => null,
            ToastType::Warning->value => 6000,
            ToastType::Info->value => 4000,
        ],
        // Bootstrap Icon names (requires yiirocks/svg-inline + yiirocks/svg-inline-bootstrap to be
        // installed - otherwise silently rendered without an icon). `null` disables a type's icon.
        'icons' => [
            ToastType::Success->value => 'check-circle-fill',
            ToastType::Error->value => 'exclamation-octagon-fill',
            ToastType::Warning->value => 'exclamation-triangle-fill',
            ToastType::Info->value => 'info-circle-fill',
        ],
        // Space-separated Bootstrap position utility classes for the toast container.
        'position' => 'top-0 end-0',
    ],

    'yiisoft/view' => [
        'parameters' => [
            'toast' => Reference::to(ToastInterface::class),
        ],
    ],

    'yiisoft/yii-view-renderer' => [
        'injections' => [
            Reference::to(ToastInjections::class),
        ],
    ],
];
