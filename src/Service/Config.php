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
use Symfony\Component\Config\Definition\Builder\NumericNodeDefinition;
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

    public function __construct(
        array $kernelBundlesMetadata
    )
    {
        $this->filesystem = new Filesystem();
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
        dump(
            $this->getBundleConfigArray('FrameworkBundle'),
            $this->getBundleConfigArray('MakerBundle'),
            $this->getBundleConfigArray('TwigBundle'),
            $this->getBundleConfigArray('WebProfilerBundle'),
            $this->getBundleConfigArray('YaroslavcheConfigUIBundle')
        );
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

    public function getBundleConfigArray(string $name): array
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

        $defaults = [];
        /** @todo BAD, WRONG */
        foreach ($definitions as $name => $definition) {
            $defaults[$name] = $definition['defaultValue'];
            foreach ($definition['children'] ?? [] as $childName => $childDefinition) {
                $defaults[$name][$childName] = $childDefinition['defaultValue'];
            }
        }
        return [$tree->getName() => $defaults];
    }

    /**
     * @todo
     * @param NodeDefinition $definition
     * @return array|null
     */
    private
    function handleDefinition(NodeDefinition $definition)
    {
        $definitionFQCN = get_class($definition);
        $definitionClosure = null;
        switch ($definitionFQCN) {
            case ScalarNodeDefinition::class:
            case BooleanNodeDefinition::class:
            case VariableNodeDefinition::class:
                $definitionClosure = function (NodeDefinition $nodeDefinition) {
                    return
                        [
                            'name' => $nodeDefinition->name,
                            'normalization' => $nodeDefinition->normalization,
                            'validation' => $nodeDefinition->validation,
                            'defaultValue' => $nodeDefinition->defaultValue,
                            'default' => $nodeDefinition->default,
                            'required' => $nodeDefinition->required,
                            'deprecationMessage' => $nodeDefinition->deprecationMessage,
                            'merge' => $nodeDefinition->merge,
                            'allowEmptyValue' => $nodeDefinition->allowEmptyValue,
                            'nullEquivalent' => $nodeDefinition->nullEquivalent,
                            'trueEquivalent' => $nodeDefinition->trueEquivalent,
                            'falseEquivalent' => $nodeDefinition->falseEquivalent,
                            'pathSeparator' => $nodeDefinition->pathSeparator,
                            'parent' => $nodeDefinition->parent,
                            'attributes' => $nodeDefinition->attributes,
                        ];
                };
                break;
            case ArrayNodeDefinition::class:
                $definitionClosure = function (ArrayNodeDefinition $nodeDefinition) {
                    return
                        [
                            'performDeepMerging' => $nodeDefinition->performDeepMerging,
                            'ignoreExtraKeys' => $nodeDefinition->ignoreExtraKeys,
                            'removeExtraKeys' => $nodeDefinition->removeExtraKeys,
                            'children' => $nodeDefinition->children,
                            'prototype' => $nodeDefinition->prototype,
                            'atLeastOne' => $nodeDefinition->atLeastOne,
                            'allowNewKeys' => $nodeDefinition->allowNewKeys,
                            'key' => $nodeDefinition->key,
                            'removeKeyItem' => $nodeDefinition->removeKeyItem,
                            'addDefaults' => $nodeDefinition->addDefaults,
                            'addDefaultChildren' => $nodeDefinition->addDefaultChildren,
                            'nodeBuilder' => $nodeDefinition->nodeBuilder,
                            'normalizeKeys' => $nodeDefinition->normalizeKeys,
                            'name' => $nodeDefinition->name,
                            'normalization' => $nodeDefinition->normalization,
                            'validation' => $nodeDefinition->validation,
                            'defaultValue' => $nodeDefinition->defaultValue,
                            'default' => $nodeDefinition->default,
                            'required' => $nodeDefinition->required,
                            'deprecationMessage' => $nodeDefinition->deprecationMessage,
                            'merge' => $nodeDefinition->merge,
                            'allowEmptyValue' => $nodeDefinition->allowEmptyValue,
                            'nullEquivalent' => $nodeDefinition->nullEquivalent,
                            'trueEquivalent' => $nodeDefinition->trueEquivalent,
                            'falseEquivalent' => $nodeDefinition->falseEquivalent,
                            'pathSeparator' => $nodeDefinition->pathSeparator,
                            'parent' => $nodeDefinition->parent,
                            'attributes' => $nodeDefinition->attributes
                        ];
                };
                break;
            case IntegerNodeDefinition::class:
            case FloatNodeDefinition::class:
                $definitionClosure = function (NumericNodeDefinition $nodeDefinition) {
                    return
                        [
                            'min' => $nodeDefinition->min,
                            'max' => $nodeDefinition->max,
                            'name' => $nodeDefinition->name,
                            'normalization' => $nodeDefinition->normalization,
                            'validation' => $nodeDefinition->validation,
                            'defaultValue' => $nodeDefinition->defaultValue,
                            'default' => $nodeDefinition->default,
                            'required' => $nodeDefinition->required,
                            'deprecationMessage' => $nodeDefinition->deprecationMessage,
                            'merge' => $nodeDefinition->merge,
                            'allowEmptyValue' => $nodeDefinition->allowEmptyValue,
                            'nullEquivalent' => $nodeDefinition->nullEquivalent,
                            'trueEquivalent' => $nodeDefinition->trueEquivalent,
                            'falseEquivalent' => $nodeDefinition->falseEquivalent,
                            'pathSeparator' => $nodeDefinition->pathSeparator,
                            'parent' => $nodeDefinition->parent,
                            'attributes' => $nodeDefinition->attributes,
                        ];
                };
                break;
            case EnumNodeDefinition::class:
                $definitionClosure = function (EnumNodeDefinition $nodeDefinition) {
                    return
                        [
                            'values' => $nodeDefinition->values,
                            'name' => $nodeDefinition->name,
                            'normalization' => $nodeDefinition->normalization,
                            'validation' => $nodeDefinition->validation,
                            'defaultValue' => $nodeDefinition->defaultValue,
                            'default' => $nodeDefinition->default,
                            'required' => $nodeDefinition->required,
                            'deprecationMessage' => $nodeDefinition->deprecationMessage,
                            'merge' => $nodeDefinition->merge,
                            'allowEmptyValue' => $nodeDefinition->allowEmptyValue,
                            'nullEquivalent' => $nodeDefinition->nullEquivalent,
                            'trueEquivalent' => $nodeDefinition->trueEquivalent,
                            'falseEquivalent' => $nodeDefinition->falseEquivalent,
                            'pathSeparator' => $nodeDefinition->pathSeparator,
                            'parent' => $nodeDefinition->parent,
                            'attributes' => $nodeDefinition->attributes,
                        ];
                };
                break;
            default:
                dump(sprintf('TODO: Implement handle %s', $definitionFQCN));
        }
        if ($definitionClosure instanceof Closure) {
            $definitionClosure = Closure::bind($definitionClosure, null, $definition);
            $definitionDataArray = $definitionClosure($definition);
            if ($definition instanceof ArrayNodeDefinition) {
                foreach ($definitionDataArray['children'] ?? [] as $name => $childDefinition) {
                    $definitionDataArray['children'][$name] = $this->handleDefinition($childDefinition);
                }
            }
            return $definitionDataArray;
        }
        return null;
    }

}
