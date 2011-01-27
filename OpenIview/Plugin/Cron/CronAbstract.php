<?php
abstract class Openiview_Plugin_Cron_CronAbstract implements Openiview_Plugin_Cron_CronInterface
{
	
	private $_options = array();
	
	public function __construct($args = null){
		if (!empty($args)){
			$this->_options = (array)$args;
		}
	}
	
    public function lock()
    {    
    	$pid = $this->isLocked();
        if ($pid) {
            throw new Openiview_Plugin_Cron_Exception('This task is already locked.Pid: '.$pid);
        }

        $pid = getmypid();
        if (!file_put_contents($this->_getLockFile(), $pid)) {
            throw new Openiview_Plugin_Cron_Exception('A lock could not be obtained.');
        }

        return $pid;
    }

    public function unlock()
    {   
        if (!file_exists($this->_getLockFile())) {
            throw new Openiview_Plugin_Cron_Exception('This task is not locked.');
        }

        if (!unlink($this->_getLockFile())) {
            throw new Openiview_Plugin_Cron_Exception('The lock could not be deleted.');
        }

        return true;
    }

    public function isLocked()
    {
        if (!file_exists($this->_getLockFile())) {
            return false;
        }

        return file_get_contents($this->_getLockFile());
    }

    protected function _getLockFile()
    {   
        $fileName = 'cron.' . get_class($this) . '.lock';
        $lockFile = realpath(APPLICATION_PATH . '/../files/tmp/') . '/' . $fileName;
        return $lockFile;
    }
}