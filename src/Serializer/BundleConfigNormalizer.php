<?php

namespace Yaroslavche\ConfigUIBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Yaroslavche\ConfigUIBundle\Model\BundleConfig;

class BundleConfigNormalizer implements NormalizerInterface
{
    /** @var ObjectNormalizer $normalizer */
    private $normalizer;

    public function __construct()
    {
        $this->normalizer = new ObjectNormalizer();
    }

    /**
     * @param BundleConfig $bundleConfig
     * @param string|null $format
     * @param string[] $context
     * @return string[]
     */
    public function normalize($bundleConfig, $format = null, array $context = [])
    {
        return [
            'name' => $bundleConfig->getName(),
            'namespace' => $bundleConfig->getNamespace(),
            'path' => $bundleConfig->getPath(),
            'definitions' => $bundleConfig->getDefinitions(),
            'defaultConfiguration' => $bundleConfig->getDefaultConfiguration(),
            'currentConfiguration' => $bundleConfig->getCurrentConfiguration(),
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof BundleConfig;
    }
}
