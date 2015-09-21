<?php

namespace Ree\Cocktail\Mixers;

use Leafo\ScssPhp\Compiler;
use Leafo\ScssPhp\Formatter\Compressed as CompressedOutput;
use Leafo\ScssPhp\Formatter\Expanded as ExpandedOutput;
use Illuminate\Filesystem\Filesystem;

/**
 * Description of SassMixer
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
class SassMixer extends AbstractScriptMixer
{

    /**
     *
     * @var Compiler
     */
    protected $compiler;

    public function __construct()
    {
        $this->compiler = new Compiler();
        $this->compiler->setLineNumberStyle(Compiler::LINE_COMMENTS);
        $this->compiler->setFormatter(ExpandedOutput::class);
    }

    public function compile(Filesystem $files, $source, $dest)
    {
        $scss = $files->get($source);

        $this->compiler->setImportPaths(array_merge([$this->getPath($source)], $this->importPaths));

        $css = $this->compiler->compile($scss);
        $files->put($dest, $css);
    }

    public function getOutputExtension()
    {
        return 'css';
    }

    public function setProductionMode($isProduction)
    {
        if ($isProduction) {
            $this->compiler->setLineNumberStyle(0);
            $this->compiler->setFormatter(CompressedOutput::class);
        }
    }
}
