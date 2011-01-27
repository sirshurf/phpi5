<?php
class Openiview_Plugin_Resource_Cron extends Zend_Application_Resource_ResourceAbstract
{
    public function init()
    {
        $options = $this->getOptions();

        if (array_key_exists('pluginPaths', $options)) {
            $cron = new Openiview_Service_Cron($options['pluginPaths']);
        } else {
            $cron = new Openiview_Service_Cron(array(
                'Openiview_Plugin_Cron' => realpath(APPLICATION_PATH . "/../library/Openiview/Plugin/Cron"),
            ));
        }

        if (array_key_exists('actions', $options)) {
            foreach ($options['actions'] as $name => $args) {
                $cron->addAction($name, $args);
            }
        }

        return $cron;
    }
}