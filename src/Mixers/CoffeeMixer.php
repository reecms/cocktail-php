<?php

namespace Ree\Cocktail\Mixers;

use Illuminate\Filesystem\Filesystem;

/**
 * Description of CoffeeMixer
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
class CoffeeMixer extends AbstractScriptMixer
{

    public function compile(Filesystem $files, $source, $dest)
    {
        
    }

    public function getOutputExtension()
    {
        return 'js';
    }

    public function setProductionMode($isProduction)
    {
        
    }
}
