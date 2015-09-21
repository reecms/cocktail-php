<?php

namespace Ree\Cocktail\Contracts;

use Illuminate\Filesystem\Filesystem;

/**
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
interface Mixer
{

    public function getOutputExtension();

    public function setProductionMode($isProduction);
    
    public function setImportPaths(array $paths);

    public function compile(Filesystem $files, $source, $dest);
}
