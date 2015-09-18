<?php

namespace Ree\Cocktail\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Description of Application
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
class Application extends BaseApplication
{

    protected function getCommandName(InputInterface $input)
    {
        return 'mix';
    }

    protected function getDefaultCommands()
    {
        $defaultCommands   = parent::getDefaultCommands();
        $defaultCommands[] = new MixCommand();

        return $defaultCommands;
    }
    
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();
        
        return $inputDefinition;
    }
}
