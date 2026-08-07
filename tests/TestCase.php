<?php

declare(strict_types=1);

namespace YiiRocks\ToastBootstrap5\tests;

use Psr\Container\ContainerInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Config\Config;
use Yiisoft\Config\ConfigPaths;
use Yiisoft\Config\Modifier\RecursiveMerge;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Session\Flash\Flash;
use Yiisoft\Session\Flash\FlashInterface;
use Yiisoft\Session\Session;
use Yiisoft\Session\SessionInterface;
use Yiisoft\View\WebView;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected ContainerInterface $container;

    protected FlashInterface $flash;

    protected WebView $view;

    protected function setUp(): void
    {
        parent::setUp();
        $this->view = new WebView(sys_get_temp_dir());
        $config = new Config(
            new ConfigPaths(dirname(__DIR__), 'config'),
            '/',
            [RecursiveMerge::groups('params')],
        );
        $containerConfig = ContainerConfig::create()
            ->withDefinitions([
                ...$config->get('di'),
                ...$config->get('di-web'),
                // Session binding an app would normally get from yiisoft/session's own di-web
                // config group - wired here directly so tests don't depend on merging that group.
                SessionInterface::class => Session::class,
                FlashInterface::class => Flash::class,
            ]);
        $this->container = new Container($containerConfig);
        $this->flash = $this->container->get(FlashInterface::class);

        // yiirocks/svg-inline resolves its fallback icon (and, when installed,
        // yiirocks/svg-inline-bootstrap resolves its icon folder) via @vendor - an app normally
        // sets this during bootstrap, so tests have to do it themselves.
        $this->container->get(Aliases::class)->set('@vendor', dirname(__DIR__) . '/vendor');
    }

    protected function tearDown(): void
    {
        $session = $this->container->get(SessionInterface::class);
        if ($session->isActive()) {
            $session->destroy();
        }
        parent::tearDown();
    }

    /**
     * Assembles a full page from the view so JS registered via {@see WebView::registerJs()} is
     * emitted at end of body (POSITION_READY wrapped in `DOMContentLoaded`). Clears the view state.
     */
    protected function renderPage(WebView $view): string
    {
        ob_start();
        $view->beginPage();
        $view->beginBody();
        $view->endBody();
        $view->endPage();

        return (string) ob_get_clean();
    }
}
