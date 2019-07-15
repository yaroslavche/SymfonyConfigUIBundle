<?php

namespace Yaroslavche\ConfigUIBundle\DataCollector;

use Exception;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Yaroslavche\ConfigUIBundle\Service\Config;

class ConfigUIDataCollector extends DataCollector
{
    const DATA_COLLECTOR_NAME = 'yaroslavche_config_ui.data_collector.config';

    /** @var Config $config */
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
        $bundles = [];
        try {
            $bundleConfigs = $this->config->getBundleConfigs(true);
            foreach ($bundleConfigs as $name => $bundleConfig) {
                $bundles[$name] = [
                    'name' => $bundleConfig->getName(),
                    'namespace' => $bundleConfig->getNamespace(),
                    'path' => $bundleConfig->getPath(),
                    /** Serialization of 'Closure' is not allowed */
//                    'definitions' => $bundleConfig->getDefinitions(),
                    'defaultConfiguration' => $bundleConfig->getDefaultConfiguration(),
                    'currentConfiguration' => $bundleConfig->getCurrentConfiguration(),
                ];
            }
        } catch (ReflectionException $exception) {
            // @ignoreException
        }

        $this->data = [
            'bundles' => $bundles,
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
