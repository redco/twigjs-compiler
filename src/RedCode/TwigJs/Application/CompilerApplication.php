<?php

namespace RedCode\TwigJs\Application;

use RedCode\TwigJs\Application\Command\CompileCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;

class CompilerApplication extends Application
{
    const VERSION = '0.1.0';

    public function __construct()
    {
        parent::__construct('TwigJs Compiler', self::VERSION);
    }

    /**
     * {@inheritdoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        return CompileCommand::NAME;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new CompileCommand();

        return $defaultCommands;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}
