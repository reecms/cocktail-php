<?php

namespace Ree\Cocktail;

use Ree\Cocktail\Cocktail;

/**
 * A container contains definitions of source directory and destination 
 * directory. Files in source directory will be compiled and the output is 
 * written into the destination directory mimic their tree structure in the 
 * source one.
 * 
 * Files whose name begins with an underscore (_) is ignored in the destination
 * directory.
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
class Container
{

    /**
     * The absolute path to the source directory
     *
     * @var string
     */
    protected $sourceDir;

    /**
     * The absolute path to the destination directory
     *
     * @var string
     */
    protected $destDir;

    /**
     * Compile files
     */
    public function compile(Cocktail $cocktail)
    {
        $files = $this->prepareFileList($cocktail);
        foreach ($files as $file => $output) {
            $ext = $this->getExt($file);
            $cocktail->output($ext, $file);
            $cocktail->getMixer($ext)->compile($file, $output);
        }
    }

    protected function prepareFileList(Cocktail $cocktail)
    {
        
    }

    protected function getExt($file)
    {
        
    }
}
