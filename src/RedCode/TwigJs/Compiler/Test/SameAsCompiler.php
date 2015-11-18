<?php

namespace RedCode\TwigJs\Compiler\Test;

use TwigJs\JsCompiler;
use TwigJs\Compiler\Test\SameAsCompiler as BaseSameAsCompiler;

/**
 * Class SameAsCompiler fix some problems with sameas test.
 *
 * @see https://github.com/schmittjoh/twig.js/pull/67
 */
class SameAsCompiler extends BaseSameAsCompiler
{
    /**
     * {@inheritdoc}
     */
    public function compile(JsCompiler $compiler, \Twig_Node_Expression_Test $node)
    {
        $compiler
            ->raw('((')
            ->subcompile($node->getNode('node'))
            ->raw(') === ')
            ->subcompile($node->getNode('arguments')->getNode(0))
            ->raw(')')
        ;
    }
}
