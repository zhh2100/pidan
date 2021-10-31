<?php
declare (strict_types = 1);

namespace pidan\cache;

use pidan\Cache;
/**
 * Redis缓存驱动 
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 * @category   Extend
 * @package  Extend
 * @subpackage  Driver.Cache
 */
class Redis implements Cache{

    /**
     * 操作句柄
     * @var string
     * @access protected
     */
    protected $handler;

    protected $app;

    /**
     * 缓存连接参数
     * @var integer
     * @access protected
     */
    protected $options = ['type'=>'','expire'=>null,'prefix'=>''];
	 /**
	 * 架构函数
	 * @param array $options 缓存参数 
	 * @access public
	 */
	public function __construct($options=array(),$handler=null) {
		$this->app=app();
		$this->options = array_merge($this->options,app('config')->get('cache'),$options);
		$this->handler  = $handler ?? redis();
	}
	public static function __make()
	{
		return new static();
	}
	/**
	 * 通用读取缓存
	 * @access public
	 * @param string $name 缓存变量名
	 * @return mixed  无数据返回 false
	 */
	public function get($name) {
		$this->app->N('cache_read',1);
		$value = $this->handler->get($this->options['prefix'].$name);
		$jsonData  = json_decode((string) $value, true );
		return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
	}
	/**
	 * 通用写入缓存
	 * @access public
	 * @param string $name 缓存变量名
	 * @param mixed $value  存储数据
	 * @param integer $expire  有效时间（秒） 必须为整形  <=0  直接过期
	 * @return boolen
	 */
	public function set($name, $value, $expire = null) {
		$this->app->N('cache_write',1);
		$name   =   $this->options['prefix'].$name;
		//对数组/对象数据进行缓存处理，保证数据完整性
		$value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
		$result = $this->handler->set($name, $value);
		
		if(is_null($expire)) {
			$expire  =  $this->options['expire'];
		}
		if($result && is_int($expire)) {
			$this->handler->expire($name, $expire);
		}		
		// if($result && $this->options['length']>0) {
		// 	// 记录缓存队列
		// 	$this->queue($name);
		// }
		return $result;
	}



	/**
	 * 删除缓存
	 * @access public
	 * @param string $name 缓存变量名
	 * @return boolen
	 */
	public function rm($name) {
		$this->app->N('cache_write',1);
		return $this->handler->delete($this->options['prefix'].$name);
	}

	/**
	 * 清除缓存  还有session用redis，所以没分开前失效
	 * @access public
	 * @return boolen
	
	public function clear() {
		return $this->handler->flushDB();
	}
	*/

}