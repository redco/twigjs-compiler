<?php

namespace RedCode\TwigJs\Application\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;

class CompileCommand extends Command
{
    const NAME = './bin/twigjs-compile';
    const CONFIG_FILE_NAME = '.twigjs.yml';

    /**
     * @var array
     */
    private $config = [
        'filters' => [],
        'functions' => [],
    ];

    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription('Compiles Twig templates to TwigJs with requireJS support')
            ->addArgument('src', InputArgument::REQUIRED, 'Path to twig views')
            ->addArgument('dst', InputArgument::REQUIRED, 'Path to rendered twigJs views')
            ->addArgument('compilingFolders', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'Compiling paths');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->loadConfig();

        $sourceDir = $input->getArgument('src');
        $this->assertSourceDirValid($sourceDir);

        $destinationDir = $input->getArgument('dst');
        $this->assertDestinationDirValid($destinationDir);

        $compilingFolders = $input->getArgument('compilingFolders');

        (new TwigJsCompiler($sourceDir, $destinationDir, $compilingFolders, $output))
            ->setRegisteredFunctions($this->config['functions'])
            ->setRegisteredFilters($this->config['filters'])
            ->compile();
    }

    /**
     * @param string $sourceDir
     */
    private function assertSourceDirValid($sourceDir)
    {
        if (!file_exists($sourceDir)) {
            throw new \UnexpectedValueException(
                sprintf('Source dir "%s" is not exists', $sourceDir)
            );
        }

        if (!is_writable($sourceDir)) {
            throw new \UnexpectedValueException(
                sprintf('Source dir "%s" is not writable', $sourceDir)
            );
        }
    }

    /**
     * @param string $destinationDir
     */
    private function assertDestinationDirValid($destinationDir)
    {
        if (!file_exists($destinationDir)) {
            if (!mkdir($destinationDir, 0777, true)) {
                throw new \UnexpectedValueException(
                    sprintf('Destination dir "%s" is not exists and could not be created', $destinationDir)
                );
            }
        }

        if (!is_writable($destinationDir)) {
            throw new \UnexpectedValueException(
                sprintf('Destination dir "%s" is not writable', $destinationDir)
            );
        }
    }

    private function loadConfig()
    {
        if (file_exists(self::CONFIG_FILE_NAME)) {
            $yaml = new Parser();
            $config = $yaml->parse(file_get_contents(self::CONFIG_FILE_NAME));
            if (!empty($config['functions']) && is_array($config['functions'])) {
                $this->config['functions'] = $config['functions'];
            }
            if (!empty($config['filters']) && is_array($config['filters'])) {
                $this->config['filters'] = $config['filters'];
            }
        }
    }
}
