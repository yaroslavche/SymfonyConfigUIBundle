<?php

namespace Yaroslavche\ConfigUIBundle\Service;

use LogicException;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Symfony\Component\Filesystem\Filesystem;

class Config
{

    /** @var Filesystem $filesystem */
    private $filesystem;

    public function __construct(
        array $kernelBundlesMetadata
    )
    {
        $this->filesystem = new Filesystem();
        foreach ($kernelBundlesMetadata as $name => $metadata) {
            /** @var string $path */
            $path = $metadata['path'] ?? false;
            if (!$path) {
                throw new LogicException('Missed expected bundle path');
            }
            $this->parseBundleConfiguration($path);
        }
    }

    private function parseBundleConfiguration($path)
    {
        $configurationClassPath = sprintf('%s%sDependencyInjection%sConfiguration.php', $path, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
        if (!$this->filesystem->exists($configurationClassPath)) {
            throw new LogicException('Missed expected bundle configuration class');
        }
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $configurationClass = file_get_contents($configurationClassPath);
            $ast = $parser->parse($configurationClass);
        } catch (Error $error) {
            throw new LogicException($error->getMessage());
        }
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new GetConfigTreeBuilderNodeVisitor());
        $traverser->traverse($ast);
    }

}

class GetConfigTreeBuilderNodeVisitor extends NodeVisitorAbstract
{
    /** @var bool $inScope */
    private $inScope = false;
    /** @var array $nodes */
    private $nodes = [];

    public function enterNode(Node $node)
    {
        if ($this->inScope) $this->nodes[] = $node;
        if ($node instanceof ClassMethod) {
            if ($node->name instanceof Identifier && $node->name->name === 'getConfigTreeBuilder') {
                $this->inScope = true;
            }
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassMethod) {
            if ($node->name instanceof Identifier && $node->name->name === 'getConfigTreeBuilder') {
                $this->inScope = false;
                dump($this->nodes);
            }
        }
    }
}
