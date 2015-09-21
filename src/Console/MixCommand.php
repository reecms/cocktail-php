<?php

namespace Ree\Cocktail\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\SplFileInfo;
use Illuminate\Filesystem\Filesystem;
use Ree\Cocktail\Contracts\Mixer;
use Ree\Cocktail\Cocktail;
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
            ->setDescription('Compile defined assets.')
            ->addOption('production', 'P', InputOption::VALUE_NONE, 'Is in production mode or not');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->getApplication()->getName() . " - " . $this->getApplication()->getVersion() . " - by Hieu Le");

        $files    = new Filesystem;
        $dir      = getcwd();
        $cocktail = new Cocktail($files, $dir, $input->getOption('production'));
        $recipes  = $this->parseRecipes($files);

        $numCups    = count($recipes);
        $recipeFile = Cocktail::FILE_NAME;

        $output->writeln('');
        $output->writeln("<info>Found {$numCups} cups from recipe files [{$recipeFile}].</info>");
        $output->writeln('');

        $cocktail->addCallback('recipe.before', function(Recipe $recipe) use ($output) {
            $output->writeln("<fg=green>Enter:</> <fg=yellow>{$recipe->getSourceDir()}</>");
        });
        $cocktail->addCallback('recipe.after', function(Recipe $recipe) use ($output) {
            $output->writeln("<fg=green>Done.</>\n");
        });
        $cocktail->addCallback('file.before', function(Recipe $recipe, Mixer $mixer, SplFileInfo $file) use($output) {
            $path = $file->getRelativePath();
            $ext  = $file->getExtension();
            $name = $file->getBasename(".{$ext}");
            $prefix = sprintf("%-8s", "[{$ext}]");
            $output->writeln("  <info>{$prefix}</info> {$path}/{$name}.{$ext} ... ");
        });

        foreach ($recipes as $recipe) {
            $cocktail->mix($recipe);
        }
    }

    protected function parseRecipes(Filesystem $files)
    {
        $recipes = [];
        if ($files->exists(Cocktail::FILE_NAME)) {

            foreach ($files->getRequire(Cocktail::FILE_NAME) as $config) {
                $recipes[] = new Recipe($config);
            }
        }

        return $recipes;
    }
}
