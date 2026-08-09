<?php

declare(strict_types=1);

namespace YiiRocks\ToastBootstrap5\tests;

use YiiRocks\ToastBootstrap5\FlashToast;
use YiiRocks\ToastBootstrap5\FlashToastInterface;
use YiiRocks\ToastBootstrap5\ToastType;

final class FlashToastTest extends TestCase
{
    public function testAddStacksMultipleMessagesOfSameType(): void
    {
        $toast = $this->container->get(FlashToastInterface::class);

        $toast->add(ToastType::WARNING, 'First.');
        $toast->add(ToastType::WARNING, 'Second.');

        self::assertSame(['First.', 'Second.'], $this->flash->get(FlashToast::key(ToastType::WARNING)));
    }

    public function testAddStoresUnderTypeKey(): void
    {
        $toast = $this->container->get(FlashToastInterface::class);

        $toast->add(ToastType::SUCCESS, 'Saved.');

        self::assertSame(['Saved.'], $this->flash->get(FlashToast::key(ToastType::SUCCESS)));
    }

    public function testKeyIsNamespacedPerType(): void
    {
        self::assertSame('toast.success', FlashToast::key(ToastType::SUCCESS));
        self::assertSame('toast.error', FlashToast::key(ToastType::ERROR));
        self::assertSame('toast.warning', FlashToast::key(ToastType::WARNING));
        self::assertSame('toast.info', FlashToast::key(ToastType::INFO));
    }
}
