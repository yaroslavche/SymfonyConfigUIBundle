<?php

namespace Yaroslavche\ConfigUIBundle\Service;

use Closure;
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
use Symfony\Component\Filesystem\Filesystem;
use Yaroslavche\ConfigUIBundle\Exception\BundleNotFoundException;
use Yaroslavche\ConfigUIBundle\Model\BundleConfig;

class Config
{

    const NODE_TYPE_SCALAR = 'scalar';
    const NODE_TYPE_VARIABLE = 'variable';
    const NODE_TYPE_BOOLEAN = 'boolean';
    const NODE_TYPE_ARRAY = 'array';
    const NODE_TYPE_INTEGER = 'integer';
    const NODE_TYPE_FLOAT = 'float';
    const NODE_TYPE_ENUM = 'enum';

    /** @var Filesystem $filesystem */
    private $filesystem;
    /** @var BundleConfig[] $bundleConfigs */
    private $bundleConfigs;
    /** @var bool[] $definitionFields */
    private $definitionFields;

    /**
     * Config constructor.
     * @param array[] $kernelBundlesMetadata
     * @param bool[] $definitionFields
     */
    public function __construct(array $kernelBundlesMetadata, array $definitionFields)
    {
        $this->filesystem = new Filesystem();
        $this->definitionFields = $definitionFields;
        foreach ($kernelBundlesMetadata as $name => $metadata) {
            $namespace = $metadata['namespace'];
            $path = $metadata['path'];
            if (empty($namespace) || empty($path)) {
                throw new LogicException('Missed expected bundle metadata');
            }
            $bundleConfig = new BundleConfig();
            $bundleConfig
                ->setName($name)
                ->setNamespace($namespace)
                ->setPath($path);
            $this->bundleConfigs[$name] = $bundleConfig;
        }
    }

    /**
     * @param string $name
     * @return BundleConfig
     * @throws ReflectionException
     */
    public function getBundleConfig(string $name): ?BundleConfig
    {
        if (!array_key_exists($name, $this->bundleConfigs)) {
            throw new BundleNotFoundException(sprintf('Bundle with name %s not found', $name));
        }
        $bundleConfig = $this->bundleConfigs[$name];
        $this->load($bundleConfig);
        return $bundleConfig;
    }

    /**
     * @param BundleConfig $bundleConfig
     * @throws ReflectionException
     */
    private function load(BundleConfig $bundleConfig): void
    {
        $treeBuilder = $bundleConfig->getTreeBuilder();
        if ($treeBuilder instanceof TreeBuilder) {
            return;
        }
        $configurationFQCN = sprintf('%s\DependencyInjection\Configuration', $bundleConfig->getNamespace());
        $configuration = new ReflectionClass($configurationFQCN);

        /** @var ConfigurationInterface $configurationInstance */
        $configurationInstance = new $configurationFQCN(false);
        /** @var TreeBuilder $treeBuilder */
        $treeBuilder = $configuration->getMethod('getConfigTreeBuilder')->invoke($configurationInstance);
        $bundleConfig->setTreeBuilder($treeBuilder);
        $bundleConfig->setTree($treeBuilder->buildTree());

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();
        $definitions = [];
        foreach ($rootNode->getChildNodeDefinitions() as $childName => $childDefinition) {
            $definitions[$childName] = $this->handleDefinition($childDefinition);
        }
        $bundleConfig->setDefinitions($definitions);
        $this->parseDefaultConfiguration($bundleConfig);
        $this->loadCurrentConfiguration($bundleConfig);
    }

    /**
     * @param BundleConfig $bundleConfig
     */
    private function parseDefaultConfiguration(BundleConfig $bundleConfig): void
    {
        $configuration = [];
        /** @todo implement */
        $bundleConfig->setDefaultConfiguration($configuration);
    }

    /**
     * @param BundleConfig $bundleConfig
     */
    private function loadCurrentConfiguration(BundleConfig $bundleConfig): void
    {
        $configuration = [];
        /** @todo implement */
        $bundleConfig->setCurrentConfiguration($configuration);
    }

