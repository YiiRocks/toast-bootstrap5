<?php

declare(strict_types=1);

namespace YiiRocks\ToastBootstrap5;

use Override;
use Yiisoft\Yii\View\Renderer\CommonParametersInjectionInterface;
use Yiisoft\Yii\View\Renderer\LayoutParametersInjectionInterface;

final class ToastInjections implements CommonParametersInjectionInterface, LayoutParametersInjectionInterface
{
    public function __construct(
        private ToastInterface $toast,
    ) {}

    #[Override]
    public function getCommonParameters(): array
    {
        return [
            'toast' => $this->toast,
        ];
    }

    #[Override]
    public function getLayoutParameters(): array
    {
        return [
            'toast' => $this->toast,
        ];
    }
}
