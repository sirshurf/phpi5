<?php
require_once APPLICATION_PATH . '/Bootstrap.php';

class Bootstrap_Cron extends Bootstrap
{
    public function run()
    {
    	echo ("Bootstrap Cron Start").PHP_EOL;
        try {
            if ($this->hasPluginResource('cron')) {
                $this->bootstrap('cron');
                $server = $this->getResource('cron');
                echo $server->run();
            } else {
                echo 'The cron plugin resource needs to be configured in application.ini.' . PHP_EOL;
            }
        } catch (Exception $e) {
            echo 'An error has occured.' . PHP_EOL;
            echo $e->getMessage() . PHP_EOL;
            echo $e->getTraceAsString() . PHP_EOL;
        }
    }
}