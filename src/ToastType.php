<?php

declare(strict_types=1);

namespace YiiRocks\ToastBootstrap5;

enum ToastType: string
{
    case Error = 'error';
    case Info = 'info';
    case Success = 'success';
    case Warning = 'warning';

    /**
     * The Bootstrap 5 contextual color name backing this type's `text-bg-*` class.
     */
    public function bootstrapColor(): string
    {
        return match ($this) {
            self::Error => 'danger',
            default => $this->value,
        };
    }

    /**
     * `text-bg-success`/`text-bg-danger` are dark backgrounds, so their dismiss button needs the
     * white variant to stay visible; `text-bg-warning`/`text-bg-info` are light and use the default.
     */
    public function closeButtonClass(): string
    {
        return match ($this) {
            self::Success, self::Error => 'btn-close-white',
            self::Warning, self::Info => 'btn-close',
        };
    }

    /**
     * Whether this type needs an assertive ARIA live region (`role="alert"`) rather than a polite
     * one (`role="status"`) — errors/warnings interrupt, success/info can wait their turn.
     */
    public function isUrgent(): bool
    {
        return match ($this) {
            self::Error, self::Warning => true,
            self::Success, self::Info => false,
        };
    }
}
