<?php

declare(strict_types=1);

namespace YiiRocks\ToastBootstrap5;

interface ToastInterface
{
    /**
     * Renders all pending toast flash messages as a Bootstrap 5 `.toast-container`, or an empty
     * string if there are none. Also implements `__toString()`, so `<?= $toast ?>` in a layout works
     * without an explicit call.
     */
    public function render(): string;
}
