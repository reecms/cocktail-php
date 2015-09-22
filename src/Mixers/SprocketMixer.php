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
    protected $paths      = [];
    protected $cssImports = [];
    protected $isCss      = false;

    public function compile(Filesystem $files, $source, $dest)
    {
        $file = new SplFileInfo($source);

        $this->reset();

        $this->isCss = $file->getExtension() == 'css';

        $content = $this->parse($files, $file, true);

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

    protected function reset()
    {
        $this->paths      = [];
        $this->isCss      = false;
        $this->cssImports = [];
    }

    protected function parse(Filesystem $files, SplFileInfo $file, $writeImports = false)
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

            if ($this->isCssImport($l)) {
                $this->cssImports[] = $l;
                continue;
            }

            $newLines[] = $l;
        }

        if ($writeImports) {
            return implode("\n", array_merge($this->cssImports, $requiredContents, $newLines));
        } else {
            return implode("\n", array_merge($requiredContents, $newLines));
        }
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

    protected function isCssImport($l)
    {
        if (!$this->isCss) {
            return false;
        }

        $line = trim($l);

        return substr($line, 0, 8) == '@import ';
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


        throw new RuntimeException("Cannot require the file [{$thatFile}.{$ext}] from [{$path}/{$filename}.{$ext}]");
    }
}
