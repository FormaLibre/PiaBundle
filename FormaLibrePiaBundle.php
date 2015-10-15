<?php

namespace FormaLibre\PiaBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;

class FormaLibrePiaBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();
        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null,'pia');
    }

    public function hasMigrations()
    {
        return true;
    }
}

?>
