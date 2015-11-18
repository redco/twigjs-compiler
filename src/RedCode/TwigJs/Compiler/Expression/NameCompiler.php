<?php

namespace RedCode\TwigJs\Compiler\Expression;

use TwigJs\JsCompiler;
use TwigJs\Compiler\Expression\NameCompiler as BaseNameCompiler;

/**
 * Class NameCompiler adds parenthesis to base macros call.
 */
class NameCompiler extends BaseNameCompiler
{
    /**
     * {@inheritdoc}
     */
    public function compile(JsCompiler $compiler, \Twig_NodeInterface $node)
    {
        if (!$node instanceof \Twig_Node_Expression_Name) {
            throw new \RuntimeException(sprintf('$node must be an instanceof of \Expression_Name, but got "%s".', get_class($node)));
        }

        $name = $node->getAttribute('name');

        if ($node->getAttribute('is_defined_test')) {
            if ($node->isSpecial()) {
                $compiler->repr(true);
            } else {
                $compiler->raw('(')->repr($name)->raw(' in context)');
            }
        } elseif ($node->isSpecial()) {
            static $specialVars = [
                '_self' => 'this',
                '_context' => 'context',
                '_charset' => 'this.env_.getCharset()',
            ];

            if (!isset($specialVars[$name])) {
                throw new \RuntimeException(sprintf('The special var "%s" is not supported by the NameCompiler.', $name));
            }

            $compiler->raw($specialVars[$name]);
        } else {
            if (isset($compiler->localVarMap[$name])) {
                $compiler->raw($compiler->localVarMap[$name]);

                return;
            }

            // FIXME: Add strict behavior?
            //        see Template::getContext()
            $compiler
                ->raw('(')
                ->string($name)
                ->raw(' in context ? context[')
                ->string($name)
                ->raw('] : null')
                ->raw(')')
            ;
        }
    }
}
