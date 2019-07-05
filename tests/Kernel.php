<?php

namespace Yaroslavche\ConfigUIBundle\Tests;

use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as SymfonyKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use Yaroslavche\ConfigUIBundle\YaroslavcheConfigUIBundle;

class Kernel extends SymfonyKernel
{
    use MicroKernelTrait;

    /** @var array $bundleConfig configuration for translation bundle */
    private $bundleConfig;

    public function __construct(?array $bundleConfig = null)
    {
        $this->bundleConfig = $bundleConfig ?? [];
        parent::__construct('test', true);
    }

    public function registerBundles()
    {
        return [
            new YaroslavcheConfigUIBundle(),
            new FrameworkBundle()
        ];
    }

    public function getCacheDir()
    {
        return __DIR__ . '/cache/' . spl_object_hash($this);
    }

    /**
     * @param RouteCollectionBuilder $routes
     * @throws LoaderLoadException
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import(__DIR__ . '/../src/Resources/config/routes.xml');
    }

    /**
     * @param ContainerBuilder $c
     * @param LoaderInterface $loader
     */
    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', [
            'secret' => 'test'
        ]);
        $c->loadFromExtension('yaroslavche_config_ui', $this->bundleConfig);
    }
}
