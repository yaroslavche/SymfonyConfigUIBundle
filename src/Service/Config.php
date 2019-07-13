<?php

namespace Yaroslavche\ConfigUIBundle\Service;

use LogicException;
use PhpParser\Error;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
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
            $namespace = $metadata['namespace'] ?? false;
            if (!$path || !$namespace) {
                throw new LogicException('Missed expected bundle metadata');
            }
            /** @var TreeBuilder $bundleConfigTreeBuilder */
            $bundleConfigTreeBuilder = $this->getBundleConfigTreeBuilder($namespace);
            dump($bundleConfigTreeBuilder->buildTree());
//            $this->parseBundleConfiguration($path);
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
        $getConfigTreeBuilderNodeVisitor = new GetConfigTreeBuilderNodeVisitor();
        $traverser->addVisitor($getConfigTreeBuilderNodeVisitor);
        $traverser->traverse($ast);
        $configArray = $getConfigTreeBuilderNodeVisitor->getConfigArray();
    }

    /**
     * @param string $namespace
     * @return TreeBuilder
     * @throws ReflectionException
     */
    private function getBundleConfigTreeBuilder(string $namespace): TreeBuilder
    {
        $configurationFQCN = sprintf('%s\DependencyInjection\Configuration', $namespace);
        $configuration = new ReflectionClass($configurationFQCN);

        /** @var ConfigurationInterface $configurationInstance */
        $configurationInstance = new $configurationFQCN(false);
        /** @var TreeBuilder $treeBuilder */
        $treeBuilder = $configuration->getMethod('getConfigTreeBuilder')->invoke($configurationInstance);

        return $treeBuilder;
    }

}

class GetConfigTreeBuilderNodeVisitor extends NodeVisitorAbstract
{
    /** @var bool $inScope */
    private $inScope = false;
    /** @var Node[] */
    private $nodes = [];
    /** @var Node[] $variables */
    private $variables = [];
    /** @var Node[] $identifiers */
    private $identifiers = [];
    /** @var Node|null $treeBuilderVariable */
    private $treeBuilderVariable = null;
    /** @var string $treeBuilderName */
    private $treeBuilderName = '';

    public function enterNode(Node $node)
    {
        if ($this->inScope) {
            $this->check($node);
        }
        if ($node instanceof ClassMethod) {
            if ($node->name instanceof Identifier && $node->name->name === 'getConfigTreeBuilder') {
                $this->inScope = true;
            } else {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof ClassMethod) {
            if ($node->name instanceof Identifier && $node->name->name === 'getConfigTreeBuilder') {
                $this->inScope = false;
            }
        }
    }

    public function getConfigArray()
    {
        dump($this);
    }

    private function check(Node $node)
    {
        $nodeKey = sprintf('%s_%s', $node->getType(), uniqid());
        $this->nodes[$nodeKey] = $node;
        $nodeClassName = get_class($node);
        switch ($nodeClassName) {
            case Assign::class:
                /** @var Assign $node */
                $nodeExpr = $node->expr;
                $nodeExprClassName = get_class($nodeExpr);
                switch ($nodeExprClassName) {
                    case New_::class:
                        if (in_array('TreeBuilder', $nodeExpr->class->parts)) {
                            $this->treeBuilderVariable = $node->var;
                            $treeBuilderName = $nodeExpr->args[0]->value;
                            if ($treeBuilderName instanceof String_) {
                                $this->treeBuilderName = $treeBuilderName->value;
                            } else {
                                $this->treeBuilderName = 'fixme';
                            }
                        }
                        break;
                    case MethodCall::class:
                        $methodName = $nodeExpr->name->name;
                        dump($methodName);
                        break;
                    case Array_::class:
                        break;
                    case Variable::class:
                        break;
                    default:
                        throw new \Exception($nodeExprClassName);
                }
                break;
            case Identifier::class:
                /** @var Identifier $node */
                $this->variables[$node->name] = $node;
                break;
            case Variable::class:
                /** @var Variable $node */
                $this->identifiers[$node->name] = $node;
                break;
            default:
                break;
        }
    }
}
