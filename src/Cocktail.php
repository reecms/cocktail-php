<?php

namespace Ree\Cocktail;

use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Filesystem\Filesystem;
use Ree\Cocktail\Contracts\Mixer;
use Ree\Cocktail\Contracts\Recipe;
use Ree\Cocktail\Mixers\CoffeeMixer;
use Ree\Cocktail\Mixers\LessMixer;
use Ree\Cocktail\Mixers\SassMixer;
use Ree\Cocktail\Mixers\GeneralMixer;

/**
 * Description of Cocktail
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
class Cocktail
{

    const APP_NAME    = 'Cocktail assets mixer for PHP';
    const APP_VERSION = '0.1.0';
    const FILE_NAME   = '.cocktail';

    /**
     * List of all containers;
     *
     * @var Container[]
     */
    protected $cups = [];

    /**
     * List of registered extension and their mixers
     *
     * @var type 
     */
    protected $mixers = [];

    /**
     * File system instance
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Current running dir
     *
     * @var type 
     */
    protected $dir;

    /**
     * Whether to run in production mode
     *
     * @var type 
     */
    protected $productionMode = false;

    /**
     * Registered callbacks
     *
     * @var type 
     */
    protected $callbacks = [];

    /**
     *
     * @var Mixer
     */
    protected $defaultMixer;

    public function __construct(Filesystem $files, $dir, $isProduction)
    {
        $this->files          = $files;
        $this->dir            = $dir;
        $this->productionMode = $isProduction;

        $this->registerBuiltInMixers();
    }

    public function mix(Recipe $recipe)
    {
        $this->callCallback('recipe.before', [$recipe]);

        $files = $this->prepareFileList($recipe->getSourceDir());
        foreach ($files as $file) {
            /* @var $file SplFileInfo */
            $ext   = $file->getExtension();
            $mixer = $this->getMixer($ext);
            $mixer->setImportPaths($recipe->getImportPaths());
            $dest  = $this->getBuildPath($recipe->getBuildDir(), $mixer, $file);

            $this->callCallback('file.before', [$recipe, $mixer, $file]);
            $mixer->compile($this->files, $file->getRealPath(), $dest);
        }

        $this->callCallback('recipe.after', [$recipe]);
    }

    /**
     * Register a mixer for an extension
     * 
     * @param string $extension
     * @param string $class
     * @param string $destExt
     * @return \Ree\Cocktail\Cocktail
     * @throws \InvalidArgumentException
     */
    public function registerMixer($extension, $class)
    {
        $refClass = new ReflectionClass($class);
        if (!$refClass->implementsInterface(Mixer::class)) {
            throw new \InvalidArgumentException("The {$class} does not implement our mixer contract.");
        }

        $mixer                    = new $class();
        $mixer->setProductionMode($this->productionMode);
        $this->mixers[$extension] = $mixer;

        return $this;
    }

    /**
     * Get the mixer for the extension
     * 
     * @param string $ext
     * @return Mixer
     */
    public function getMixer($ext)
    {
        if (!isset($this->mixers[$ext])) {
            return $this->getDefaultMixer();
        }
        return $this->mixers[$ext];
    }

    /**
     * Get the file system instance
     * 
     * @return Filesystem
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Get the current running dir without the slash at the end
     * 
     * @return string
     */
    function getDir()
    {
        return $this->dir;
    }

    public function getBuildPath($buildDir, Mixer $mixer, SplFileInfo $file)
    {
        $ext = $mixer->getOutputExtension();
        if (!$ext) {
            $ext = $file->getExtension();
        }

        $path = $file->getRelativePath();

        $filename = $file->getBasename("." . $file->getExtension());

        $build = $buildDir . "/" . $path;
        if (!$this->files->exists($build)) {
            $this->files->makeDirectory($build, 0755, true);
        }

        return "{$build}/{$filename}.{$ext}";
    }

    public function addCallback($name, $closure)
    {
        if (!isset($this->callbacks[$name])) {
            $this->callbacks[$name] = [];
        }
        $this->callbacks[$name][] = $closure;
    }

    protected function callCallback($name, $args)
    {
        if (!isset($this->callbacks[$name])) {
            return;
        }
        foreach ($this->callbacks[$name] as $callback) {
            call_user_func_array($callback, $args);
        }
    }

    protected function registerBuiltInMixers()
    {
        $this->registerMixer('scss', SassMixer::class);
        $this->registerMixer('less', LessMixer::class);
    }

    protected function getDefaultMixer()
    {
        if (!$this->defaultMixer) {
            $this->defaultMixer = new GeneralMixer();
        }

        return $this->defaultMixer;
    }

    protected function prepareFileList($sourceDir)
    {
        $sourceDir = $this->dir . "/" . $sourceDir;

        $finder = Finder::create()->files()->in($sourceDir)->filter(function(SplFileInfo $file) {

            $filename = $file->getFilename();

            return $filename[0] != '_';
        });

        $files = iterator_to_array($finder, false);

        return $files;
    }
}
