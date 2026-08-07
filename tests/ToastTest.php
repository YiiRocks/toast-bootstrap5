<?php

declare(strict_types=1);

namespace YiiRocks\ToastBootstrap5\tests;

use Psr\Container\ContainerInterface;
use YiiRocks\SvgInline\Bootstrap\SvgInlineBootstrapInterface;
use YiiRocks\SvgInline\SvgInlineInterface;
use YiiRocks\ToastBootstrap5\FlashToastInterface;
use YiiRocks\ToastBootstrap5\Toast;
use YiiRocks\ToastBootstrap5\ToastInterface;
use YiiRocks\ToastBootstrap5\ToastType;

final class ToastTest extends TestCase
{
    public function testAutohideDisabledTypeOmitsDelayAttribute(): void
    {
        $this->container->get(FlashToastInterface::class)->error('Failed.');

        $html = $this->container->get(ToastInterface::class)->render($this->view);

        self::assertStringContainsString('data-bs-autohide="false"', $html);
        self::assertStringNotContainsString('data-bs-delay', $html);
    }

    public function testAutohideEnabledTypeIncludesConfiguredDelay(): void
    {
        $this->container->get(FlashToastInterface::class)->success('Saved.');

        $html = $this->container->get(ToastInterface::class)->render($this->view);

        self::assertStringContainsString('data-bs-autohide="true"', $html);
        self::assertStringContainsString('data-bs-delay="4000"', $html);
    }

    public function testErrorTypeMapsToBootstrapDangerColor(): void
    {
        $this->container->get(FlashToastInterface::class)->error('Failed.');

        $html = $this->container->get(ToastInterface::class)->render($this->view);

        self::assertStringContainsString('text-bg-danger', $html);
    }

    public function testFlashMessagesAreCastToStringEvenWhenSetDirectlyAsNonStrings(): void
    {
        // Bypasses FlashToastInterface to simulate a consumer calling FlashInterface::add()
        // directly with a non-string value - renderToast()'s $message parameter is a hard
        // `string` under strict_types, so this would fatal without the defensive cast.
        $this->flash->add('toast.success', 42);

        $html = $this->container->get(ToastInterface::class)->render($this->view);

        self::assertStringContainsString('42', $html);
    }

    public function testRenderReturnsOnlyTheContainerMarkupWithoutAnInlineScript(): void
    {
        $this->container->get(FlashToastInterface::class)->success('Saved.');

        $html = $this->container->get(ToastInterface::class)->render($this->view);

        // The show-script is registered on the view, not concatenated into the returned markup.
        self::assertStringStartsWith('<div class="toast-container', $html);
        self::assertStringEndsWith('</div>', $html);
        self::assertStringNotContainsString('<script', $html);
    }

    public function testRegistersShowScriptOnTheViewWrappedInDomContentLoaded(): void
    {
        // registerJs(POSITION_READY) makes yiisoft/view emit the script at end of body, wrapped in
        // DOMContentLoaded, so it runs after Bootstrap's own bundle regardless of load order.
        $this->container->get(FlashToastInterface::class)->success('Saved.');

        $this->container->get(ToastInterface::class)->render($this->view);
        $page = $this->renderPage($this->view);

        self::assertStringContainsString("document.addEventListener('DOMContentLoaded'", $page);
        self::assertStringContainsString(
            'document.querySelectorAll(".toast-container .toast").forEach(function (el)'
                . ' { bootstrap.Toast.getOrCreateInstance(el).show(); });',
            $page,
        );
    }

    public function testRegistersTheShowScriptOnlyWhenThereAreToasts(): void
    {
        $toast = $this->container->get(ToastInterface::class);

        self::assertSame('', $toast->render($this->view));
        self::assertStringNotContainsString('getOrCreateInstance', $this->renderPage($this->view));

        $this->container->get(FlashToastInterface::class)->info('FYI.');
        $toast->render($this->view);
        self::assertStringContainsString('getOrCreateInstance', $this->renderPage($this->view));
    }

    public function testMessagesFromDifferentTypesAllRender(): void
    {
        $flashToast = $this->container->get(FlashToastInterface::class);
        foreach (ToastType::cases() as $type) {
            $flashToast->add($type, $type->value . ' message');
        }

        $html = $this->container->get(ToastInterface::class)->render($this->view);

        foreach (ToastType::cases() as $type) {
            self::assertStringContainsString($type->value . ' message', $html);
        }
    }

