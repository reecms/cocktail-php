<?php

namespace Ree\Cocktail\Mixers;

use Less_Parser;
use Illuminate\Filesystem\Filesystem;

/**
 * Description of LessMixer
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
class LessMixer extends AbstractScriptMixer
{

    protected $isProduction = false;

    public function compile(Filesystem $files, $source, $dest)
    {
        $compiler = new Less_Parser();
        if ($this->isProduction) {
            $compiler->SetOption('compress', true);
        } else {
            $compiler->SetOption('sourceMap', true);
        }

        $compiler->SetImportDirs(array_merge([$this->getPath($source)], $this->importPaths));

        $less = $files->get($source);
        $compiler->parse($less);
        $css  = $compiler->getCss();
        $files->put($dest, $css);
    }

    public function getOutputExtension()
    {
        return 'css';
    }

    public function setProductionMode($isProduction)
    {
        $this->isProduction = $isProduction;
    }
}
