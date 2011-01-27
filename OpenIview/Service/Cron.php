<?php
class Openiview_Service_Cron {
	protected $_loader;
	protected $_actions = array ();
	protected $_actionsArgs = array ();
	protected $_errors = array ();
	protected $_log;
	
	public function __construct(array $pluginPaths) {
		$this->_loader = new Zend_Loader_PluginLoader ( $pluginPaths );
	}
	
	/**
	 * Get loader
	 *
	 * @return Zend_Loader_PluginLoader
	 */
	public function getLoader() {
		return $this->_loader;
	}
	
	public function run()
{
    // Initialize the log before we fork; that way child processes will
    // have a reference to the same log as the parent process.
    $log = $this->getLog();
    $children = array();
    foreach ($this->_actions as $key => $action) {
        $class = $this->getLoader()->load($action);
        if (null !== $this->_actionsArgs[$key]) {
            $action = new $class($this->_actionsArgs[$key]);
        } else {
            $action = new $class;
        }

        if (!($action instanceof Openiview_Plugin_Cron_CronInterface)) {
            throw new Openiview_Service_Exception('One of the specified actions is not the right kind of class.');
        }

        // Check to see if this task is locked (currently running,
        // probably due to an earlier cron run); if it is, don't run
        // it again.
        if ($action->isLocked()) {
            continue;
        }

        $pid = pcntl_fork();
        if ($pid == -1) {
            $log->err('Could not fork.');
            continue;
        } else if ($pid == 0) {
            // This is the child.
            $mypid = getmypid();

            unset($children);
            try {
                $action->lock();
                $action->run();
            } catch (Openiview_Plugin_Cron_Exception $e) {
                $log->err('[' . $mypid . '] ' . $e->getMessage());
            } catch (Exception $e) {
                if (APPLICATION_ENV == 'development') {
                    $log->err('[' . $mypid . '] [DEV]: ' . $e->getMessage());
                } else {
                    $log->err('[' . $mypid . '] An undefined error occurred.');
                }
            }

            // Unlock regardless of results.
            try {
                $action->unlock();
            } catch (Exception $e) {
                if (APPLICATION_ENV == 'development') {
                    $log->err('[' . $mypid . '] [DEV]: ' . $e->getMessage());
                } else {
                    $log->err('[' . $mypid . '] An unlocking error occurred.');
                }
            }

            // Child process doesn't need to continue; it's done its job.
            exit;
        } else {
            // This is the parent.
            $children[] = $pid;
        }
    }

    // Now that we've started all the actions, we just need to wait
    // for them to finish and clean everything up.  The following
    // gets rid of the zombie processes leftover when the child
    // processes die.
    foreach ($children as $child) {
        pcntl_waitpid($child, $status);
    }

    // At this point all the child processes should be finished; we can
    // output the log.  Save a copy as "cron.latest.log" so we can look
    // it over if necessary, but in general if there's any output it'll
    // be emailed to the cron runner user anyway.
    $output = file_get_contents($this->getLogFile());
    rename($this->getLogFile(), realpath(APPLICATION_PATH . '/../log/') . '/cron.latest.log');
    return $output;
}
	
	public function addAction($action, $args = null) {
		$key = count ( $this->_actions ) + 1;
		$this->_actions [$key] = $action;
		$this->_actionsArgs [$key] = $args;
		return $this;
	}
	
	public function addError($message) {
		$this->_errors [] = $message;
		return $this;
	}
	
	public function getErrors() {
		return $this->_errors;
	}
	
	public function getLogFile() {
		return realpath ( APPLICATION_PATH . '/../files/log' ) . '/cron.' . getmypid () . '.log';
	}
	
	public function getLog() {
		if (null === $this->_log) {
			$writer = new Zend_Log_Writer_Stream ( $this->getLogFile () );
			$formatter = new Zend_Log_Formatter_Simple ( '%timestamp% %priorityName% (%priority%): %message%' . PHP_EOL );
			$writer->setFormatter ( $formatter );
			
			$log = new Zend_Log ();
			$log->addWriter ( $writer );
			$this->setLog ( $log );
		}
		return $this->_log;
	}
	
	public function setLog(Zend_Log $log) {
		if (null !== $this->_log) {
			// Letting the log be set and re-set by various processes could result in child processes
			// using a different log file than the parent process; we can't have that.
			throw new Openiview_Service_Exception ( 'The log has already been established; it cannot be set again.' );
		}
		$this->_log = $log;
		return $this;
	}
}