    public function testMultipleMessagesOfSameTypeEachRenderAsSeparateToast(): void
    {
        $flashToast = $this->container->get(FlashToastInterface::class);
        $flashToast->warning('First.');
        $flashToast->warning('Second.');

        $html = $this->container->get(ToastInterface::class)->render($this->view);

        self::assertSame(2, substr_count($html, 'toast align-items-center'));
        self::assertStringContainsString('First.', $html);
        self::assertStringContainsString('Second.', $html);
    }

    public function testNonUrgentTypeUsesStatusRoleAndPoliteLiveRegion(): void
    {
        $this->container->get(FlashToastInterface::class)->info('FYI.');

        $html = $this->container->get(ToastInterface::class)->render($this->view);

        self::assertStringContainsString('role="status"', $html);
        self::assertStringContainsString('aria-live="polite"', $html);
    }

    public function testRendersAMessageWithItsBootstrapColorAndEscapesContent(): void
    {
        $this->container->get(FlashToastInterface::class)->success('<b>Saved</b>.');

        $html = $this->container->get(ToastInterface::class)->render($this->view);

        self::assertStringContainsString('toast-container', $html);
        self::assertStringContainsString('text-bg-success', $html);
        self::assertStringContainsString('&lt;b&gt;Saved&lt;/b&gt;.', $html);
        self::assertStringNotContainsString('<b>Saved</b>.', $html);
    }

    public function testRendersIconWhenSvgInlineIsRegisteredInTheContainer(): void
    {
        $this->container->get(FlashToastInterface::class)->success('Saved.');

        $html = $this->container->get(ToastInterface::class)->render($this->view);

        self::assertStringContainsString('<svg', $html);
    }

    public function testRendersNothingWhenNoMessagesArePending(): void
    {
        $toast = $this->container->get(ToastInterface::class);

        self::assertSame('', $toast->render($this->view));
    }

    public function testRendersWithoutAnIconWhenContainerLacksSvgInline(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturn(false);
        $this->flash->add('toast.success', 'Saved.');
        $toast = new Toast($this->flash, $container);
        $toast->setIcons([ToastType::Success->value => 'check-circle-fill']);

        self::assertStringNotContainsString('<svg', $toast->render($this->view));
    }

    public function testRendersWithoutAnIconWhenSvgInlineBootstrapIsNotInstalled(): void
    {
        // Base yiirocks/svg-inline is present, but the bootstrap icon set (yiirocks/svg-inline-bootstrap)
        // is not - icon() must skip the icon rather than call ->bootstrap() and throw BadMethodCallException.
        $container = $this->createStub(ContainerInterface::class);
        $container->method('has')->willReturnCallback(
            static fn(string $id): bool => $id === SvgInlineInterface::class,
        );
        $this->flash->add('toast.success', 'Saved.');
        $toast = new Toast($this->flash, $container);
        $toast->setIcons([ToastType::Success->value => 'check-circle-fill']);

        $html = $toast->render($this->view);

        self::assertStringNotContainsString('<svg', $html);
        self::assertStringContainsString('Saved.', $html);
    }

    public function testRendersWithoutAnIconWhenNoContainerIsInjected(): void
    {
        $this->flash->add('toast.success', 'Saved.');
        $toast = new Toast($this->flash);

        $html = $toast->render($this->view);

        self::assertStringNotContainsString('<svg', $html);
        self::assertStringContainsString('Saved.', $html);
    }

    public function testRendersWithoutAnIconWhenTheTypeHasNoneConfigured(): void
    {
        $toast = $this->container->get(ToastInterface::class);
        $toast->setIcons([]);
        $this->container->get(FlashToastInterface::class)->success('Saved.');

        self::assertStringNotContainsString('<svg', $toast->render($this->view));
    }

    public function testUrgentTypeUsesAlertRoleAndAssertiveLiveRegion(): void
    {
        $this->container->get(FlashToastInterface::class)->warning('Careful.');

        $html = $this->container->get(ToastInterface::class)->render($this->view);

        self::assertStringContainsString('role="alert"', $html);
        self::assertStringContainsString('aria-live="assertive"', $html);
    }
}
