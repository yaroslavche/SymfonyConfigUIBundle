<?php

namespace Yaroslavche\ConfigUIBundle\DependencyInjection;

use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class YaroslavcheConfigUIExtension extends Extension
{
    const EXTENSION_ALIAS = 'yaroslavche_config_ui';

    /**
     * Loads a specific configuration.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     * @throws \Exception When loading config failed
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $kernelBundlesMetadata = $container->getParameter('kernel.bundles_metadata');
        if (!isset($kernelBundlesMetadata['FrameworkBundle'])) {
            throw new RuntimeException('FrameworkBundle must be installed.');
        }
        /*
        $container->prependExtensionConfig(static::EXTENSION_ALIAS, [
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
                'min' => true,
                'max' => true,
                'values' => true,
                'type' => true,
            ]
        ]);
        */
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $kernelProjectDir = $container->getParameter('kernel.project_dir');

        $configServiceDefinition = $container->getDefinition('yaroslavche_config_ui.service.config');
        $configServiceDefinition->setArgument('$kernelProjectDir', $kernelProjectDir);
        $configServiceDefinition->setArgument('$kernelBundlesMetadata', $kernelBundlesMetadata);
        $configServiceDefinition->setArgument('$definitionFields', $config['definition_fields']);
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return static::EXTENSION_ALIAS;
    }
}
