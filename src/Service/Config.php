<?php

namespace Yaroslavche\ConfigUIBundle\Service;

use Closure;
use Exception;
use LogicException;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\BooleanNodeDefinition;
use Symfony\Component\Config\Definition\Builder\EnumNodeDefinition;
use Symfony\Component\Config\Definition\Builder\FloatNodeDefinition;
use Symfony\Component\Config\Definition\Builder\IntegerNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\VariableNodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\NodeInterface;
use Symfony\Component\Filesystem\Filesystem;

class Config
{

    /** @var Filesystem $filesystem */
    private $filesystem;
    /** @var array $bundles */
    private $bundles;
    /** @var array[string => bool] $definitionFields */
    private $definitionFields;

    /**
     * Config constructor.
     * @param array $kernelBundlesMetadata
     * @param array $definitionFields
     */
    public function __construct(
        array $kernelBundlesMetadata,
        array $definitionFields
    ) {
//        $this->filesystem = new Filesystem();
        $this->definitionFields = $definitionFields;
        foreach ($kernelBundlesMetadata as $name => $metadata) {
            $path = $metadata['path'] ?? false;
            $namespace = $metadata['namespace'] ?? false;
            if (!$path || !$namespace) {
                throw new LogicException('Missed expected bundle metadata');
            }
            $this->bundles[$name] = array_merge(
                $metadata,
                [
                    'treeBuilder' => null
                ]
            );
        }
    }


    /**
     * @param string $name
     * @return TreeBuilder
     * @throws ReflectionException
     * @throws Exception
     */
    private function getBundleConfigTreeBuilder(string $name): TreeBuilder
    {
        if (!array_key_exists($name, $this->bundles)) {
            throw new Exception(sprintf('Bundle with name %s not found', $name));
        }
        $bundle = $this->bundles[$name];
        if ($bundle['treeBuilder'] instanceof TreeBuilder) {
            return $bundle['treeBuilder'];
        }
        $configurationFQCN = sprintf('%s\DependencyInjection\Configuration', $bundle['namespace']);
        $configuration = new ReflectionClass($configurationFQCN);

        /** @var ConfigurationInterface $configurationInstance */
        $configurationInstance = new $configurationFQCN(false);
        /** @var TreeBuilder $treeBuilder */
        $treeBuilder = $configuration->getMethod('getConfigTreeBuilder')->invoke($configurationInstance);
        $this->bundles[$name]['treeBuilder'] = $treeBuilder;
        $this->bundles[$name]['tree'] = $treeBuilder->buildTree();

        return $treeBuilder;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getBundleConfigDefinitions(string $name): array
    {
        try {
            $treeBuilder = $this->getBundleConfigTreeBuilder($name);
        } catch (Exception $exception) {
            dump($exception);
            return [];
        }
        /** @var NodeInterface $tree */
        $tree = $this->bundles[$name]['tree'];
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $definitions = [];
        foreach ($rootNode->getChildNodeDefinitions() as $childName => $childDefinition) {
            $definitions[$childName] = $this->handleDefinition($childDefinition);
        }

        return [$tree->getName() => $definitions];
    }

    private function handleDefinition(NodeDefinition $definition)
    {
        $fields = [
            'name', 'normalization', 'validation', 'defaultValue', 'default', 'required', 'deprecationMessage',
            'merge', 'allowEmptyValue', 'nullEquivalent', 'trueEquivalent', 'falseEquivalent', 'pathSeparator',
            'parent', 'attributes'
        ];
        switch (get_class($definition)) {
            case VariableNodeDefinition::class:
            case ScalarNodeDefinition::class:
            case BooleanNodeDefinition::class:
                break;
            case ArrayNodeDefinition::class:
                $fields = array_merge(
                    [
                        'performDeepMerging', 'ignoreExtraKeys', 'removeExtraKeys', 'children', 'prototype',
                        'atLeastOne', 'allowNewKeys', 'key', 'removeKeyItem', 'addDefaults', 'addDefaultChildren',
                        'nodeBuilder', 'normalizeKeys'
                    ],
                    $fields
                );
                break;
            case IntegerNodeDefinition::class:
            case FloatNodeDefinition::class:
                $fields = array_merge(
                    [
                        'min', 'max'
                    ],
                    $fields
                );
                break;
            case EnumNodeDefinition::class:
                $fields = array_merge(
                    [
                        'values'
                    ],
                    $fields
                );
                break;
            default:
                /** @todo check if NodeDefinition, then warn */
        }
        $definitionClosure = function (NodeDefinition $nodeDefinition, array $fields) {
            $definition = [];
            foreach ($fields as $key => $field) {
                $definition[$field] = $nodeDefinition->{$field};
            }
            return $definition;
        };
        $definitionClosure = Closure::bind($definitionClosure, null, $definition);
        $definitionDataArray = $definitionClosure($definition, $fields);
        $definitionDataArray['type'] = $this->getType($definition);
        foreach ($definitionDataArray as $field => $data) {
            if ($this->definitionFields[$field] === false) {
                unset($definitionDataArray[$field]);
            }
        }
        foreach ($definitionDataArray['children'] ?? [] as $name => $childDefinition) {
            $definitionDataArray['children'][$name] = $this->handleDefinition($childDefinition);
        }
        return $definitionDataArray;
    }

    private function getType(NodeDefinition $nodeDefinition): string
    {
        switch (get_class($nodeDefinition)) {
            case VariableNodeDefinition::class:
                return 'variable';
            case ScalarNodeDefinition::class:
                return 'scalar';
            case BooleanNodeDefinition::class:
                return 'boolean';
            case ArrayNodeDefinition::class:
                return 'array';
            case IntegerNodeDefinition::class:
                return 'integer';
            case FloatNodeDefinition::class:
                return 'float';
            case EnumNodeDefinition::class:
                return 'enum';
            default:
                /** @todo check if NodeDefinition, then warn */
        }
    }
}
