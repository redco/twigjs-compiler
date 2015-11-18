<?php

namespace RedCode\TwigJs\Application\Command;

use Twig_Environment;
use Twig_Extension_Core;
use Twig_Loader_Filesystem;
use Twig_SimpleFunction;
use Twig_SimpleFilter;
use TwigJs\CompileRequest;
use TwigJs\CompileRequestHandler;
use RedCode\TwigJs\CamelCaseFunctionNameStrategy;
use Symfony\Component\Console\Output\OutputInterface;
use RedCode\TwigJs\JsCompiler;
use TwigJs\Twig\TwigJsExtension;

class TwigJsCompiler
{
    /**
     * @var string
     */
    private $sourceDir;

    /**
     * @var array
     */
    private $compilingFolders;

    /**
     * @var string
     */
    private $destinationDir;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $registeredFunctions = [];

    /**
     * @var array
     */
    private $registeredFilters = [];

    /**
     * @param string          $sourceDir
     * @param string          $destinationDir
     * @param array           $compilingFolders
     * @param OutputInterface $output
     */
    public function __construct($sourceDir, $destinationDir, array $compilingFolders, OutputInterface $output)
    {
        $this->output = $output;
        $this->sourceDir = $sourceDir;
        $this->destinationDir = $destinationDir;
        $this->compilingFolders = $compilingFolders;
    }

    /**
     * Compiles templates.
     */
    public function compile()
    {
        $env = $this->getTwigEnvironment($this->sourceDir);
        $compiler = $this->getCompiler($env);
        $handler = new CompileRequestHandler($env, $compiler);

        $time = microtime(true);
        $compiledFilesCounter = $this->compileTwigFiles(
            $handler,
            $this->sourceDir,
            $this->destinationDir,
            $this->compilingFolders
        );

        $this->output->writeln(sprintf('Compiled: %s files', $compiledFilesCounter));
        $this->output->writeln(sprintf('Time: %s sec', microtime(true) - $time));
    }

    /**
     * @param array $functions
     *
     * @return self
     */
    public function setRegisteredFunctions(array $functions)
    {
        $this->registeredFunctions = $functions;

        return $this;
    }

    /**
     * @param array $filters
     *
     * @return self
     */
    public function setRegisteredFilters(array $filters)
    {
        $this->registeredFilters = $filters;

        return $this;
    }

    /**
     * @param CompileRequestHandler $handler
     * @param array                 $sourceDir
     * @param string                $destinationDir
     * @param array                 $compilingDirs
     *
     * @return int
     */
    private function compileTwigFiles(
        CompileRequestHandler $handler,
        $sourceDir,
        $destinationDir,
        array $compilingDirs = []
    ) {
        if (count($compilingDirs) === 0) {
            return $this->compileTwigDir($handler, $sourceDir, $destinationDir);
        }

        $compiledFilesCounter = 0;
        foreach ($compilingDirs as $dir) {
            $compiledFilesCounter += $this->compileTwigDir($handler, $sourceDir.$dir, $destinationDir.$dir);
        }

        return $compiledFilesCounter;
    }

    /**
     * @param CompileRequestHandler $handler
     * @param string                $twigDir
     * @param string                $twigJsDir
     * @param array                 $excludes
     *
     * @return int
     *
     * @throws \Exception
     */
    private function compileTwigDir(CompileRequestHandler $handler, $twigDir, $twigJsDir, $excludes = [])
    {
        $counter = 0;
        !file_exists($twigJsDir) && mkdir($twigJsDir, 0777, true);
        foreach (new \RecursiveDirectoryIterator($twigDir, \RecursiveDirectoryIterator::SKIP_DOTS) as $file) {
            $twigJsFilename = $twigJsDir.'/'.basename($file);
            foreach ($excludes as $exclude) {
                if (strpos($twigDir, $exclude) !== false) {
                    continue 2;
                }
            }

            if ('.twig' !== substr($file, -5)) {
                if (is_dir($file)) {
                    $counter += $this->compileTwigDir($handler, (string) $file, $twigJsFilename, $excludes);
                }
                continue;
            }

            $request = new CompileRequest(basename($file), file_get_contents($file));
            file_put_contents(
                $twigJsFilename.'.js',
                $handler->process($request)
            );

            ++$counter;
        }

        return $counter;
    }

    /**
     * @param string $sourceDir
     *
     * @return \Twig_Environment
     */
    private function getTwigEnvironment($sourceDir)
    {
        $env = new Twig_Environment();
        $env->setLoader(
            new Twig_Loader_Filesystem(
                [
                    $sourceDir,
                ]
            )
        );

        $env->addExtension(new Twig_Extension_Core());
        $env->addExtension(new TwigJsExtension());

        foreach ($this->registeredFunctions as $functionName) {
            $env->addFunction(new Twig_SimpleFunction($functionName, true));
        }

        foreach ($this->registeredFilters as $filterName) {
            $env->addFilter(new Twig_SimpleFilter($filterName, true));
        }

        return $env;
    }

    /**
     * @return JsCompiler
     */
    private function getCompiler(Twig_Environment $twigEnv)
    {
        $compiler = new JsCompiler($twigEnv);
        $compiler->setFunctionNamingStrategy(new CamelCaseFunctionNameStrategy());

        return $compiler;
    }
}
