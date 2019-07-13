<?php

namespace Yaroslavche\ConfigUIBundle\Tests;

use PHPUnit\Framework\TestCase;
use Yaroslavche\ConfigUIBundle\DependencyInjection\YaroslavcheConfigUIExtension;
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
        $config = $this->configService->getBundleConfigDefinitions('YaroslavcheConfigUIBundle');
        $this->assertArrayHasKey(YaroslavcheConfigUIExtension::EXTENSION_ALIAS, $config);
        $collectedDefinitionFields = $config[YaroslavcheConfigUIExtension::EXTENSION_ALIAS]['definition_fields'];
        $definitionsFieldsConfig = $this->kernel->getBundleConfig()['definition_fields'];
        foreach ($definitionsFieldsConfig as $field => $show) {
            if ($show === true) {
                $this->assertArrayHasKey($field, $collectedDefinitionFields);
            }
        }
    }
}
