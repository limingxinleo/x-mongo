<?php
// +----------------------------------------------------------------------
// | Mongo.php [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 limingxinleo All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <https://github.com/limingxinleo>
// +----------------------------------------------------------------------
namespace Xin;

class Mongo
{
    protected static $_instance = [];
    protected $mongo;
    protected $manager;

    /**
     * Mongo constructor.
     * @param       $host
     * @param       $port
     * @param array $options
     *
     * @options boolean connect     表示是否在Mongo构造函数中建立连接
     * @options integer timeout     配置建立连接超时时间，单位是ms
     * @options string  db          覆盖$server字符串中的database段
     * @options string  username    覆盖$server字符串中的username段，如果username包含冒号:时，选用此种方式。
     * @options string  password    覆盖$server字符串中的password段，如果password包含符号@时，选用此种方式。
     * @options string  replicaSet  配置replicaSet名称
     */
    public function __construct($host, $port, $options = [])
    {
        $uri = "mongodb://{$host}:{$port}";
        $this->manager = new \MongoDB\Driver\Manager($uri, $options);
    }


    /**
     * 防止克隆
     */
    private function __clone()
    {
    }

    /**
     * @desc   获取Mongo单例
     * @author limx
     * @param string $host    地址
     * @param int    $port    端口号
     * @param array  $options 可选配置
     * @return Mongo
     */
    public static function getInstance($host, $port, $options = [])
    {
        $key = md5(json_encode([$host, $port, $options]));

        if (isset(static::$_instance[$key]) && static::$_instance[$key] instanceof RedisClient) {
            return static::$_instance[$key];
        }

        return static::$_instance[$key] = new static($host, $port, $options);
    }

    /**
     * @desc   返回实例数
     * @author limx
     * @return int
     */
    public static function count()
    {
        return count(static::$_instance);
    }
}
