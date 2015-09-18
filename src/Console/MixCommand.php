<?php

namespace Ree\Cocktail\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Filesystem\Filesystem;
use Ree\Cocktail\Cocktail;
use Ree\Cocktail\Container;
use Ree\Cocktail\Recipe;

/**
 * Description of MixCommand
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
class MixCommand extends Command
{

    protected function configure()
    {
        $this->setName('mix')
            ->setDescription('Compile defined assets.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getApplication()->getName() . " " . $this->getApplication()->getVersion());

        $files    = new Filesystem;
        $dir      = getcwd();
        $cocktail = new Cocktail($files, $dir);

        $numCups    = count($cocktail->getCups());
        $recipeFile = Recipe::FILE_NAME;

        $output->writeln("Found {$numCups} cups from recipe files [{$recipeFile}].");

        $cocktail->beforeContainer(function(Container $container) use ($output) {
            $output->writeln("Enter: {$container->getSourceDir()}");
        });
        $cocktail->afterContainer(function(Container $_) use ($output) {
            $output->writeln("DONE\n");
        });
        $cocktail->beforeAsset(function($name, $ext, $source, $path) use($output) {
            $output->write(" [{$ext}] {$source} ... ");
        });
        $cocktail->afterAsset(function($name, $ext, $source, $path, $error) use($output) {
            $out = $error ? "error" : "ok";
            $output->writeln(" {$out}");
        });

        $cocktail->mix();
    }
}
