<?php

namespace Ree\Cocktail\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Filesystem\Filesystem;
use Ree\Cocktail\Cocktail;

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
        $cocktail = new Cocktail($files);

        $cocktail->mix();
    }
}
