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

    /**
     * Kernel constructor.
     * @param string $env
     * @param bool $debug
     */
    public function __construct(string $env = 'test', bool $debug = true)
    {
        parent::__construct($env, $debug);
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
     * @return array
     */
    public function getBundleConfig(): array
    {
        return [
            'definition_fields' => [
                'name' => true,
                'normalization' => false,
                'validation' => false,
                'defaultValue' => true,
                'default' => true,
                'required' => true,
                'deprecationMessage' => true,
                'merge' => false,
                'allowEmptyValue' => true,
                'nullEquivalent' => false,
                'trueEquivalent' => false,
                'falseEquivalent' => false,
                'pathSeparator' => false,
                'parent' => false,
                'attributes' => true,
                'performDeepMerging' => false,
                'ignoreExtraKeys' => false,
                'removeExtraKeys' => false,
                'children' => true,
                'prototype' => true,
                'atLeastOne' => true,
                'allowNewKeys' => false,
                'key' => false,
                'removeKeyItem' => false,
                'addDefaults' => false,
                'addDefaultChildren' => false,
                'nodeBuilder' => false,
                'normalizeKeys' => false,
                'min' => false,
                'max' => false,
                'values' => false,
                'type' => true,
            ]
        ];
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
        $c->loadFromExtension('yaroslavche_config_ui', $this->getBundleConfig());
    }
}
