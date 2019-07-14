<?php

namespace Yaroslavche\ConfigUIBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Yaroslavche\ConfigUIBundle\Service\Config;

class DashboardController extends AbstractController
{
    /** @var Config $configService */
    private $configService;

    /**
     * DashboardController constructor.
     * @param Config $configService
     */
    public function __construct(Config $configService)
    {
        $this->configService = $configService;
    }


    public function __invoke()
    {
        $bundles = [];
        foreach ($this->configService->getBundles() as $name => $bundle) {
            $bundles[$name] = $this->configService->getBundleConfigDefinitions($name);
        }
        return $this->render('@YaroslavcheConfigUI/dashboard.html.twig', [
            'bundles' => json_encode($bundles, JSON_PRETTY_PRINT)
        ]);
    }
}
