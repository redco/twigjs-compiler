<?php

namespace RedCode\TwigJs;

use TwigJs\JsCompiler as BaseJsCompiler;
use RedCode\TwigJs\Compiler\Expression\GetAttrCompiler;
use RedCode\TwigJs\Compiler\Expression\NameCompiler;
use RedCode\TwigJs\Compiler\Test\SameAsCompiler;
use RedCode\TwigJs\Compiler\MacroCompiler;
use RedCode\TwigJs\Compiler\ForCompiler;
use RedCode\TwigJs\Compiler\RequireModuleCompiler;

/**
 * JsCompiler replaces base GetAttrCompiler with the custom one.
 */
class JsCompiler extends BaseJsCompiler
{
    public function __construct(\Twig_Environment $env)
    {
        parent::__construct($env);

        $this->addTypeCompiler(new GetAttrCompiler());
        $this->addTypeCompiler(new NameCompiler());
        $this->addTypeCompiler(new RequireModuleCompiler());
        $this->addTestCompiler(new SameAsCompiler());
        $this->addTypeCompiler(new MacroCompiler());
        $this->addTypeCompiler(new ForCompiler());
    }
}
