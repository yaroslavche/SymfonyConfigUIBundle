<?php

namespace Yaroslavche\ConfigUIBundle\Service;

class Config
{

    private $kernelProjectDir;

    public function __construct(
        string $kernelProjectDir
    )
    {
        $this->kernelProjectDir = $kernelProjectDir;
    }
}
