<?php
/**
 * 
 * @author loki
 *
 */
class LController
{
    const URL_RAW = 0;
    const URL_PATHINFO = 1;
    const URL_REWRITE = 2;
    /**
     * 赋予模板变量
     * @param unknown_type $c_a
     * @param unknown_type $params
     */
    protected function display($c_a, $params = array())
    {
        if (empty($c_a)) {
            throw new Exception("Please specify the LController and action.");
        }
        $controller = $action = '';
        if (false === strpos($c_a, '/')) {
            throw new Exception('Please specify the LController/action view.');
        }
        $list = explode('/', $c_a);
        $controller = $list[0];
        $action = $list[1];

        $view_path = VIEW_PATH.strtolower($controller).DIRECTORY_SEPARATOR.strtolower($action).'.php';
        if (!file_exists($view_path)) {
            throw new Exception("view: $controller/$action is not found.");
        }
        if (is_array($params) && count($params) > 0) {
            extract($params);
        }
        include $view_path;
    }
    /**
     * 根据配置文件创建相应形式的url
     * @param unknown_type $c_a
     * @param unknown_type $params
     */
    protected function createUrl($c_a, $params = array())
    {
        if (empty($c_a)) {
            throw new Exception("Please specify the LController and action.");
        }
        $controller = $action = '';
        if (false === strpos($c_a, '/')) {
            throw new Exception('Please specify the LController/action view.');
        }
        $list = explode('/', $c_a);
        $controller = $list[0];
        $action = $list[1];
        $url = '';
        switch ($GLOBALS['config']['url_format']) {
            case self::URL_PATHINFO:
                $url = "index.php/$controller/$action";
                break;
            case self::URL_REWRITE:
                $url = "$controller/$action";
                break;
            default:
                $url = "index.php?c=$controller&a=$action";
                break;
        }
        foreach ($params as $key=>$value) {
            if (empty($key)) {
                throw new Exception("Please specify the key of the value $value.");
            }
            switch ($GLOBALS['config']['url_format']) {
	            case self::URL_PATHINFO:
	            case self::URL_REWRITE:
	                $url .= "/$key/$value";
	                break;
	            default:
	                $url .= "&$key=$value";
	                break;
            }
        }
        return $url;
    }
    
}
?>