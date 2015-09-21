<?php

namespace Ree\Cocktail\Mixers;

use Illuminate\Filesystem\Filesystem;
use Ree\Cocktail\Contracts\Mixer;

/**
 * Description of GeneralMixer
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
class GeneralMixer implements Mixer
{

    public function compile(Filesystem $files, $source, $dest)
    {
        $files->copy($source, $dest);
    }

    public function getOutputExtension()
    {
        return false;
    }

    public function setProductionMode($isProduction)
    {
        return;
    }

    public function setImportPaths(array $paths)
    {
        return false;
    }
}