    /**
     * @param NodeDefinition $definition
     * @return array[]
     */
    private function handleDefinition(NodeDefinition $definition): array
    {
        $definitionClosure = function (NodeDefinition $nodeDefinition, array $fields) {
            $definition = [];
            foreach ($fields as $key => $field) {
                $definition[$field] = $nodeDefinition->{$field};
            }
            return $definition;
        };
        $definitionClosure = Closure::bind($definitionClosure, null, $definition);
        $definitionDataArray = $definitionClosure($definition, $this->getNodeDefinitionFields($definition));
        $definitionDataArray['type'] = $this->getNodeType($definition);
        $prototypeField = $definitionDataArray['prototype'] ?? null;
        if ($prototypeField instanceof NodeDefinition) {
            $definitionDataArray['prototype'] = $this->getNodeType($prototypeField);
        }
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

    /**
     * @param NodeDefinition $nodeDefinition
     * @return string[]
     */
    private function getNodeDefinitionFields(NodeDefinition $nodeDefinition): array
    {
        $fields = [
            'name', 'normalization', 'validation', 'defaultValue', 'default', 'required', 'deprecationMessage',
            'merge', 'allowEmptyValue', 'nullEquivalent', 'trueEquivalent', 'falseEquivalent', 'pathSeparator',
            'parent', 'attributes'
        ];
        switch ($this->getNodeType($nodeDefinition)) {
            case self::NODE_TYPE_BOOLEAN:
            case self::NODE_TYPE_VARIABLE:
            case self::NODE_TYPE_SCALAR:
                break;
            case self::NODE_TYPE_ARRAY:
                $fields = array_merge(
                    [
                        'performDeepMerging', 'ignoreExtraKeys', 'removeExtraKeys', 'children', 'prototype',
                        'atLeastOne', 'allowNewKeys', 'key', 'removeKeyItem', 'addDefaults', 'addDefaultChildren',
                        'nodeBuilder', 'normalizeKeys'
                    ],
                    $fields
                );
                break;
            case self::NODE_TYPE_INTEGER:
            case self::NODE_TYPE_FLOAT:
                $fields = array_merge(
                    [
                        'min', 'max'
                    ],
                    $fields
                );
                break;
            case self::NODE_TYPE_ENUM:
                $fields = array_merge(
                    [
                        'values'
                    ],
                    $fields
                );
                break;
        }
        return $fields;
    }

    /**
     * @param NodeDefinition $nodeDefinition
     * @return string
     */
    private function getNodeType(NodeDefinition $nodeDefinition): string
    {
        switch (get_class($nodeDefinition)) {
            case VariableNodeDefinition::class:
                return self::NODE_TYPE_VARIABLE;
            case ScalarNodeDefinition::class:
                return self::NODE_TYPE_SCALAR;
            case BooleanNodeDefinition::class:
                return self::NODE_TYPE_BOOLEAN;
            case ArrayNodeDefinition::class:
                return self::NODE_TYPE_ARRAY;
            case IntegerNodeDefinition::class:
                return self::NODE_TYPE_INTEGER;
            case FloatNodeDefinition::class:
                return self::NODE_TYPE_FLOAT;
            case EnumNodeDefinition::class:
                return self::NODE_TYPE_ENUM;
            default:
                throw new LogicException(sprintf('Unknown NodeDefinition "%s"', get_class($nodeDefinition)));
        }
    }

    /**
     * @param bool $load
     * @return BundleConfig[]
     * @throws ReflectionException
     */
    public function getBundleConfigs(bool $load = false): array
    {
        if ($load) {
            foreach ($this->bundleConfigs as $name => $bundleConfig) {
                $this->load($bundleConfig);
            }
        }
        return $this->bundleConfigs;
    }

    /**
     * @return bool[]
     */
    public function getDefinitionFields(): array
    {
        return $this->definitionFields;
    }
}
