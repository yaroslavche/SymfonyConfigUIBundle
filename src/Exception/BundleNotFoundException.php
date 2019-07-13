<?php

namespace Yaroslavche\ConfigUIBundle\Exception;

use RuntimeException;
use Symfony\Component\Console\Exception\ExceptionInterface;

class BundleNotFoundException extends RuntimeException implements ExceptionInterface
{
}
