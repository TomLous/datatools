<?php
/**
 * Created by PhpStorm.
 * User: tomlous
 * Date: 23/04/14
 * Time: 15:48
 */

namespace DataInterface;

use \Slim\Slim;

/**
 * Default interface for all custom crawler, api, validation classes
 *
 * Class DataInterface
 * @package DataInterface
 */
class DataInterface {

    /**
     * Refernce to the slim framework app
     * @var null|\Slim\Slim
     */
    private $slim;



    /**
     * General construct method, init all extended classes with base
     */
    function __construct(){
        // reference to slim framework
        $this->slim = Slim::getInstance();

        // load enviroment config from Slim
        $config = $this->slim->environment();

        // retrieve classnamespace for current object
        $classNamespace = get_class($this);

        // split namespace in parts
        $classNameParts = explode('\\',$classNamespace);

        // retrieve config from Slim based on nested params configed in Slim config
        foreach($classNameParts as $className){
            if((is_array($config) || is_object($config)) && isset($config[$className])){
                $config = $config[$className];
            }else{
                $config = null; // no config params for this instance
            }

        }

        // if the final config is found, set a property
        if(is_array($config)){
            foreach($config as $property=>$value){
                if(property_exists($this, $property)){
                    $this->$property = $value;
                }
            }
        }

    }
} 