<?php

declare(strict_types=1);

namespace YiiRocks\ToastBootstrap5\tests;

use Psr\Container\ContainerInterface;
use YiiRocks\ToastBootstrap5\FlashToastInterface;
use YiiRocks\ToastBootstrap5\Toast;
use YiiRocks\ToastBootstrap5\ToastInterface;
use YiiRocks\ToastBootstrap5\ToastType;

final class ToastTest extends TestCase
{
    public function testAutohideDisabledTypeOmitsDelayAttribute(): void
    {
        $this->container->get(FlashToastInterface::class)->error('Failed.');

        $html = (string) $this->container->get(ToastInterface::class);

        self::assertStringContainsString('data-bs-autohide="false"', $html);
        self::assertStringNotContainsString('data-bs-delay', $html);
    }

    public function testAutohideEnabledTypeIncludesConfiguredDelay(): void
    {
        $this->container->get(FlashToastInterface::class)->success('Saved.');

        $html = (string) $this->container->get(ToastInterface::class);

        self::assertStringContainsString('data-bs-autohide="true"', $html);
        self::assertStringContainsString('data-bs-delay="4000"', $html);
    }

    public function testErrorTypeMapsToBootstrapDangerColor(): void
    {
        $this->container->get(FlashToastInterface::class)->error('Failed.');

        $html = (string) $this->container->get(ToastInterface::class);

        self::assertStringContainsString('text-bg-danger', $html);
    }

    public function testFlashMessagesAreCastToStringEvenWhenSetDirectlyAsNonStrings(): void
    {
        // Bypasses FlashToastInterface to simulate a consumer calling FlashInterface::add()
        // directly with a non-string value - renderToast()'s $message parameter is a hard
        // `string` under strict_types, so this would fatal without the defensive cast.
        $this->flash->add('toast.success', 42);

        $html = (string) $this->container->get(ToastInterface::class);

        self::assertStringContainsString('42', $html);
    }

    public function testInitScriptIsAppendedAfterTheToastContainerMarkup(): void
    {
        $this->container->get(FlashToastInterface::class)->success('Saved.');

        $html = (string) $this->container->get(ToastInterface::class);

        self::assertStringStartsWith('<div class="toast-container', $html);
        self::assertStringEndsWith('</script>', $html);
    }

    public function testMessagesFromDifferentTypesAllRender(): void
    {
        $flashToast = $this->container->get(FlashToastInterface::class);
        foreach (ToastType::cases() as $type) {
            $flashToast->add($type, $type->value . ' message');
        }

        $html = (string) $this->container->get(ToastInterface::class);

        foreach (ToastType::cases() as $type) {
            self::assertStringContainsString($type->value . ' message', $html);
        }
    }

    public function testMultipleMessagesOfSameTypeEachRenderAsSeparateToast(): void
    {
        $flashToast = $this->container->get(FlashToastInterface::class);
        $flashToast->warning('First.');
        $flashToast->warning('Second.');

        $html = (string) $this->container->get(ToastInterface::class);

        self::assertSame(2, substr_count($html, 'toast align-items-center'));
        self::assertStringContainsString('First.', $html);
        self::assertStringContainsString('Second.', $html);
    }

    public function testNonUrgentTypeUsesStatusRoleAndPoliteLiveRegion(): void
    {
        $this->container->get(FlashToastInterface::class)->info('FYI.');

        $html = (string) $this->container->get(ToastInterface::class);

        self::assertStringContainsString('role="status"', $html);
        self::assertStringContainsString('aria-live="polite"', $html);
    }

    public function testRenderIncludesInitScriptOnlyWhenThereAreToasts(): void
    {
        $toast = $this->container->get(ToastInterface::class);
        self::assertStringNotContainsString('<script>', $toast->render());

        $this->container->get(FlashToastInterface::class)->info('FYI.');
        self::assertStringContainsString('bootstrap.Toast.getOrCreateInstance', $toast->render());
    }

    public function testRendersAMessageWithItsBootstrapColorAndEscapesContent(): void
    {
        $this->container->get(FlashToastInterface::class)->success('<b>Saved</b>.');

        $html = (string) $this->container->get(ToastInterface::class);

        self::assertStringContainsString('toast-container', $html);
        self::assertStringContainsString('text-bg-success', $html);
        self::assertStringContainsString('&lt;b&gt;Saved&lt;/b&gt;.', $html);
        self::assertStringNotContainsString('<b>Saved</b>.', $html);
    }

    public function testRendersIconWhenSvgInlineIsRegisteredInTheContainer(): void
    {
        $this->container->get(FlashToastInterface::class)->success('Saved.');

        $html = (string) $this->container->get(ToastInterface::class);

        self::assertStringContainsString('<svg', $html);
    }

    public function testRendersNothingWhenNoMessagesArePending(): void
    {
        $toast = $this->container->get(ToastInterface::class);

        self::assertSame('', $toast->render());
        self::assertSame('', (string) $toast);
    }

    public function testRendersWithoutAnIconWhenContainerLacksSvgInline(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);
        $this->flash->add('toast.success', 'Saved.');
        $toast = new Toast($this->flash, $container);
        $toast->setIcons([ToastType::Success->value => 'check-circle-fill']);

        self::assertStringNotContainsString('<svg', $toast->render());
    }

    public function testRendersWithoutAnIconWhenNoContainerIsInjected(): void
    {
        $this->flash->add('toast.success', 'Saved.');
        $toast = new Toast($this->flash);

        $html = $toast->render();

        self::assertStringNotContainsString('<svg', $html);
        self::assertStringContainsString('Saved.', $html);
    }

    public function testRendersWithoutAnIconWhenTheTypeHasNoneConfigured(): void
    {
        $toast = $this->container->get(ToastInterface::class);
        $toast->setIcons([]);
        $this->container->get(FlashToastInterface::class)->success('Saved.');

        self::assertStringNotContainsString('<svg', $toast->render());
    }

    public function testUrgentTypeUsesAlertRoleAndAssertiveLiveRegion(): void
    {
        $this->container->get(FlashToastInterface::class)->warning('Careful.');

        $html = (string) $this->container->get(ToastInterface::class);

        self::assertStringContainsString('role="alert"', $html);
        self::assertStringContainsString('aria-live="assertive"', $html);
    }
}
