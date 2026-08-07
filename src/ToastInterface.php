<?php

declare(strict_types=1);

namespace YiiRocks\ToastBootstrap5;

use Yiisoft\View\WebView;

interface ToastInterface
{
    /**
     * Renders pending toast flash messages as a Bootstrap 5 `.toast-container` (empty string if
     * none) and registers the show-script on `$view`. A layout calls `<?= $toast->render($this) ?>`.
     */
    public function render(WebView $view): string;
}
