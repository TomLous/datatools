<?php
/**
 * @author      Tom Lous <tomlous@gmail.com>
 * @copyright   2014 Tom Lous
 * @package     package
 * Datetime:     26/05/14 10:24
 */

namespace Tool;

use Slim\Slim;
use Tool\KBOOpenData\KBOOpenDataImport;

abstract class Tool
{

    /**
     * Refernce to the slim framework app
     * @var null|Slim
     */
    protected $slim;

    private $tool;
    private $action;
    private $logs;
    private $errors;

    private $filesToDelete;

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
        $this->logs = array();
        $this->errors = array();
        $this->filesToDelete = array();

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
    }

    /**
     * General destructor method
     */
    function __destruct()
    {
        $this->cleanUpFiles();
    }


        protected function log($message, $level = \Slim\Log::INFO, $data = array())
    {
        $data['message'] = $message;
        $this->logs[] = $message;
        $this->slim->getLog()->log($level, print_r($data, true));
    }

    protected function error($message, $level = \Slim\Log::ERROR, $data = array())
    {
        $data['message'] = $message;
        $this->errors[] = $message;
        $this->slim->getLog()->log($level, print_r($data, true));
    }

    protected function getErrors()
    {
        return $this->errors;
    }

    protected function getLogs()
    {
        return $this->logs;
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

            $toolClassName = $tool . ucfirst($action);

            try {
                $className = '\\Tool\\' . $tool . '\\' . $toolClassName;

                if (!class_exists($className)) {
                    throw new \RuntimeException('Non existing Tool : `' . $action . '\\' . $toolClassName . '`');
                }

                $toolInstance = new $className($tool, $action);

                $requestAction = $app->request->get('action');
                $renderToolPage = true;

                if($requestAction && method_exists($toolInstance, $requestAction)){
                    $parameters = $app->request->get();
                    unset($parameters['action']);

                    $renderToolPage = call_user_func_array(array($toolInstance, $requestAction), $parameters);
                }


                if($renderToolPage){
                    $toolInstance->renderToolPage();
                }


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

            $toolClassName = $tool . ucfirst($action);

            try {
                $className = '\\Tool\\' . $tool . '\\' . $toolClassName;

                if (!class_exists($className)) {
                    throw new \RuntimeException('Non existing Tool : `' . $action . '\\' . $toolClassName . '`');
                }

                $toolInstance = new $className($tool, $action);


                $toolInstance->handleToolSubmit($app->request->post());


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
    private function _setLateStaticBinding()
    {
        if (!static::$_bound) {
            // Workaround
            $tmp = 'x';
//            static::$_bound = &$tmp;
//            static::$_bound = true;

        }
    }

    protected function renderToolPage()
    {
        $toolTemplate = $this->action . DIRECTORY_SEPARATOR . $this->tool . DIRECTORY_SEPARATOR . 'tool.php';


        $this->slim->render($toolTemplate, array());

    }

    protected abstract function handleToolSubmit();

    protected function fetchFilesInfo($inputName)
    {
        if (!isset($_FILES) || !isset($_FILES[$inputName])) {
            throw new \Exception('Missing file upload for ' . $inputName);
        }

        $files = $_FILES[$inputName];

        if(!is_array($files['name'])){
            $newStructure = array(
                'name'=>array($files['name']),
                'type'=>array($files['type']),
                'tmp_name'=>array($files['tmp_name']),
                'error'=>array($files['error']),
                'size'=>array($files['size']),
            );
            $files = $newStructure;
        }

        $numFiles  = count($files['name']);

        $uploadedFiles = array();


        for($n=0;$n<$numFiles;$n++){

            if (in_array($files['error'][$n], array(UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE))) {
                foreach($files['tmp_name'] as $tmpName){
                    $this->markFilePathForDeletion($tmpName);
                }
                throw new \Exception('File '.$files['name'][$n].' exceeded allowed size', $files['error'][$n]);
            }

            if (in_array($files['error'][$n], array(UPLOAD_ERR_PARTIAL, UPLOAD_ERR_NO_FILE))) {
                foreach($files['tmp_name'] as $tmpName){
                    $this->markFilePathForDeletion($tmpName);
                }
                throw new \Exception('File  '.$files['name'][$n].' missing or partially uploaded', $files['error'][$n]);
            }

            if (in_array($files['error'][$n], array(UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_EXTENSION))) {
                foreach($files['tmp_name'] as $tmpName){
                    $this->markFilePathForDeletion($tmpName);
                }
                throw new \Exception('File  '.$files['name'][$n].' handling invalid server side', $files['error'][$n]);
            }

            $uploadedFiles[$n] = array(
                'name' => $files['name'][$n],
                'tmp_name' => $files['tmp_name'][$n],
                'type' => $files['type'][$n],
                'error' => $files['error'][$n],
                'size' => $files['size'][$n],
            );
        }

        return $uploadedFiles;
    }

    protected function markFilePathForDeletion($pathOrArray){
        if(is_array($pathOrArray)){
            foreach($pathOrArray as $path){
                $this->filesToDelete[$path] = $path;
            }
        }else{
            $this->filesToDelete[$pathOrArray] = $pathOrArray;
        }

    }

    /**
     * clean up by deleting files
     */
    protected  function cleanUpFiles()
    {
        foreach ($this->filesToDelete as $filePath) {
            @unlink($filePath);
        }
    }


} 