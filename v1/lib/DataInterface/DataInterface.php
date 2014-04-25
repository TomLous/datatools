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
use Slim\Slim;

/**
 * Default interface for all custom crawler, api, validation classes
 *
 * Class DataInterface
 * @package DataInterface
 */
class DataInterface
{

    /**
     * Refernce to the slim framework app
     * @var null|Slim
     */
    private $slim;


    /**
     * General construct method, init all extended classes with base
     */
    function __construct()
    {
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
                if (property_exists($this, $property)) {
                    $this->$property = $value;
                }
            }
        }

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
        $route = $app->post('/' . $path . '/:api/:endpoint', function ($api, $endpoint) use ($app) {

            // Set basic return data for the API
            $data = array();
            $data['success'] = false;
            $data['Meta'] = array();
            $data['Meta']['requestUri'] = $app->request()->getResourceUri();
            $data['Meta']['requestData'] = $app->request()->params();
            $data['Meta']['ip'] = $app->request()->getIp();

            try {
                $className = '\\DataInterface\\' . $api;

                if (!class_exists($className)) {
                    throw new IncompatibleInputException('Non existing DataInterface API: `' . $api . '`');
                }

                $apiInstance = new $className();

                if (!method_exists($apiInstance, $endpoint)) {
                    throw new IncompatibleInputException('Non existing DataInterface endpoint `' . $endpoint . '` for API `' . $api . '`');
                }

                // call the method on the class and map its output to data object
                $data = array_merge_recursive($apiInstance->$endpoint($app->request()->params()), $data);
                $data['success'] = true;

                if ($app->config('debug')) {
                    $app->getLog()->debug($data);
                }


            } catch (IncompatibleInputException $e) { // User error (wrong entry)
                $data['message'] = $e->getMessage();
                $data['errorSource'] = 'User';
                $app->getLog()->notice($data);

            } catch (IncompatibleInterfaceException $e) { // API error (connection failed)
                $data['message'] = $e->getMessage();
                $data['errorSource'] = 'API';
                $app->getLog()->warn($data + array('trace' => $e->getTrace()));

            } catch (\Exception $e) { // Catch all other exceptions
                $data['message'] = $e->getMessage();
                $data['errorSource'] = 'unknown';
                $app->getLog()->warn($data + array('trace' => $e->getTrace()));

            } catch (Exception $e) { // Catch all other exceptions
                $data['message'] = $e->getMessage();
                $data['errorSource'] = 'unknown';
                $app->getLog()->warn($data + array('trace' => $e->getTrace()));
            }

            // Always output JSON
            header("Content-Type: application/json");
            print json_encode($data);

        });
        return $route;
    }
} 