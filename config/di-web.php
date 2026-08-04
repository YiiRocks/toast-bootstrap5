<?php

declare(strict_types=1);

use YiiRocks\ToastBootstrap5\FlashToast;
use YiiRocks\ToastBootstrap5\FlashToastInterface;
use YiiRocks\ToastBootstrap5\Toast;
use YiiRocks\ToastBootstrap5\ToastInterface;
use YiiRocks\ToastBootstrap5\ToastInjections;
use Yiisoft\Definitions\Reference;

/** @var array $params */

return [
    FlashToastInterface::class => FlashToast::class,

    ToastInterface::class => [
        'class' => Toast::class,
        'setDelays()' => [$params['yiirocks/toast-bootstrap5']['delay']],
        'setIcons()' => [$params['yiirocks/toast-bootstrap5']['icons']],
        'setPosition()' => [$params['yiirocks/toast-bootstrap5']['position']],
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
