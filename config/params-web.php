<?php

declare(strict_types=1);

use YiiRocks\ToastBootstrap5\ToastInjections;
use YiiRocks\ToastBootstrap5\ToastInterface;
use Yiisoft\Definitions\Reference;

return [
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
