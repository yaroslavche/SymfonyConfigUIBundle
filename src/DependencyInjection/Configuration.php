<?php

namespace Yaroslavche\ConfigUIBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(YaroslavcheConfigUIExtension::EXTENSION_ALIAS);
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->arrayNode('definition_fields')->prototype('boolean')->end()
            ->end();
        return $treeBuilder;
    }
}
