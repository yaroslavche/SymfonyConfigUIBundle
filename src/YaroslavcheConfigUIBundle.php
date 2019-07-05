<?php

namespace Yaroslavche\ConfigUIBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class YaroslavcheConfigUIBundle extends Bundle
{
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new DependencyInjection\YaroslavcheConfigUIExtension();
        }

        return $this->extension;
    }

}
