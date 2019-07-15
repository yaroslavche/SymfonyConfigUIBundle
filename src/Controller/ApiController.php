<?php

namespace Yaroslavche\ConfigUIBundle\Controller;

use ReflectionException;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Yaroslavche\ConfigUIBundle\Model\BundleConfig;
use Yaroslavche\ConfigUIBundle\Serializer\BundleConfigNormalizer;
use Yaroslavche\ConfigUIBundle\Service\Config;

class ApiController extends AbstractController
{
    /** @var Config $configService */
    private $configService;
    /** @var Serializer $serializer */
    private $serializer;

    /**
     * ApiController constructor.
     * @param Config $configService
     */
    public function __construct(Config $configService)
    {
        $this->configService = $configService;
        $this->serializer = new Serializer([new BundleConfigNormalizer()], [new JsonEncoder()]);
    }


    /**
     * @return JsonResponse
     */
    public function getBundles(): JsonResponse
    {
        try {
            $bundleConfigs = $this->configService->getBundleConfigs(true);
        } catch (ReflectionException $exception) {
            return $this->errorResponse($exception->getMessage());
        }
        $bundles = [];
        foreach ($bundleConfigs as $name => $bundleConfig) {
            $bundles[$name] = $this->getBundleConfigArray($bundleConfig);
        }
        return $this->successResponse(['bundles' => $bundles]);
    }

    /**
     * @param string $name
     * @return JsonResponse
     */
    public function getBundle(string $name): JsonResponse
    {
        try {
            $bundleConfig = $this->configService->getBundleConfig($name);
        } catch (ReflectionException $exception) {
            return $this->errorResponse($exception->getMessage());
        }
        if (!$bundleConfig instanceof BundleConfig) {
            return $this->errorResponse(sprintf('Bundle "%s" not found', $name));
        }
        $bundle = $this->getBundleConfigArray($bundleConfig);
        return $this->successResponse(['bundle' => $bundle]);
    }

    /**
     * ------------------------------------------------------------
     * Response format
     */

    /**
     * @param string $message
     * @param array[] $data
     * @return JsonResponse
     */
    private function errorResponse(string $message, array $data = []): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message
        ];
        if (!empty($data)) {
            $response['data'] = $data;
        }
        return new JsonResponse($response);
    }

    /**
     * @param array[] $data
     * @param string $message
     * @return JsonResponse
     */
    private function successResponse(array $data = [], string $message = ''): JsonResponse
    {
        $response = array_merge(
            [
                'status' => 'success'
            ],
            $data
        );
        if (!empty($message)) {
            $response['message'] = $message;
        }
        return new JsonResponse($response);
    }

    /**
     * @return stdClass|null
     */
    private function getRequestJSONPayload(): ?stdClass
    {
        $input = file_get_contents('php://input');
        $payload = json_decode($input !== false ? $input : '');
        if (!$payload instanceof stdClass) {
            return null;
        }
        return $payload;
    }

    /**
     * @param BundleConfig $bundleConfig
     * @return array[]
     */
    private function getBundleConfigArray(BundleConfig $bundleConfig): array
    {
        $bundleConfigJSON = $this->serializer->serialize($bundleConfig, 'json');
        $bundleConfigArray = json_decode($bundleConfigJSON, true);
        return $bundleConfigArray;
    }
}
