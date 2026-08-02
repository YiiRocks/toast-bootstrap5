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

        $toast->add(ToastType::Warning, 'First.');
        $toast->add(ToastType::Warning, 'Second.');

        self::assertSame(['First.', 'Second.'], $this->flash->get(FlashToast::key(ToastType::Warning)));
    }

    public function testAddStoresUnderTypeKey(): void
    {
        $toast = $this->container->get(FlashToastInterface::class);

        $toast->add(ToastType::Success, 'Saved.');

        self::assertSame(['Saved.'], $this->flash->get(FlashToast::key(ToastType::Success)));
    }

    public function testConvenienceMethodsDelegateToMatchingType(): void
    {
        $toast = new FlashToast($this->flash);

        $toast->success('S');
        $toast->error('E');
        $toast->warning('W');
        $toast->info('I');

        self::assertSame(['S'], $this->flash->get(FlashToast::key(ToastType::Success)));
        self::assertSame(['E'], $this->flash->get(FlashToast::key(ToastType::Error)));
        self::assertSame(['W'], $this->flash->get(FlashToast::key(ToastType::Warning)));
        self::assertSame(['I'], $this->flash->get(FlashToast::key(ToastType::Info)));
    }

    public function testKeyIsNamespacedPerType(): void
    {
        self::assertSame('toast.success', FlashToast::key(ToastType::Success));
        self::assertSame('toast.error', FlashToast::key(ToastType::Error));
        self::assertSame('toast.warning', FlashToast::key(ToastType::Warning));
        self::assertSame('toast.info', FlashToast::key(ToastType::Info));
    }
}
