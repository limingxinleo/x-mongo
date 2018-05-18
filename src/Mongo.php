<?php
// +----------------------------------------------------------------------
// | Mongo.php [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 limingxinleo All rights reserved.
// +----------------------------------------------------------------------
// | Author: limx <715557344@qq.com> <https://github.com/limingxinleo>
// +----------------------------------------------------------------------
namespace Xin;

use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Query;
use MongoDB\Driver\WriteConcern;
use MongoDB\Driver\BulkWrite;

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
     * @desc   查询
     * @author limx
     * @param string $namespace A fully qualified namespace (databaseName.collectionName)
     * @param array  $filter
     * @param array  $options
     * @return array(obj,obj)
     *
     *  $filter = ['id' => ['$gt' => 1]];
     *  $options = [
     *      'projection' => ['_id' => 0],
     *      'sort' => ['id' => -1],
     *      'limit' => 1,
     *  ];
     */
    public function query($namespace, $filter = [], $options = [])
    {
        $query = new Query($filter, $options);
        $cursor = $this->manager->executeQuery($namespace, $query);

        return $cursor->toArray();
    }

    /**
     * @desc   插入一条数据
     * @author limx
     * @param string $namespace A fully qualified namespace (databaseName.collectionName)
     * @param array  $document
     * @return \MongoDB\Driver\WriteResult;
     */
    public function insert($namespace, $document)
    {
        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 1000);
        $bulk = new BulkWrite();
        $bulk->insert($document);
        return $this->manager->executeBulkWrite($namespace, $bulk, $writeConcern);
    }

    /**
     * @desc   批量插入数据
     * @author limx
     * @param string $namespace A fully qualified namespace (databaseName.collectionName)
     * @param array  $documents
     * @return \MongoDB\Driver\WriteResult
     */
    public function insertMany($namespace, $documents)
    {
        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 1000);
        $bulk = new BulkWrite();
        foreach ($documents as $doc) {
            $bulk->insert($doc);
        }
        return $this->manager->executeBulkWrite($namespace, $bulk, $writeConcern);
    }

    /**
     * @desc   更新数据
     * @author limx
     * @param string $namespace A fully qualified namespace (databaseName.collectionName)
     * @param array  $filter
     * @param array  $newObj
     * @param array  $updateOptions
     * @return \MongoDB\Driver\WriteResult;
     *
     *  $document = ['name' => uniqid()];
     *  $filter = ['id' => 999];
     *  $options = [
     *      'upsert' => true,
     *      'multi' => true,
     *  ];
     */
    public function update($namespace, $filter, $newObj, array $updateOptions = [])
    {
        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 1000);
        $bulk = new BulkWrite();
        $bulk->update($filter, ['$set' => $newObj], $updateOptions);
        return $this->manager->executeBulkWrite($namespace, $bulk, $writeConcern);
    }

    /**
     * @desc   删除文档
     * @author limx
     * @param string $namespace A fully qualified namespace (databaseName.collectionName)
     * @param array  $filter
     * @param array  $deleteOptions
     * @return \MongoDB\Driver\WriteResult;
     */
    public function delete($namespace, $filter, array $deleteOptions = [])
    {
        $writeConcern = new WriteConcern(WriteConcern::MAJORITY, 1000);
        $bulk = new BulkWrite();
        $bulk->delete($filter, $deleteOptions);
        return $this->manager->executeBulkWrite($namespace, $bulk, $writeConcern);
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
