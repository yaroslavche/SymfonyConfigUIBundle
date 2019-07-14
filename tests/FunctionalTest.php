<?php

namespace Yaroslavche\ConfigUIBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;
use Yaroslavche\ConfigUIBundle\DependencyInjection\YaroslavcheConfigUIExtension;
use Yaroslavche\ConfigUIBundle\Model\BundleConfig;
use Yaroslavche\ConfigUIBundle\Service\Config;

class FunctionalTest extends TestCase
{
    /** @var Kernel $kernel */
    private $kernel;
    /** @var Config $configService */
    private $configService;

    protected function setUp(): void
    {
        $this->kernel = new Kernel();
        $this->kernel->boot();
        $container = $this->kernel->getContainer();
        $this->configService = $container->get('yaroslavche_config_ui.service.config');
    }

    public function testServiceWiring()
    {
        $this->assertInstanceOf(Config::class, $this->configService);
    }

    public function testSelf()
    {
        $bundleConfig = $this->configService->getBundleConfig('YaroslavcheConfigUIBundle');
        $this->assertInstanceOf(BundleConfig::class, $bundleConfig);
        $this->assertSame('YaroslavcheConfigUIBundle', $bundleConfig->getName());
        $this->assertSame('Yaroslavche\ConfigUIBundle', $bundleConfig->getNamespace());
        $this->assertNotEmpty($bundleConfig->getPath());
        $this->assertInstanceOf(TreeBuilder::class, $bundleConfig->getTreeBuilder());
        $this->assertInstanceOf(NodeInterface::class, $bundleConfig->getTree());
        $this->assertSame(YaroslavcheConfigUIExtension::EXTENSION_ALIAS, $bundleConfig->getTree()->getName());
        $this->assertIsArray($bundleConfig->getDefinitions());
        $this->assertIsArray($bundleConfig->getDefaultConfiguration());
        $this->assertIsArray($bundleConfig->getCurrentConfiguration());
        $definitions = $bundleConfig->getDefinitions();
        $this->assertArrayHasKey('definition_fields', $definitions);
        $definitionFieldsDefinition = $definitions['definition_fields'];

        /** yaroslavche_config_ui.definitions_fields */
        $this->assertArrayHasKey('type', $definitionFieldsDefinition);
        $this->assertSame('array', $definitionFieldsDefinition['type']);
        $this->assertArrayHasKey('prototype', $definitionFieldsDefinition);
        $this->assertSame('boolean', $definitionFieldsDefinition['prototype']);
        $this->assertArrayHasKey('children', $definitionFieldsDefinition);
        /** yaroslavche_config_ui.definitions_fields.children */
        $definitionFieldsChildrenDefinition = $definitionFieldsDefinition['children'];
        $this->assertIsArray($definitionFieldsChildrenDefinition);
        /** yaroslavche_config_ui.definitions_fields.children.default */
        $this->assertArrayHasKey('', $definitionFieldsChildrenDefinition);
        $this->assertIsArray($definitionFieldsChildrenDefinition['']);
        $this->assertSame('boolean', $definitionFieldsChildrenDefinition['']['type']);

        $definitionsFieldsConfig = $this->kernel->getBundleConfig()['definition_fields'];
        foreach ($definitionFieldsDefinition as $field => $value) {
            if ($definitionsFieldsConfig[$field] === false) {
                $this->assertTrue(true, sprintf('Field %s must be excluded from result.', $field));
            }
        }
    }
}
