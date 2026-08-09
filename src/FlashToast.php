<?php

declare(strict_types=1);

namespace YiiRocks\ToastBootstrap5;

use Override;
use Yiisoft\Session\Flash\FlashInterface;

final class FlashToast implements FlashToastInterface
{
    public function __construct(
        private FlashInterface $flash,
    ) {}

    #[Override]
    public function add(ToastType $type, string $message): void
    {
        $this->flash->add(self::key($type), $message);
    }

    /**
     * The flash key a given {@see ToastType}'s messages are stored under. Shared with {@see Toast},
     * which reads messages back out under the same key.
     */
    public static function key(ToastType $type): string
    {
        return 'toast.' . $type->value;
    }
}
