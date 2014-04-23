<?php
/**
 *
 */
namespace Slim;

/**
 * Sys Log Writer
 *
 * This class is used by Slim_Log to write log messages to syslog
 *
 * @package Slim
 * @author  Josh Lockhart
 * @since   1.6.0
 */
class SysLogWriter extends \Slim\LogWriter
{

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->resource = null;
    }

    /**
     * Write message
     * @param  mixed     $message
     * @param  int       $level
     * @return int|bool
     */
    public function write($message, $level = null)
    {
        return syslog($level, (string) $message);
    }
}