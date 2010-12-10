<?php
/**
 * 
 * @author loki
 *
 */
abstract class LCache
{
    
    static $_container = array();
    
    const FILE = 'cache_file';
    
    const MEMCACHED = 'cache_memcached';
    
    const EXCEPTHON_CACHE_NO_KEY = '没有缓存key';
    
    const PREFIX = 'L';
    
    private function __construct() {
        
    }    
    
    /**
     * 
     * @param string $name
     * @return LCache
     */
    public static function getInstance($name='') {
        $cache = null;
        $cache_backend = array(self::FILE, self::MEMCACHED);
        if (empty($name)) {
            $cache = self::FILE;
        } else {
            $cache = $name;
        }
        if (!in_array($cache, $cache_backend)) {
            throw new Exception("没有名为{$name}的缓存适配器");
        } 
        $names = explode('_', $cache);
        $class_name = self::PREFIX.ucfirst($names[0]).ucfirst($names[1]);
        $key = md5($class_name);
        if (isset(self::$_container[$key])) {
            return self::$_container[$key];
        }
        self::$_container[$key] = $obj = new $class_name();
        return $obj;
    }
    /**
     * 根据key获取缓存内容
     * @param string $key
     */
    abstract public function get($key);
    /**
     * 设置缓存的key、内容以及过期时间
     * @param string $key
     * @param string $data
     * @param string $expires
     */
    abstract public function set($key, $data, $expires = '');
    /**
     * 测试这个key缓存是否有效
     * @param string $key
     */
    abstract public function test($key);
    /**
     * 刷新所有缓存
     */
    abstract public function flush();
}
?>