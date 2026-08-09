<?php

declare(strict_types=1);

namespace YiiRocks\ToastBootstrap5\tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use YiiRocks\ToastBootstrap5\ToastType;

final class ToastTypeTest extends TestCase
{
    /**
     * @return iterable<string, array{ToastType, string}>
     */
    public static function bootstrapColorProvider(): iterable
    {
        yield 'success' => [ToastType::SUCCESS, 'success'];
        yield 'error maps to danger' => [ToastType::ERROR, 'danger'];
        yield 'warning' => [ToastType::WARNING, 'warning'];
        yield 'info' => [ToastType::INFO, 'info'];
    }

    /**
     * @return iterable<string, array{ToastType, string}>
     */
    public static function closeButtonClassProvider(): iterable
    {
        yield 'success is base + white' => [ToastType::SUCCESS, 'btn-close btn-close-white'];
        yield 'error is base + white' => [ToastType::ERROR, 'btn-close btn-close-white'];
        yield 'warning is base only' => [ToastType::WARNING, 'btn-close'];
        yield 'info is base only' => [ToastType::INFO, 'btn-close'];
    }

    /**
     * @return iterable<string, array{ToastType, bool}>
     */
    public static function isUrgentProvider(): iterable
    {
        yield 'success is not urgent' => [ToastType::SUCCESS, false];
        yield 'error is urgent' => [ToastType::ERROR, true];
        yield 'warning is urgent' => [ToastType::WARNING, true];
        yield 'info is not urgent' => [ToastType::INFO, false];
    }

    #[DataProvider('bootstrapColorProvider')]
    public function testBootstrapColor(ToastType $type, string $expected): void
    {
        self::assertSame($expected, $type->bootstrapColor());
    }

    #[DataProvider('closeButtonClassProvider')]
    public function testCloseButtonClass(ToastType $type, string $expected): void
    {
        self::assertSame($expected, $type->closeButtonClass());
    }

    #[DataProvider('isUrgentProvider')]
    public function testIsUrgent(ToastType $type, bool $expected): void
    {
        self::assertSame($expected, $type->isUrgent());
    }
}
