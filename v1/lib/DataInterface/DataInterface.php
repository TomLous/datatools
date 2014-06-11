<?php
/**
 * Created by PhpStorm.
 * User: tomlous
 * Date: 23/04/14
 * Time: 15:48
 */

namespace DataInterface;

use DataInterface\Exception\IncompatibleInputException;
use DataInterface\Exception\IncompatibleInterfaceException;
use DataInterface\Exception\InterfaceQuotaExceededException;
use Slim\Slim;

/**
 * Default interface for all custom crawler, api, validation classes
 *
 * Class DataInterface
 * @package DataInterface
 */
abstract class DataInterface
{

    /**
     * Refernce to the slim framework app
     * @var null|Slim
     */
    protected $slim;

    /**
     * @var int remainingQueries
     * @todo do something with this info
     */
    protected static $remainingQueries = null;

    /**
     * @var int usedQueries
     * @todo do something with this info
     */
    protected static $usedQueries = 0;

    /**
     * @var int quotaResetTimestamp
     * @todo do something with this info
     */
    protected static $quotaResetTimestamp = null;

    /**
     * @var bool meta variable to set bound state for class
     */
    protected static $_bound = false;


    /**
     * General construct method, init all extended classes with base
     */
    function __construct()
    {
        // sets statics
        $this->_setLateStaticBinding();


        // reference to slim framework
        $this->slim = Slim::getInstance();

        // load enviroment config from Slim
        $config = $this->slim->environment();

        // retrieve classnamespace for current object
        $classNamespace = get_class($this);

        // split namespace in parts
        $classNameParts = explode('\\', $classNamespace);

        // retrieve config from Slim based on nested params configed in Slim config
        foreach ($classNameParts as $className) {
            if ((is_array($config) || is_object($config)) && isset($config[$className])) {
                $config = $config[$className];
            } else {
                $config = null; // no config params for this instance
            }

        }

        // if the final config is found, set a property
        if (is_array($config)) {
            foreach ($config as $property => $value) {
                $staticSetterMethod = 'set' . ucfirst($property);

                if (method_exists($classNamespace, $staticSetterMethod)) {
                    call_user_func_array(array($classNamespace, $staticSetterMethod), array($value));
                }elseif (property_exists($this, $property)) {
                    $this->$property = $value;
                }
            }
        }

        // call query quota settings
       // $this->checkResetQueryQuota();

    }

    /**
     * Creates a POST API interface for DataInterface API's
     * Just call POST request to [$path]/[classname]/[method] and POST an array of parameters as form-data
     * @param \Slim|\Slim\Slim $app
     * @param string $path
     * @return \Slim\Route
     */
    public static function createSlimAPIRoutes(Slim $app, $path = 'DataInterface')
    {
        $route = $app->post('/' . $path . '/:directory/:api/:endpoint', function ($directory, $api, $endpoint) use ($app) {

            // Set basic return data for the API
            $data = array();
            $data['success'] = false;
            $data['Meta'] = array();
            $data['Meta']['requestUri'] = $app->request()->getResourceUri();
            $data['Meta']['requestData'] = $app->request()->params();
            $data['Meta']['ip'] = $app->request()->getIp();

            try {
                $className = '\\DataInterface\\' . $directory . '\\' . $api;

                if (!class_exists($className)) {
                    throw new IncompatibleInputException('Non existing DataInterface API: `' . $directory . '\\'. $api . '`');
                }

                $apiInstance = new $className();

                if (!method_exists($apiInstance, $endpoint)) {
                    throw new IncompatibleInputException('Non existing DataInterface endpoint `' . $endpoint . '` for API `' . $api . '`');
                }

                // call the method on the class and map its output to data object
                $data = array_merge_recursive($apiInstance->$endpoint($app->request()->params()), $data);
                $data['success'] = true;

                if ($app->config('debug')) {
                    $app->getLog()->debug(print_r($data, true));
                }


            } catch (IncompatibleInputException $e) { // User error (wrong entry)
                $data['message'] = $e->getMessage();
                $data['errorSource'] = 'User';
                $app->getLog()->notice(print_r($data, true));

            } catch (IncompatibleInterfaceException $e) { // API error (connection failed)
                $data['message'] = $e->getMessage();
                $data['errorSource'] = 'API';
                $app->getLog()->warn(print_r($data + array('trace' => $e->getTrace()), true));

            } catch (\Exception $e) { // Catch all other exceptions
                $data['message'] = $e->getMessage();
                $data['errorSource'] = 'unknown';
                $app->getLog()->warn(print_r($data + array('trace' => $e->getTrace()), true));

            } catch (Exception $e) { // Catch all other exceptions
                $data['message'] = $e->getMessage();
                $data['errorSource'] = 'unknown';
                $app->getLog()->warn(print_r($data + array('trace' => $e->getTrace()), true));
            }

            // Always output JSON
            header("Content-Type: application/json");
            print json_encode($data);

        })->name('DataInterfacePost');
        return $route;
    }

    /**
     * Breaks static references for inheretid classes and makes late static binding work as expected
     * @see: http://stackoverflow.com/questions/5513484/php-static-variables-in-an-abstract-parent-class-question-is-in-the-sample-code
     */
    private function _setLateStaticBinding(){
        if(!static::$_bound){
            // Workaround
            $tmp = 'x';
            static::$_bound = &$tmp;
            static::$_bound = true;

            static::$remainingQueries = &$tmp;
            static::$remainingQueries = null;

            static::$usedQueries = &$tmp;
            static::$usedQueries = 0;
        }
    }

    /**
     * Initializes remaining queries when null or forced
     * @param $number
     * @param bool $forceIfNotNull
     */
    protected static function setRemainingQueries($number, $forceIfNotNull=false){
        if(static::$remainingQueries === null || $forceIfNotNull){
            static::$remainingQueries = $number;
        }
    }

    /**
     * Initializes used queries when 0 or forced
     * @param $number
     * @param bool $forceIfNotZero
     */
    protected static function setUsedQueries($number, $forceIfNotZero=false){
        if(static::$usedQueries == 0 || $forceIfNotZero){
            static::$usedQueries = $number;
        }
    }

    /**
     * Initializes the timestamp when the quota resets
     * @param $timestamp
     * @param bool $forceIfNotNull
     */
    protected static function setQuotaResetTimestamp($timestamp, $forceIfNotNull=false){
        if(static::$quotaResetTimestamp === null || $forceIfNotNull){
            static::$quotaResetTimestamp = $timestamp;
        }
    }


    /**
     * Increments the used queries (default 1) and checks wether the quota will be exceeded, or needs to be reset.
     * @param int $increment
     * @throws Exception\InterfaceQuotaExceededException
     * @internal param $number
     * @internal param bool $forceIfNotZero
     */
    protected static function incrementUsedQueries($increment=1){
//        static::checkResetQueryQuota();
        static::$usedQueries += $increment;
        if(static::$remainingQueries !== null){
            $newRemainder = static::$remainingQueries - $increment;
            if($newRemainder < 0){
                throw new InterfaceQuotaExceededException('Quota (probably) exceeded for DataInterface : '. get_called_class() . ' used queries: '.static::$usedQueries);
            }

        }
    }

    /**
     * Checks and optionally resets the quota variables.
     * Abstract only, since every interface will define it based on own config
     */
//    protected abstract function checkResetQueryQuota();
}