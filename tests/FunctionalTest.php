<?php

namespace Yaroslavche\ConfigUIBundle\Tests;

use PHPUnit\Framework\TestCase;
use Yaroslavche\ConfigUIBundle\Service\Config;

class FunctionalTest extends TestCase
{
    public function testServiceWiring()
    {
        $kernel = new Kernel();
        $kernel->boot();
        $container = $kernel->getContainer();
        $configService = $container->get('yaroslavche_config_ui.service.config');
        $this->assertInstanceOf(Config::class, $configService);
    }
}
