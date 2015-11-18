<?php

namespace RedCode\TwigJs\Compiler;

use TwigJs\JsCompiler;
use TwigJs\Compiler\MacroCompiler as BaseMacroCompiler;

class MacroCompiler extends BaseMacroCompiler
{
    public function compile(JsCompiler $compiler, \Twig_NodeInterface $node)
    {
        if (!$node instanceof \Twig_Node_Macro) {
            throw new \RuntimeException(
                sprintf(
                    '$node must be an instanceof of \Twig_Node_Macro, but got "%s".',
                    get_class($node)
                )
            );
        }

        $compiler->enterScope();

        $arguments = [];
        $configArguments = [];
        foreach ($node->getNode('arguments') as $name => $argument) {
            if ($argument->hasAttribute('name')) {
                $name = $argument->getAttribute('name');
            }

            $arguments[] = 'opt_'.$name;
            $configArguments[] = $name;
            $compiler->setVar($name, 'opt_'.$name);
        }

        $compiler
            ->addDebugInfo($node)
            ->write("/**\n", ' * Macro "'.$node->getAttribute('name')."\"\n", " *\n")
        ;

        foreach ($arguments as $arg => $var) {
            $compiler->write(" * @param {*} $var\n");
        }

        $compiler
            ->write(" * @return {string}\n")
            ->write(" */\n")
            ->raw($compiler->templateFunctionName)
            ->raw('.prototype.get')
            ->raw($node->getAttribute('name'))
            ->raw(' = function('.implode(', ', $arguments).") {\n")
            ->indent()
            ->write("var context = twig.extend({}, this.env_.getGlobals());\n\n")
            ->write("var args = arguments;\n\n")
            ->write("['".implode("', '", $configArguments)."'].forEach(function (argumentName, i) {context[argumentName] = args[i]});\n\n")
            ->write("var sb = new twig.StringBuffer;\n")
            ->subcompile($node->getNode('body'))
            ->raw("\n")
            ->write("return new twig.Markup(sb.toString());\n")
            ->outdent()
            ->write("};\n\n")
            ->leaveScope()
        ;
    }
}
