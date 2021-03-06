<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Transform;

use Trismegiste\Mondrian\Graph\Graph;
use Trismegiste\Mondrian\Visitor;
use Trismegiste\Mondrian\Builder\Compiler\AbstractTraverser;
use Trismegiste\Mondrian\Transform\Logger\LoggerInterface;

/**
 * Design Pattern: Builder
 *
 * GraphBuilder is a builder for a compiler
 *
 */
class GraphBuilder extends AbstractTraverser
{

    protected $graphResult;
    protected $config;
    protected $reflection;
    protected $vertexContext;
    protected $logger;

    /**
     * Injecting the external in/out parameters
     * 
     * @param array $cfg
     * @param \Trismegiste\Mondrian\Graph\Graph $g
     * @param \Trismegiste\Mondrian\Transform\Logger\LoggerInterface $log
     */
    public function __construct(array $cfg, Graph $g, LoggerInterface $log)
    {
        $this->config = $cfg;
        $this->graphResult = $g;
        $this->logger = $log;
    }

    /**
     * Build the context(s) for passes
     */
    public function buildContext()
    {
        $this->reflection = new ReflectionContext();
        $this->vertexContext = new GraphContext($this->config, $this->logger);
    }

    /**
     * Build a list of viitor for each pass
     * 
     * @return FqcnCollector[] list of passes
     */
    public function buildCollectors()
    {
        return array(
            new Visitor\SymbolMap($this->reflection),
            new Visitor\VertexCollector($this->reflection, $this->vertexContext, $this->graphResult),
            new Visitor\EdgeCollector($this->reflection, $this->vertexContext, $this->graphResult)
        );
    }

}
