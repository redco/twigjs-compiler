<?php

namespace RedCode\TwigJs\Compiler\Expression;

use TwigJs\JsCompiler;
use TwigJs\Compiler\Expression\GetAttrCompiler as BaseGetAttrCompiler;

/**
 * Class GetAttrCompiler replaces base macros call compilation with array compiler approach.
 *
 * @see https://github.com/schmittjoh/twig.js/pull/67
 */
class GetAttrCompiler extends BaseGetAttrCompiler
{
    /**
     * {@inheritdoc}
     */
    public function compile(JsCompiler $compiler, \Twig_NodeInterface $node)
    {
        if (!$node instanceof \Twig_Node_Expression_GetAttr) {
            throw new \RuntimeException(sprintf('$node must be an instanceof of \Expression_GetAttr, but got "%s".', get_class($node)));
        }

        $compiler->raw('twig.attr(');

        if ($node->getAttribute('is_defined_test') && $compiler->getEnvironment()->isStrictVariables()) {
            $compiler->subcompile(new \Twig_Node_Expression_Filter(
                $node->getNode('node'),
                new \Twig_Node_Expression_Constant('default', $node->getLine()),
                new \Twig_Node(),
                $node->getLine()
            ));
        } else {
            $compiler->subcompile($node->getNode('node'));
        }

        $compiler
            ->raw(', ')
            ->subcompile($node->getNode('attribute'))
        ;

        $defaultArguments = 0 === count($node->getNode('arguments'));
        $defaultAccess = \Twig_TemplateInterface::ANY_CALL === $node->getAttribute('type');
        $defaultTest = false == $node->getAttribute('is_defined_test');

        if (!$defaultArguments) {
            $compiler->raw(', ')->subcompile($node->getNode('arguments'));
        } elseif (!$defaultAccess || !$defaultTest) {
            $compiler->raw(', undefined');
        }

        if (!$defaultAccess) {
            $compiler->raw(', ')->repr($node->getAttribute('type'));
        } elseif (!$defaultTest) {
            $compiler->raw(', undefined');
        }

        if (!$defaultTest) {
            $compiler->raw(', '.($node->getAttribute('is_defined_test') ?
                    'true' : 'false'));
        }

        $compiler->raw(')');
    }
}
