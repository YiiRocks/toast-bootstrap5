<?php

declare(strict_types=1);

namespace YiiRocks\ToastBootstrap5;

use Override;
use Psr\Container\ContainerInterface;
use YiiRocks\SvgInline\SvgInlineInterface;
use Yiisoft\Html\Html;
use Yiisoft\Html\NoEncode;
use Yiisoft\Html\NoEncodeStringableInterface;
use Yiisoft\Html\Tag\Div;

use Yiisoft\Session\Flash\FlashInterface;

use function array_map;
use function explode;
use function is_array;

/**
 * Renders pending {@see FlashToastInterface} messages as Bootstrap 5 toasts. Bound as `$toast` in
 * views/layouts (see {@see ToastInjections}), so `<?= $toast ?>` is all a consumer needs to write.
 *
 * Auto-dismiss/positioning is left entirely to Bootstrap's own `data-bs-*` toast API - the only
 * script emitted here calls `bootstrap.Toast.getOrCreateInstance(el).show()` per toast, since
 * Bootstrap does not auto-initialize toasts from markup alone the way it does dropdowns/collapses.
 * That requires Bootstrap's JS bundle to already be loaded on the page before this renders.
 */
final class Toast implements NoEncodeStringableInterface, ToastInterface
{
    /** @var array<string, int|null> */
    private array $delays = [];

    /** @var array<string, string|null> */
    private array $icons = [];

    private string $position = 'top-0 end-0';

    public function __construct(
        private FlashInterface $flash,
        private ?ContainerInterface $container = null,
    ) {}

    public function __toString(): string
    {
        return $this->render();
    }

    #[Override]
    public function render(): string
    {
        $toasts = [];
        foreach (ToastType::cases() as $type) {
            foreach ($this->messages($type) as $message) {
                $toasts[] = $this->renderToast($type, $message);
            }
        }

        if ($toasts === []) {
            return '';
        }

        $container = Html::div()
            ->class('toast-container', 'position-fixed', 'p-3', ...explode(' ', $this->position))
            ->attribute('style', 'z-index: 1100')
            ->content(...$toasts);

        return $container->render() . '<script>document.querySelectorAll(".toast-container .toast").forEach(function (el) { bootstrap.Toast.getOrCreateInstance(el).show(); });</script>';
    }

    /**
     * @param array<string, int|null> $delays Milliseconds before auto-hiding, keyed by {@see ToastType::$value}.
     * A `null` (or missing) entry disables autohide for that type.
     */
    public function setDelays(array $delays): void
    {
        $this->delays = $delays;
    }

    /**
     * @param array<string, string|null> $icons Bootstrap Icon names, keyed by {@see ToastType::$value}.
     * A `null` (or missing) entry, or `yiirocks/svg-inline` not being installed, renders that type
     * without an icon.
     */
    public function setIcons(array $icons): void
    {
        $this->icons = $icons;
    }

    /**
     * @param string $position Space-separated Bootstrap position utility classes, e.g. `top-0 end-0`.
     */
    public function setPosition(string $position): void
    {
        $this->position = $position;
    }

    private function icon(ToastType $type): NoEncodeStringableInterface
    {
        $name = $this->icons[$type->value] ?? null;
        if ($name === null || $this->container === null || !$this->container->has(SvgInlineInterface::class)) {
            return NoEncode::string('');
        }

        /** @var SvgInlineInterface $svg */
        $svg = $this->container->get(SvgInlineInterface::class);

        // SvgInlineInterface doesn't declare Stringable/NoEncodeStringableInterface itself, only
        // its concrete implementations do - render up front and re-wrap so content() below never
        // HTML-encodes the icon's already-safe SVG markup.
        /** @psalm-suppress InvalidCast */
        return NoEncode::string((string) $svg->bootstrap($name));
    }

    /**
     * @return array<array-key, string>
     */
    private function messages(ToastType $type): array
    {
        /** @psalm-suppress MixedAssignment FlashInterface::get() is untyped upstream. */
        $value = $this->flash->get(FlashToast::key($type));

        return is_array($value) ? array_map(strval(...), $value) : [];
    }

    private function renderToast(ToastType $type, string $message): Div
    {
        $delay = $this->delays[$type->value] ?? null;

        $toast = Html::div()
            ->class('toast', 'align-items-center', 'text-bg-' . $type->bootstrapColor(), 'border-0')
            ->attribute('role', $type->isUrgent() ? 'alert' : 'status')
            ->attribute('aria-live', $type->isUrgent() ? 'assertive' : 'polite')
            ->attribute('aria-atomic', 'true')
            ->attribute('data-bs-autohide', $delay !== null ? 'true' : 'false');

        if ($delay !== null) {
            $toast = $toast->attribute('data-bs-delay', (string) $delay);
        }

        $body = Html::div()
            ->class('toast-body', 'd-flex', 'align-items-center', 'gap-2')
            ->content($this->icon($type), $message);

        $closeButton = Html::button('')
            ->class($type->closeButtonClass(), 'me-2', 'm-auto')
            ->attribute('data-bs-dismiss', 'toast')
            ->attribute('aria-label', 'Close');

        return $toast->content(Html::div()->class('d-flex')->content($body, $closeButton));
    }
}
