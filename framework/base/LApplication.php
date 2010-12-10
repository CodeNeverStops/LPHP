<?php
/**
 * 
 * @author loki
 *
 */
class LApplication
{
    /**
     * 
     * @var array
     */
    public $config = null;
    /**
     * 
     * @param $config
     */
    private function __construct($config)
    {
        if (empty($config)) {
            throw new Exception("please create config file.");
        }
        if (!is_file($config)) {
            throw new Exception("can't find config file in path:{$config}");
        }
        $this->config = require_once $config;
        $this->parseConfig();
    }
    /**
     * 
     * @param $config
     */
    public static function getInstance($config = '')
    {
        static $_app = null;
        if (null === $_app) {
            $_app = new self($config);
        }
        return $_app;
    }
    /**
     * 
     */
    public function parseConfig()
    {
        if (empty($this->config)) {
            throw new Exception("there is no config");
        }
        if (!isset($this->config['basePath'])) {
            throw new Exception("please consider the application path.");
        }
    }
    /**
     * run the application
     */
    public static function run()
    {
        define('IS_CLI', php_sapi_name() == 'cli');
        $controller = IS_CLI?$_SERVER['argv'][1]:$_GET['c'];
        $action = IS_CLI?$_SERVER['argv'][2]:$_GET['a'];
        
        if (empty($controller)) {
            $controller = 'index';
        }
        
        if (empty($action)) {
            $action = 'index';
        }
        
        spl_autoload_register(function ($classname) {
            require_once "{$classname}.php";
        });
        
        $controller_name = ucfirst($controller).'Controller';
        if (!file_exists(rtrim(L::getApp()->config['basePath'], DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.$controller_name.'.php')) {
            throw new Exception("File: {$controller_name}.php is not existed!");
        }
        include "$controller_name.php";
        if (!class_exists($controller_name)) {
            throw new Exception("Controller: $controller_name is not existed!");
        }
        
        $action_name = 'action'.ucfirst($action);
        $controller_obj = new $controller_name();
        if (!method_exists($controller_obj, $action_name)) {
            throw new Exception("Action: $action_name is not existed!");
        }
        
        try {
            $controller_obj->$action_name();
        } catch (Exception $ex) {
            $msg = array(
                'File:'.$ex->getFile(),
                'Line:'.$ex->getLine(),
                'Error:'.$ex->getMessage(),
                'Trace:'.$ex->getTraceAsString()
            );
            if (defined("L_DEBUG") && L_DEBUG) {
                echo implode("<br/>", $msg);
            } else {
                
            }
        }
    }
}
?>