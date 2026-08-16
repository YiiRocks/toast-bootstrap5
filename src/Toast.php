<?php

declare(strict_types=1);

namespace YiiRocks\ToastBootstrap5;

use Override;
use Psr\Container\ContainerInterface;
use YiiRocks\SvgInline\Bootstrap\SvgInlineBootstrapInterface;
use YiiRocks\SvgInline\SvgInlineInterface;
use Yiisoft\Html\Html;
use Yiisoft\Html\NoEncode;
use Yiisoft\Html\NoEncodeStringableInterface;
use Yiisoft\Html\Tag\Div;
use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\View\WebView;

use function array_map;
use function explode;
use function is_array;

/**
 * Renders pending {@see FlashToastInterface} messages as Bootstrap 5 toasts. Bound as `$toast` in
 * views (see {@see ToastInjections}); a layout writes `<?= $toast->render($this) ?>`. Dismiss and
 * autohide are Bootstrap's own `data-bs-*` API; the show-script is registered via {@see render()}.
 */
final class Toast implements ToastInterface
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

    #[Override]
    public function render(WebView $view): string
    {
        $toasts = [];
        foreach (ToastType::cases() as $type) {
            foreach ($this->messages($type) as $message) {
                $toasts[] = $this->renderToast($type, $message);
            }

            $this->flash->remove(FlashToast::key($type));
        }

        if ($toasts === []) {
            return '';
        }

        // Bootstrap doesn't auto-init toasts from markup. POSITION_READY emits this at end of body
        // wrapped in DOMContentLoaded, so it runs after Bootstrap's bundle whatever the load order.
        $view->registerJs(
            'document.querySelectorAll(".toast-container .toast")'
                . '.forEach(function (el) { bootstrap.Toast.getOrCreateInstance(el).show(); });',
            WebView::POSITION_READY,
            self::class,
        );

        return Html::div()
            ->class('toast-container', 'position-fixed', 'p-3', ...explode(' ', $this->position))
            ->attribute('style', 'z-index: 1100')
            ->content(...$toasts)
            ->render();
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

        // Guard on the bootstrap icon set, not base svg-inline: since 2.0 $svg->bootstrap() throws
        // if svg-inline-bootstrap isn't installed, and its interface is bound iff it is.
        if ($name === null || $this->container === null || !$this->container->has(SvgInlineBootstrapInterface::class)) {
            return NoEncode::string('');
        }

        /** @var SvgInlineInterface $svg */
        $svg = $this->container->get(SvgInlineInterface::class);

        // bootstrap() (resolved via __call, reads as undefined→mixed to psalm) returns a
        // NoEncodeStringableInterface as of svg-inline 2.0, so it flows into content() unencoded.
        /** @psalm-suppress UndefinedMagicMethod, MixedReturnStatement */
        return $svg->bootstrap($name);
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

        $icon = $this->icon($type);
        $iconElement = (string) $icon !== ''
            ? Html::div()
                ->class('d-flex', 'align-items-center', 'justify-content-center', 'flex-shrink-0')
                ->attribute('style', 'width: 1.5rem; height: 1.5rem;')
                ->content($icon)
            : NoEncode::string('');

        $body = Html::div()
            ->class('toast-body', 'd-flex', 'align-items-center', 'gap-2')
            ->content($iconElement, $message);

        $closeButton = Html::button('')
            ->class($type->closeButtonClass(), 'me-2', 'm-auto')
            ->attribute('data-bs-dismiss', 'toast')
            ->attribute('aria-label', 'Close');

        return $toast->content(Html::div()->class('d-flex')->content($body, $closeButton));
    }
}
