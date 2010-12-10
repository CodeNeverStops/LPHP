<?php
/**
 * 
 * @author loki
 *
 */
class LCacheFile extends LCache
{
    /**
     * 目录层级
     * @var int
     */
    protected $_cache_level = 2;
    /**
     * 缓存目录
     * @var string
     */
    protected $_cache_dir = '';
    /**
     * 缓存过期时间文件名后缀
     * @var string
     */
    protected $_cache_expired = '.expired';
    /**
     * 最大目录层级
     * @var unknown_type
     */
    const MAX_CACHE_LEVEL = 5;
    
    const EXCEPTION_CACHE_DIR_NOT_WRITEABLE = '目录不存在或者不可写';
    
    const EXCEPTION_CACHE_DIR_CREATE_FAIL = '创建目录失败';
    
    const EXCEPTION_CACHE_DIR_CHMOD_FAIL = '修改目录权限失败';
    
    const EXCEPTION_CACEH_FILE_READ_FAIL = '文件不存在或者不可读';
    /**
     * 
     * @param string $cache_dir
     * @param int $cache_level
     */
    public function __construct($cache_dir = '', $cache_level = '') {
        if (empty($cache_dir)) {
            $this->_cache_dir = rtrim(L::getApp()->config['basePath'], DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;
        } else {
            $this->_cache_dir = rtrim($cache_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        }
        if (!is_writable($this->_cache_dir)) {
            throw new Exception(self::EXCEPTION_CACHE_DIR_NOT_WRITEABLE);
        }
        if (is_numeric($cache_level) && $cache_level <= self::MAX_CACHE_LEVEL) {
            $this->_cache_level = $cache_level;
        }
    }
	/**
	 * 
	 */
	public function flush() {
		
	}
	/**
	 * @param string $key
	 */
	public function get($key) {
		if (empty($key)) {
		    throw new Exception(self::EXCEPTHON_CACHE_NO_KEY);
		}
		$key = md5($key);
		$file = $this->_getDirByKey($key).$key;
		if (!is_readable($file.$this->_cache_expired)) {
		    return false;
		}
		$expired = intval(file_get_contents($file.$this->_cache_expired));
		if ($expired !== 0 && $expired < time()) {
		    return false;
		}
		if (false === is_readable($file)) {
		    throw new Exception(self::EXCEPTION_CACEH_FILE_READ_FAIL);
		}
		$content = file_get_contents($file);
		return unserialize($content);
	}
	/**
	 * 测试缓存是否有效
	 * @param string $key
	 */
	public function test($key) {
		if (empty($key)) {
		    throw new Exception(self::EXCEPTHON_CACHE_NO_KEY);
		}
		$key = md5($key);
		$expired_file = $this->_getDirByKey($key).$key.$this->_cache_expired;
		if (!is_readable($expired_file)) {
		    return false;
		}
		$expired = intval(file_get_contents($expired_file));
		if ($expired !== 0 && $expired < time()) {
		    return false;
		}
		return true;
	}
    /**
     * @param string $key
     * @param mixed $data
     * @param int $expires
     */
	public function set($key, $data, $expires = '') {
		$key = md5($key);
		$file = $this->_getDirByKey($key).$key;
		$content = serialize($data);
		$time = time();
		file_put_contents($file, $content);
		if ($expires > 0) {
		    $time += $expires;
		} else {
		    $time = 0;
		}
		file_put_contents($file.$this->_cache_expired, $time);
	}
	/**
	 * 根据缓存key获取缓存内容存放目录
	 * @param string $key
	 */
	protected function _getDirByKey($key)
	{
	    if (empty($key)) {
	        throw new Exception(self::EXCEPTHON_CACHE_NO_KEY);
	    }
	    $cur_dir = $this->_cache_dir;
	    for ($i = 0; $i < $this->_cache_level; $i++) {
	        $dir = substr($key, $i*2, 2);
	        $cur_dir = rtrim($cur_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$dir.DIRECTORY_SEPARATOR;
	        if (is_writable($cur_dir)) {
	            continue;
	        }
	        if (file_exists($cur_dir)) {
	            if (false === chmod($cur_dir, 0777)) {
	                throw new Exception(self::EXCEPTION_CACHE_DIR_CHMOD_FAIL);
	            }
	            continue;
	        }
            if (false === mkdir($cur_dir, 0777)) {
                throw new Exception(self::EXCEPTION_CACHE_DIR_CREATE_FAIL);
            }
	    }
	    return $cur_dir;
	}
}
?>