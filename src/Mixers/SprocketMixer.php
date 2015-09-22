<?php

namespace Ree\Cocktail\Mixers;

use RuntimeException;
use SplFileInfo;
use Illuminate\Filesystem\Filesystem;
use Ree\Cocktail\Mixers\AbstractScriptMixer;

/**
 * Description of SprocketMixer
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
class SprocketMixer extends AbstractScriptMixer
{

    protected $isProduction;
    protected $paths = [];

    public function compile(Filesystem $files, $source, $dest)
    {
        $file = new SplFileInfo($source);

        $this->resetPaths();

        $content = $this->parse($files, $file);

        $files->put($dest, $content);
    }

    public function getOutputExtension()
    {
        return false;
    }

    public function setProductionMode($isProduction)
    {
        $this->isProduction = $isProduction;
    }

    protected function resetPaths()
    {
        $this->paths = [];
    }

    protected function parse(Filesystem $files, SplFileInfo $file)
    {
        $path = $file->getPathname();
        if (in_array($path, $this->paths)) {
            throw new RuntimeException("Recursively requirements [{$path}].");
        }
        $this->paths[] = $path;
        $content       = trim($files->get($file));

        $lines    = explode("\n", $content);
        $newLines = [];

        $inComment       = $firstCommentEnd = false;

        $requiredContents = [];

        foreach ($lines as $l) {
            // if we meet the block comment openning "/*"
            if (!$firstCommentEnd && $this->isCommentStart($l)) {
                $inComment  = true;
                $newLines[] = $l;
                continue;
            }

            // if we meet the block comment closing "*/"
            if ($inComment && $this->isCommentEnd($l)) {
                $inComment       = false;
                $firstCommentEnd = false;
                $newLines[]      = $l;
                continue;
            }

            // if we are in the first block comment 
            // if we meet javascript line comments
            if (($inComment && $this->isBlockDirective($l)) || (!$firstCommentEnd && $this->isLineDirective($l))) {

                $require = $this->extractRequires($l);
                if (!$require) {
                    $newLines[] = $l;
                    continue;
                }

                $requiredContents[] = $this->tryRequire($files, $file, $require);
                continue;
            }

            // other lines
            if (trim($l)) {
                $firstCommentEnd = true;
            }
            $newLines[] = $l;
        }

        return implode("\n", array_merge($requiredContents, $newLines));
    }

    protected function isCommentStart($l)
    {
        $line = trim($l);
        return substr($line, 0, 2) == '/*' && !preg_match('/(\\/\\*.*\\*\\/)/', $line);
    }

    protected function isCommentEnd($l)
    {
        $line = trim($l);
        return strpos("*/", $line) !== false;
    }

    protected function isLineDirective($l)
    {
        $line = trim($l);
        return substr($line, 0, 2) == '//';
    }

    protected function isBlockDirective($l)
    {
        $line = trim($l);
        return substr($line, 0, 2) == '*=';
    }

    protected function extractRequires($l)
    {
        $line    = trim($l);
        $matches = [];
        $re      = "/(?:\\*|\\/\\/)=[ \\t]+require[ \\t]+(.+)/";

        if (preg_match($re, $line, $matches)) {
            return $matches[1];
        }

        return false;
    }

    protected function tryRequire(Filesystem $files, SplFileInfo $thisFile, $thatFile)
    {
        $path     = $thisFile->getPath();
        $ext      = $thisFile->getExtension();
        $filename = $thisFile->getBasename(".{$ext}");


        $file = "{$path}/{$thatFile}.{$ext}";

        if ($files->exists($file) && $files->isFile($file)) {

            $newFile = new SplFileInfo($file);
            return $this->parse($files, $newFile);
        }


        throw new RuntimeException("Cannot require the file [{$thatFile}] from [{$path}/{$filename}.{$ext}]");
    }
}
