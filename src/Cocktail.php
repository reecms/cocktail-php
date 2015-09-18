<?php

namespace Ree\Cocktail;

use ReflectionClass;
use Illuminate\Filesystem\Filesystem;
use Ree\Cocktail\Contracts\Mixer;
use Ree\Cocktail\Container;
use Ree\Cocktail\Recipe;
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

    /**
     * Current running dir
     *
     * @var type 
     */
    protected $dir;

    /**
     * Registered callbacks
     *
     * @var type 
     */
    protected $callbacks = [];

    public function __construct(Filesystem $files, $dir)
    {
        $this->files = $files;
        $this->dir   = $dir;

        $this->registerBuiltInMixers();

        $this->getContainers();
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
     * @param string $destExt
     * @return \Ree\Cocktail\Cocktail
     * @throws \InvalidArgumentException
     */
    public function registerMixer($extension, $class, $destExt)
    {
        $refClass = new ReflectionClass($class);
        if (!$refClass->implementsInterface(Mixer::class)) {
            throw new \InvalidArgumentException("The {$class} does not implement our mixer contract.");
        }
        $this->mixers[$extension] = [
            'mixer' => new $class(),
            'ext'   => $destExt
        ];
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
        return $this->mixers[$ext]['mixer'];
    }

    /**
     * 
     * @param string $ext
     * @return Mixer
     * @throws \InvalidArgumentException
     */
    public function getDestExt($ext)
    {
        if (!isset($this->mixers[$ext])) {
            throw new \InvalidArgumentException("No mixer was registered for the extension [.{$ext}].");
        }
        return $this->mixers[$ext]['ext'];
    }

    /**
     * Get all containers;
     * 
     * @return Container[];
     */
    public function getCups()
    {
        return $this->cups;
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

    /**
     * Run before entering a container
     * 
     * @param type $callback
     */
    public function beforeContainer($callback)
    {
        if (!isset($this->callbacks['container.entering'])) {
            $this->callbacks['container.entering'] = [];
        }
        $this->callbacks['container.entering'][] = $callback;
    }

    /**
     * Run after leaving a container
     * 
     * @param type $callback
     */
    public function afterContainer($callback)
    {
        if (!isset($this->callbacks['container.left'])) {
            $this->callbacks['container.left'] = [];
        }
        $this->callbacks['container.lelf'][] = $callback;
    }

    /**
     * Run before compiling an assets
     * 
     * @param type $callback
     */
    public function beforeAsset($callback)
    {
        if (!isset($this->callbacks['asset.compiling'])) {
            $this->callbacks['asset.compiling'] = [];
        }
        $this->callbacks['asset.compiling'][] = $callback;
    }

    /**
     * Run after compiling an assets
     * 
     * @param type $callback
     */
    public function afterAsset($callback)
    {
        if (!isset($this->callbacks['asset.compiled'])) {
            $this->callbacks['asset.compiled'] = [];
        }
        $this->callbacks['asset.compiled'][] = $callback;
    }

    /**
     * Run before entering a container
     * 
     * @param Container $container
     */
    public function enteringContainer(Container $container)
    {
        if (!isset($this->callbacks['container.entering'])) {
            return;
        }
        foreach ($this->callbacks['container.entering'] as $callback) {
            call_user_func($callback, $container);
        }
    }

    /**
     * Run after leaving a container
     * 
     * @param Container $container
     */
    public function leftContainer(Container $container)
    {
        if (!isset($this->callbacks['container.left'])) {
            return;
        }
        foreach ($this->callbacks['container.lelf'] as $callback) {
            call_user_func($callback, $container);
        }
    }

    /**
     * Run before compiling an assets
     * 
     * @param string $name
     * @param string $ext
     * @param string $source
     * @param string $path
     */
    public function compilingAsset($name, $ext, $source, $path)
    {
        if (!isset($this->callbacks['asset.compiling'])) {
            return;
        }
        foreach ($this->callbacks['asset.compiling'] as $callback) {
            call_user_func($callback, $name, $ext, $source, $path);
        }
    }

    /**
     * Run after compiling an assets
     * 
     * @param string $name
     * @param string $ext
     * @param string $source
     * @param string $path
     * @param string $error
     */
    public function compiledAsset($name, $ext, $source, $path, $error = false)
    {
        if (!isset($this->callbacks['asset.compiled'])) {
            return;
        }
        foreach ($this->callbacks['asset.compiled'] as $callback) {
            call_user_func($callback, $name, $ext, $source, $path, $error);
        }
    }

    protected function registerBuiltInMixers()
    {
        $this->registerMixer('scss', SassMixer::class, 'css');
        $this->registerMixer('less', LessMixer::class, 'css');
        $this->registerMixer('coffee', CoffeeMixer::class, 'js');
        $this->registerMixer('js', JsMixer::class, 'js');
    }

    protected function getContainers()
    {
        $recipe = new Recipe($this->files);

        $this->cups = $recipe->read();
    }
}
