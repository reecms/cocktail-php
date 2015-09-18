<?php

namespace Ree\Cocktail;

use ReflectionClass;
use Illuminate\Filesystem\Filesystem;
use Ree\Cocktail\Contracts\Mixer;
use Ree\Cocktail\Container;
use Ree\Cocktail\Mixers\CoffeeMixer;
use Ree\Cocktail\Mixers\JsMixer;
use Ree\Cocktail\Mixers\LessMixer;
use Ree\Cocktail\Mixers\SassMixer;

/**
 * Description of Cocktail
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
class Cocktail
{

    const APP_NAME    = 'Cocktail assets mixer for PHP';
    const APP_VERSION = '0.1.0';

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

    public function __construct(Filesystem $files)
    {
        $this->files = $files;
        $this->registerBuiltInMixers();
    }

    public function mix()
    {
        foreach ($this->cups as $cup) {
            $cup->compile($this);
        }
    }

    /**
     * 
     * @param string $extension
     * @param string $class
     * @return \Ree\Cocktail\Cocktail
     * @throws \InvalidArgumentException
     */
    public function registerMixer($extension, $class)
    {
        $refClass = new ReflectionClass($class);
        if (!$refClass->implementsInterface(Mixer::class)) {
            throw new \InvalidArgumentException("The {$class} does not implement our mixer contract.");
        }
        $this->mixers[$extension] = new $class();
        return $this;
    }

    /**
     * 
     * @param string $ext
     * @return Mixer
     * @throws \InvalidArgumentException
     */
    public function getMixer($ext)
    {
        if (!isset($this->mixers[$ext])) {
            throw new \InvalidArgumentException("No mixer was registered for the extension [.{$ext}].");
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

    public function output($ext, $file)
    {
        
    }

    protected function registerBuiltInMixers()
    {
        $this->registerMixer('scss', SassMixer::class);
        $this->registerMixer('less', LessMixer::class);
        $this->registerMixer('coffee', CoffeeMixer::class);
        $this->registerMixer('js', JsMixer::class);
    }
}
