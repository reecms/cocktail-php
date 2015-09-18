<?php

namespace Ree\Cocktail;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
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

    function __construct($sourceDir, $destDir)
    {
        $this->sourceDir = $sourceDir;
        $this->destDir   = $destDir;
    }

    function getSourceDir()
    {
        return $this->sourceDir;
    }

    function getDestDir()
    {
        return $this->destDir;
    }

    /**
     * Compile files
     */
    public function compile(Cocktail $cocktail)
    {
        $cocktail->enteringContainer($this);

        $files = $this->prepareFileList($cocktail);
        foreach ($files as $file) {
            $ext      = $this->getExt($file);
            $filename = $this->getBaseName($file, $ext);
            $path     = $this->getPath($file);

            $source     = $this->getSource($file);
            $detination = $this->getDesitnation($cocktail, $filename, $cocktail->getDestExt($ext), $path);

            $cocktail->compilingAsset($filename, $ext, $source, $path);
            $cocktail->getMixer($ext)->compile($source, $detination);
            $cocktail->compiledAsset($filename, $ext, $source, $path);
        }

        $cocktail->leftContainer($this);
    }

    protected function prepareFileList(Cocktail $cocktail)
    {
        $sourceDir = $cocktail->getDir() . "/" . $this->sourceDir;

        $finder = Finder::create()->files()->in($sourceDir)->filter(function(SplFileInfo $file) {

            $filename = $file->getFilename();

            return $filename[0] != '_';
        });

        $files = iterator_to_array($finder, false);

        return $files;
    }

    protected function getExt(SplFileInfo $file)
    {
        return $file->getExtension();
    }

    protected function getBaseName(SplFileInfo $file, $ext)
    {
        return $file->getBasename(".{$ext}");
    }

    protected function getPath(SplFileInfo $file)
    {
        return $file->getRelativePath();
    }

    protected function getSource(SplFileInfo $file)
    {
        return $file->getRealPath();
    }

    protected function getDesitnation(Cocktail $cocktail, $filename, $ext, $path)
    {
        $dirPath = $cocktail->getDir() . "/" . $this->destDir . "/" . $path;

        if (!$cocktail->getFiles()->isDirectory($dirPath)) {
            $cocktail->getFiles()->makeDirectory($dirPath, 0755, true);
        }

        return $dirPath . "/{$filename}.{$ext}";
    }
}
