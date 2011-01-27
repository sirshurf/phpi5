<?php
class Openiview_Plugin_Cron_TouchFile extends Openiview_Plugin_Cron_CronAbstract
{
    protected $_filename;

    public function __construct($args = null)
    {
        if (!is_array($args) || !array_key_exists('filename', $args)) {
            throw new Openiview_Plugin_Cron_Exception('The FileToucher cron task plugin is not configured correctly.');
        }
        $this->_filename = $args['filename'];
 
    }

    public function run()
    {
        $result = touch($this->_filename);
        if (!$result) {
            throw new Openiview_Plugin_Cron_Exception('The file timestamp could not be updated.');
        }
    }
    
    
}