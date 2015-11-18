<?php

namespace RedCode\TwigJs;

class Node extends \Twig_Node
{
    public function __construct($name, \Twig_Node_Expression $value, $line, $tag = null)
    {
        parent::__construct(['value' => $value], ['name' => $name], $line, $tag);
    }

    public function compile(\Twig_Compiler $compiler)
    {
    }
}
