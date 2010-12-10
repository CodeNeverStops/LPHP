<?php
/**
 * 
 * @author loki
 *
 */
class L
{
    /**
     * 
     * @var LApplication
     */
    protected static $_app = null;
    /**
     * 
     * @param string $config
     */
    public static function createApp($config)
    {
        include 'base/LApplication.php';
        include 'base/LController.php';
        include 'base/LDB.php';
        include 'base/LModel.php';

        self::$_app = LApplication::getInstance($config);
        
        set_include_path(implode(PATH_SEPARATOR, array(
            get_include_path(),
            rtrim(self::$_app->config['basePath'], '/').'/controllers',
            rtrim(self::$_app->config['basePath'], '/').'/components',
            rtrim(self::$_app->config['basePath'], '/').'/models'
        )));
        return self::getApp();
    }
    /**
     * @return LApplication
     */
    public static function getApp()
    {
        return LApplication::getInstance();
    }
}

?>