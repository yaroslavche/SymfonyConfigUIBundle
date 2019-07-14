<?php

namespace Yaroslavche\ConfigUIBundle\Model;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;

class BundleConfig
{
    /** @var string $name */
    private $name;
    /** @var string $namespace */
    private $namespace;
    /** @var string $path */
    private $path;
    /** @var TreeBuilder $treeBuilder */
    private $treeBuilder;
    /** @var NodeInterface $tree */
    private $tree;
    /** @var array[] $definitions */
    private $definitions;
    /** @var array[] $defaultConfiguration */
    private $defaultConfiguration;
    /** @var array[] $currentConfiguration */
    private $currentConfiguration;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return BundleConfig
     */
    public function setName(string $name): BundleConfig
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     * @return BundleConfig
     */
    public function setNamespace(string $namespace): BundleConfig
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return BundleConfig
     */
    public function setPath(string $path): BundleConfig
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return TreeBuilder|null
     */
    public function getTreeBuilder(): ?TreeBuilder
    {
        return $this->treeBuilder;
    }

    /**
     * @param TreeBuilder $treeBuilder
     * @return BundleConfig
     */
    public function setTreeBuilder(TreeBuilder $treeBuilder): BundleConfig
    {
        $this->treeBuilder = $treeBuilder;
        return $this;
    }

    /**
     * @return NodeInterface|null
     */
    public function getTree(): ?NodeInterface
    {
        return $this->tree;
    }

    /**
     * @param NodeInterface $tree
     * @return BundleConfig
     */
    public function setTree(NodeInterface $tree): BundleConfig
    {
        $this->tree = $tree;
        return $this;
    }

    /**
     * @return array[]|null
     */
    public function getDefinitions(): ?array
    {
        return $this->definitions;
    }

    /**
     * @param array[] $definitions
     * @return BundleConfig
     */
    public function setDefinitions(array $definitions): BundleConfig
    {
        $this->definitions = $definitions;
        return $this;
    }

    /**
     * @return array[]|null
     */
    public function getDefaultConfiguration(): ?array
    {
        return $this->defaultConfiguration;
    }

    /**
     * @param array[] $defaultConfiguration
     * @return BundleConfig
     */
    public function setDefaultConfiguration(array $defaultConfiguration): BundleConfig
    {
        $this->defaultConfiguration = $defaultConfiguration;
        return $this;
    }

    /**
     * @return array[]|null
     */
    public function getCurrentConfiguration(): ?array
    {
        return $this->currentConfiguration;
    }

    /**
     * @param array[] $currentConfiguration
     * @return BundleConfig
     */
    public function setCurrentConfiguration(array $currentConfiguration): BundleConfig
    {
        $this->currentConfiguration = $currentConfiguration;
        return $this;
    }
}
