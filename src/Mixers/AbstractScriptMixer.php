<?php

namespace Ree\Cocktail\Mixers;

use Ree\Cocktail\Contracts\Mixer;

/**
 * Description of AbstractScriptMixer
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
abstract class AbstractScriptMixer implements Mixer
{

    protected $importPaths = [];

    public function setImportPaths(array $paths)
    {
        $this->importPaths = $paths;
    }

    protected function getPath($source)
    {
        $file = new SplFileInfo($source);
        return $file->getPath();
    }
}
