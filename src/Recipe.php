<?php

namespace Ree\Cocktail;

use Illuminate\Support\Arr;
use Ree\Cocktail\Contracts\Recipe as RecipeContract;

/**
 * Description of Recipe
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
class Recipe implements RecipeContract
{

    protected $sourceDir;
    protected $buildDir;
    protected $importPaths;

    public function __construct(array $config)
    {
        $this->sourceDir   = Arr::get($config, 'source', 'source');
        $this->buildDir    = Arr::get($config, 'build', 'build');
        $this->importPaths = Arr::get($config, 'imports', []);
    }

    public function addImportPaths($paths)
    {
        $this->importPaths = array_merge($this->importPaths, $paths);
    }

    public function getBuildDir()
    {
        return $this->buildDir;
    }

    public function getImportPaths()
    {
        return $this->importPaths;
    }

    public function getSourceDir()
    {
        return $this->sourceDir;
    }
}
