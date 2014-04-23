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
        return syslog($this->mapSlimLogLevelToSysLogLevel($level), (string) $message);
    }

    private function mapSlimLogLevelToSysLogLevel($level){
        $syslogLevel = LOG_INFO; // default?

        switch($level){
            case Log::EMERGENCY:
                $syslogLevel = LOG_EMERG;
                break;
            case Log::ALERT:
                $syslogLevel = LOG_ALERT;
                break;
            case Log::CRITICAL:
                $syslogLevel = LOG_CRIT;
                break;
            case Log::FATAL:
                $syslogLevel = LOG_CRIT;
                break;
            case Log::ERROR:
                $syslogLevel = LOG_ERR;
                break;
            case Log::WARN:
                $syslogLevel = LOG_WARNING;
                break;
            case Log::NOTICE:
                $syslogLevel = LOG_NOTICE;
                break;
             case Log::INFO:
                $syslogLevel = LOG_INFO;
                break;
             case Log::DEBUG:
                $syslogLevel = LOG_DEBUG;
                break;
        }

        return $syslogLevel;


    }
}