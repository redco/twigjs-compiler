<?php

namespace RedCode\TwigJs\Compiler;

use TwigJs\JsCompiler;
use TwigJs\Compiler\ModuleCompiler;
use TwigJs\TypeCompilerInterface;

/**
 * Class RequireModuleCompiler.
 */
class RequireModuleCompiler extends ModuleCompiler implements TypeCompilerInterface
{
    /**
     * Compiles class header: adds beginning of AMD module definition.
     *
     * @param JsCompiler          $compiler
     * @param \Twig_NodeInterface $node
     */
    protected function compileClassHeader(JsCompiler $compiler, \Twig_NodeInterface $node)
    {
        $functionName = $this->functionName = $compiler->templateFunctionName = $compiler->getFunctionName($node);
        $dependencies = $this->getDependencies($compiler, $node);

        $compiler
            ->write("define('".$functionName."Template', ['twig'");
        if (count($dependencies['files'])) {
            $dependenciesInDefine = implode("', '", $dependencies['files']);
            $dependenciesInAttributes = implode(', ', $dependencies['functions']);
            $compiler
                ->write(", '$dependenciesInDefine'], function (Twig, $dependenciesInAttributes) {");
        } else {
            $compiler
                ->write('], function (Twig) {');
        }

        $compiler
            ->write("\n")
            ->write(
                "/**\n",
                " * @constructor\n",
                " * @param {twig.Environment} env\n",
                " * @extends {twig.Template}\n",
                " */\n"
            )
            ->write("var $functionName = function(env) {\n")
            ->indent()
            ->write("twig.Template.call(this, env);\n")
        ;

        if (count($node->getNode('blocks')) || count($node->getNode('traits'))) {
            $this->compileConstructor($compiler, $node);
        }

        $compiler
            ->outdent()
            ->write("};\n")
            ->write("twig.inherits($functionName, twig.Template);\n\n")
        ;
    }

    /**
     * Compiles class footer: adds end of AMD module definition.
     *
     * @param JsCompiler          $compiler
     * @param \Twig_NodeInterface $node
     */
    protected function compileClassFooter(JsCompiler $compiler, \Twig_NodeInterface $node)
    {
        $compiler
            ->write("return $this->functionName;")
            ->write("});\n");
    }

    /**
     * Parses module dependencies from Twig AST.
     *
     * @param JsCompiler          $compiler
     * @param \Twig_NodeInterface $node
     *
     * @return array of dependencies
     */
    private function getDependencies(JsCompiler $compiler, \Twig_NodeInterface $node)
    {
        $dependencies = ['files' => [], 'functions' => []];
        $stack = iterator_to_array($node->getIterator());
        while (count($stack)) {
            $subNode = array_shift($stack);
            if (!$subNode instanceof \Twig_Node) {
                continue;
            }
            if ($subNode instanceof \Twig_Node_Include || $subNode instanceof \Twig_Node_Import) {
                $expr = $subNode->getNode('expr');
                $env = $compiler->getEnvironment();
                $path = $expr->getAttribute('value');
                $source = $env->getLoader()->getSource($path);
                $module = $env->parse($env->tokenize($source, $path));
                $name = $compiler->getFunctionName($module);
                array_push($dependencies['files'], $name.'Template');
                array_push($dependencies['functions'], $name);
            } elseif ($subNode->count()) {
                $stack = array_merge(iterator_to_array($subNode->getIterator()), $stack);
            }
        }

        return $dependencies;
    }
}
