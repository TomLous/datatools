<?php
/**
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2014 Tom Lous
 * @package     package
 * Datetime:     26/05/14 10:24
 */

namespace Tool;

use Slim\Slim;

abstract class Tool {

    /**
     * Refernce to the slim framework app
     * @var null|Slim
     */
    private $slim;

    private $tool;
    private $action;

    /**
     * @var bool meta variable to set bound state for class
     */
    protected static $_bound = false;


    /**
     * General construct method, init all extended classes with base
     */
    function __construct($tool, $action)
    {

        $this->tool = $tool;
        $this->action = $action;

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
    public static function createSlimToolRoutes(Slim $app, $path = 'tools')
    {
        $routes = array();
        $routes[] = $app->get('/' . $path . '/:action/:tool/', function ($action, $tool) use ($app) {
            // Set basic return data for the API
            $data = array();

            $toolClassName =  $tool . ucfirst($action);

            try {
                $className = '\\Tool\\' . $tool . '\\' . $toolClassName;

                if (!class_exists($className)) {
                    throw new \RuntimeException('Non existing Tool : `' . $action . '\\'. $toolClassName . '`');
                }

                $toolInstance = new $className($tool, $action);


                $toolInstance->renderToolPage();


            } catch (\Exception $e) { // Catch all other exceptions
                $app->getLog()->warn(print_r($data + array('trace' => $e->getTrace()), true));
                print $e->getMessage();

            } catch (Exception $e) { // Catch all other exceptions
                $app->getLog()->warn(print_r($data + array('trace' => $e->getTrace()), true));
                print $e->getMessage();
            }


        })->name('toolActionGet');

        $routes[] = $app->post('/' . $path . '/:action/:tool/', function ($action, $tool) use ($app) {
            // Set basic return data for the API
            $data = array();

            $toolClassName =  $tool . ucfirst($action);

            try {
                $className = '\\Tool\\' . $tool . '\\' . $toolClassName;

                if (!class_exists($className)) {
                    throw new \RuntimeException('Non existing Tool : `' . $action . '\\'. $toolClassName . '`');
                }

                $toolInstance = new $className($tool, $action);


                $toolInstance->handleToolSubmit();


            } catch (\Exception $e) { // Catch all other exceptions
                $app->getLog()->warn(print_r($data + array('trace' => $e->getTrace()), true));
                print $e->getMessage();

            } catch (Exception $e) { // Catch all other exceptions
                $app->getLog()->warn(print_r($data + array('trace' => $e->getTrace()), true));
                print $e->getMessage();
            }


        })->name('toolActionPost');
        return $routes;
    }

    /**
     * Breaks static references for inheretid classes and makes late static binding work as expected
     * @see: http://stackoverflow.com/questions/5513484/php-static-variables-in-an-abstract-parent-class-question-is-in-the-sample-code
     */
    private function _setLateStaticBinding(){
        if(!static::$_bound){
            // Workaround
            $tmp = 'x';
//            static::$_bound = &$tmp;
//            static::$_bound = true;

        }
    }

    protected  function renderToolPage(){
        $toolTemplate =  $this->action . DIRECTORY_SEPARATOR . $this->tool . DIRECTORY_SEPARATOR . 'tool.php';


        $this->slim->render($toolTemplate, array());

    }

    protected abstract function handleToolSubmit();

    protected function fetchFileInfo($inputName){
        if(!isset($_FILES) || !isset($_FILES[$inputName])){
            throw new \Exception('Missing file upload for '.$inputName);
        }

        $uploadedFileInfo = $_FILES[$inputName];

        if(in_array($uploadedFileInfo['error'], array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE))){
            throw new \Exception('File exceeded allowed size', $uploadedFileInfo['error']);
        }

        if(in_array($uploadedFileInfo['error'], array(UPLOAD_ERR_PARTIAL, UPLOAD_ERR_NO_FILE))){
            throw new \Exception('File missing or partially uploaded', $uploadedFileInfo['error']);
        }

        if(in_array($uploadedFileInfo['error'], array(UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_EXTENSION))){
            throw new \Exception('File handling invalid server side', $uploadedFileInfo['error']);
        }

        return $uploadedFileInfo;
    }


} 