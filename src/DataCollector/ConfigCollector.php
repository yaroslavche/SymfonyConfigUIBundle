<?php

namespace Yaroslavche\ConfigUIBundle\DataCollector;

use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Yaroslavche\ConfigUIBundle\Service\Config;

class ConfigCollector extends DataCollector
{
    const DATA_COLLECTOR_NAME = 'yaroslavche_config_ui.data_collector.config';
    /**
     * @var Config
     */
    private $config;

    /**
     * ConfigCollector constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }


    /**
     * Collects data for the given Request and Response.
     * @param Request $request
     * @param Response $response
     * @param Exception|null $exception
     */
    public function collect(Request $request, Response $response, Exception $exception = null)
    {
        $this->data = [
            'bundles' => []
        ];
    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     */
    public function getName()
    {
        return static::DATA_COLLECTOR_NAME;
    }

    public function reset()
    {
        $this->data = [];
    }

    /**
     * @return string[]
     */
    public function getBundles(): array
    {
        return $this->data['bundles'];
    }
}
