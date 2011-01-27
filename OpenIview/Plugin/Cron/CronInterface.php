<?php 
interface Openiview_Plugin_Cron_CronInterface
{
    public function __construct($args = null);

    /**
     * Run the cron task
     *
     * @return void
     * @throws Blahg_Plugin_Cron_Exception to describe any errors that should be returned to the user
     */
    public function run();

    /**
     * Lock
     * @return integer pid of this process
     * @throws Blahg_Plugin_Cron_Exception if already locked
     */
    public function lock();

    /**
     * Unlock
     * @return boolean true if successful
     * @throws Blahg_Plugin_Cron_Exception if an error occurs
     */
    public function unlock();

    /**
     * Is locked
     * @return integer|boolean pid of existing process or false if there isn't one
     */
    public function isLocked();

}