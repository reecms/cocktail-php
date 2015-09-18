<?php

namespace Ree\Cocktail;

use Illuminate\Filesystem\Filesystem;
use Ree\Cocktail\Container;

/**
 * Description of Recipe
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
class Recipe
{

    const FILE_NAME = '.cocktail';

    /**
     * File system instance
     *
     * @var Filesystem
     */
    protected $files;
    
    function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

        /**
     * Create containers from the recipe file
     * 
     * @return Container[]
     */
    public function read()
    {
        $recipeFile = static::FILE_NAME;
        
        if (!$this->files->exists($recipeFile)) {
            return [];
        }

        $recipes = $this->files->getRequire($recipeFile);

        $containers = [];

        foreach ($recipes as $source => $dest) {
            $container    = new Container($source, $dest);
            $containers[] = $container;
        }

        return $containers;
    }
}
