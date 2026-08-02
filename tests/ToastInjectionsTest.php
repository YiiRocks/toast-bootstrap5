<?php

declare(strict_types=1);

namespace YiiRocks\ToastBootstrap5\tests;

use YiiRocks\ToastBootstrap5\ToastInjections;
use YiiRocks\ToastBootstrap5\ToastInterface;

final class ToastInjectionsTest extends TestCase
{
    public function testCommonAndLayoutParametersExposeTheToastService(): void
    {
        $toast = $this->container->get(ToastInterface::class);
        $injections = new ToastInjections($toast);

        self::assertSame(['toast' => $toast], $injections->getCommonParameters());
        self::assertSame(['toast' => $toast], $injections->getLayoutParameters());
    }
}
