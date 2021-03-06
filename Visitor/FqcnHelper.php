<?php

/*
 * Mondrian
 */

namespace Trismegiste\Mondrian\Visitor;

use PHPParser_NodeVisitorAbstract;
use PHPParser_Node;
use PHPParser_Error;

/**
 * FqcnHelper is an helper for resolving FQCN for Class/Interface/Param
 */
class FqcnHelper extends PHPParser_NodeVisitorAbstract
{

    /**
     * @var null|PHPParser_Node_Name Current namespace
     */
    protected $namespace;

    /**
     * @var array Currently defined namespace and class aliases
     */
    protected $aliases;

    /**
     * current file
     */
    protected $currentPhpFile = false;

    public function beforeTraverse(array $nodes)
    {
        // if the visitor is used without PhpFile nodes
        $this->namespace = null;
        $this->aliases = array();
    }

    public function enterNode(PHPParser_Node $node)
    {

        switch ($node->getType()) {

            case 'PhpFile' :
                $this->currentPhpFile = $node;
                // resetting the tracking of namespace and alias if we enter in a new file
                $this->namespace = null;
                $this->aliases = array();
                break;

            case 'Stmt_Namespace' :
                $this->namespace = $node->name;
                $this->aliases = array();
                break;

            case 'Stmt_UseUse' :
                if (isset($this->aliases[$node->alias])) {
                    throw new PHPParser_Error(
                    sprintf(
                            'Cannot use "%s" as "%s" because the name is already in use', $node->name, $node->alias
                    ), $node->getLine()
                    );
                }
                $this->aliases[$node->alias] = $node->name;
                break;
        }
    }

    /**
     * resolve the Name with current namespace and alias
     *
     * @param \PHPParser_Node_Name $src
     * @return \PHPParser_Node_Name|\PHPParser_Node_Name_FullyQualified
     */
    protected function resolveClassName(\PHPParser_Node_Name $src)
    {
        $name = clone $src;
        // don't resolve special class names
        if (in_array((string) $name, array('self', 'parent', 'static'))) {
            return $name;
        }

        // fully qualified names are already resolved
        if ($name->isFullyQualified()) {
            return $name;
        }

        // resolve aliases (for non-relative names)
        if (!$name->isRelative() && isset($this->aliases[$name->getFirst()])) {
            $name->setFirst($this->aliases[$name->getFirst()]);
            // if no alias exists prepend current namespace
        } elseif (null !== $this->namespace) {
            $name->prepend($this->namespace);
        }

        return new \PHPParser_Node_Name_FullyQualified($name->parts, $name->getAttributes());
    }

    /**
     * Helper : get the FQCN of the given $node->name
     *
     * @param PHPParser_Node $node
     * @return string
     */
    protected function getNamespacedName(PHPParser_Node $node)
    {
        if (null !== $this->namespace) {
            $namespacedName = clone $this->namespace;
            $namespacedName->append($node->name);
        } else {
            $namespacedName = $node->name;
        }

        return (string) $namespacedName;
    }

}
