<?php

namespace RedCode\TwigJs\Compiler;

use TwigJs\JsCompiler;
use TwigJs\TypeCompilerInterface;

class ForCompiler implements TypeCompilerInterface
{
    private $count = 0;

    public function getType()
    {
        return 'Twig_Node_For';
    }

    public function compile(JsCompiler $compiler, \Twig_NodeInterface $node)
    {
        if (!$node instanceof \Twig_Node_For) {
            throw new \RuntimeException(
                sprintf(
                    '$node must be an instanceof of \For, but got "%s".',
                    get_class($node)
                )
            );
        }

        $count = $this->count++;
        $suffix = $count > 0 ? $count : '';
        $seqName = 'seq'.$suffix;
        $keyName = 'k'.$suffix;
        $valueName = 'v'.$suffix;
        $iteratedName = 'iterated'.$suffix;
        $loopName = 'loop'.$suffix;

        if ($count > 0) {
            $compiler->enterScope();
        }

        $compiler
            ->setVar('_seq', $seqName)
            ->setVar('_iterated', $iteratedName)
            ->setVar('loop', $loopName)
            ->addDebugInfo($node)
        ;

        // keep parent reference as some might rely on this
        if (0 === $count) {
            $compiler->write("context['_parent'] = context;\n");
        }

        $compiler
            ->write("var $seqName = ")
//             ->write("\$context['_seq'] = twig_ensure_traversable(")
            ->subcompile($node->getNode('seq'))
//             ->raw(");\n")
            ->raw(";\n")
        ;

        if (null !== $node->getNode('else')) {
            $compiler->write("var $iteratedName = false;\n");
        }

        if ($node->getAttribute('with_loop')) {
            $compiler
                ->write("var $loopName = {\n")
                ->indent()
            ;

            if ($count > 0) {
                $parentSuffix = ($count - 1 > 0) ? $count - 1 : '';

                $compiler
                    ->write("'parent': {loop: loop$parentSuffix},\n")
                ;
            }

            $compiler
                ->write("'index0': 0,\n")
                ->write("'index': 1,\n")
                ->write("'first': true\n")
                ->outdent()
                ->write("};\n")
            ;

            if (false === $node->getAttribute('ifexpr')) {
                $compiler
                    ->write("if (twig.countable($seqName)) {\n")
                    ->indent()
                    ->write("var length = twig.count($seqName);\n")
                    ->write("{$loopName}['revindex0'] = length - 1;\n")
                    ->write("{$loopName}['revindex'] = length;\n")
                    ->write("{$loopName}['length'] = length;\n")
                    ->write("{$loopName}['last'] = 1 === length;\n")
                    ->outdent()
                    ->write("}\n")
                ;
            }
        }

        $ref = new \ReflectionProperty($node, 'loop');
        $ref->setAccessible(true);
        $loop = $ref->getValue($node);
        $loop->setAttribute('else', null !== $node->getNode('else'));
        $loop->setAttribute('with_loop', $node->getAttribute('with_loop'));
        $loop->setAttribute('ifexpr', $node->getAttribute('ifexpr'));

        $compiler
            ->write("twig.forEach($seqName, function($valueName, $keyName) {\n")
            ->indent()
            ->write('')
            ->subcompile($node->getNode('key_target'))
            ->raw(" = $keyName;\n")
            ->write('')
            ->subcompile($node->getNode('value_target'))
            ->raw(" = $valueName;\n")
            ->subcompile($node->getNode('body'))
            ->outdent()
            ->write("}, this);\n")
        ;

        if (null !== $node->getNode('else')) {
            $compiler
                ->write("if (!$iteratedName) {\n")
                ->indent()
                ->subcompile($node->getNode('else'))
                ->outdent()
                ->write("}\n")
            ;
        }

        if ($count > 0) {
            $compiler->leaveScope();
        }
        $this->count = $count;
    }
}
