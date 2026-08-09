<?php

declare(strict_types=1);

namespace YiiRocks\ToastBootstrap5;

/**
 * Queues toast messages onto the session flash, one call per message. Multiple messages of the
 * same {@see ToastType} stack rather than overwrite each other.
 */
interface FlashToastInterface
{
    public function add(ToastType $type, string $message): void;
}
