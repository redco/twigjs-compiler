<?php

namespace RedCode\TwigJs;

use TwigJs\DefaultFunctionNamingStrategy;
use TwigJs\FunctionNamingStrategyInterface;

class CamelCaseFunctionNameStrategy extends DefaultFunctionNamingStrategy implements FunctionNamingStrategyInterface
{
    private function dashesToCamelCase($string, $capitalizeFirstCharacter = false)
    {
        $str = str_replace(' ', '', ucwords(str_replace(['-', '.'], ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }

    public function getFunctionName(\Twig_Node_Module $module)
    {
        return $this->dashesToCamelCase(parent::getFunctionName($module));
    }
}
