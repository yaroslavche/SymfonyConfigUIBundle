<?php

namespace Yaroslavche\ConfigUIBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Yaroslavche\ConfigUIBundle\Controller\ApiController;
use Yaroslavche\ConfigUIBundle\Service\Config;

class ApiControllerTest extends WebTestCase
{
    /** @var ApiController $apiController */
    private $apiController;
    private $containerMock;
    private $configServiceMock;

    protected function setUp(): void
    {
        $this->containerMock = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $this->configServiceMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->apiController = new ApiController($this->configServiceMock);
        $this->apiController->setContainer($this->containerMock);
    }

    public function testGetBundles()
    {
        $response = $this->apiController->getBundles();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $responseJSON = $response->getContent();
        $responseArray = json_decode($responseJSON, true);
        $this->assertArrayHasKey('status', $responseArray);
        $this->assertSame('success', $responseArray['status']);
        $this->assertArrayHasKey('bundles', $responseArray);
        $bundles = $responseArray['bundles'];
        $this->assertIsArray($bundles);
        /** @todo fix this */
//        $this->assertArrayHasKey('name', $bundle);
//        $this->assertSame('YaroslavcheConfigUIBundle', $bundle['name']);
    }

    public function testGetBundle()
    {
        $response = $this->apiController->getBundle('YaroslavcheConfigUIBundle');
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $responseJSON = $response->getContent();
        $responseArray = json_decode($responseJSON, true);
        $this->assertArrayHasKey('status', $responseArray);
        $this->assertSame('success', $responseArray['status']);
        $this->assertArrayHasKey('bundle', $responseArray);
        $bundle = $responseArray['bundle'];
        $this->assertIsArray($bundle);
        /** @todo fix this */
//        $this->assertArrayHasKey('name', $bundle);
//        $this->assertSame('YaroslavcheConfigUIBundle', $bundle['name']);
    }

//    public function testGetBundles()
//    {
//        $client = static::createClient();
//        $client->request('GET', '/bundles');
//        $this->assertEquals(200, $client->getResponse()->getStatusCode());
//    }
}
