<?php
/**
 * Consul 实体类
 *
 * @package  Jirry
 * @author   jirry <jirenyoucn@gmail.com>
 * @datetime 2019/6/28 11:01
 */

namespace Jirry;

use GuzzleHttp\Client;

class Consul
{
    /**
     * 服务列表
     */
    const REST_SERVICES = 'v1/agent/services';

    /**
     * 服务信息
     */
    const REST_SERVICE = 'v1/agent/service';

    /**
     * 服务注册
     */
    const REST_REGISTER = 'v1/agent/service/register';

    /**
     * 服务注销
     */
    const REST_DEREGISTER = 'v1/agent/service/deregister';

    /**
     * Http 客户端
     *
     * @var Client
     */
    protected static $httpClient;

    /**
     * Consul constructor.
     *
     * @param array $httpConfig
     */
    public function __construct(array $httpConfig = [])
    {
        self::$httpClient = new Client($httpConfig);
    }

    /**
     * 服务注册数据包
     *
     * @param string $id      服务ID
     * @param string $name    服务名称
     * @param string $address 服务地址
     * @param int    $port    服务端口
     * @param array  $tags    服务标签
     * @param array  $checks  服务健康检查
     * @param bool   $json    是否json编码; true: 是、false: 否
     *
     * @return string
     */
    public function servicePackage(string $id, string $name, string $address, int $port = 80, array $tags = [], array $checks = [], bool $json = true)
    {
        $package = [
            'id'      => $id,
            'name'    => $name,
            'address' => $address,
            'port'    => $port,
            'tags'    => $tags,
            'checks'  => $checks
        ];
        return $json ? json_encode($package) : $package;
    }

    /**
     * 服务健康检查数据包 - HTTP
     *
     * @param string $id       check id
     * @param string $name     check 名称
     * @param string $http     check 地址
     * @param int    $interval check 频率(单位: s秒)
     * @param int    $timeout  check 超时(单位: s秒)
     *
     * @return array
     */
    public function checkHttpPackage(string $id, string $name, string $http, int $interval = 10, int $timeout = 3)
    {
        return [
            'id'       => $id,
            'name'     => $name,
            'http'     => $http,
            'interval' => $interval . 's',
            'timeout'  => $timeout . 's'
        ];
    }

    /**
     * 服务健康检查数据包 - TCP
     *
     * @param string $id       check id
     * @param string $name     check 名称
     * @param string $tcp      check 地址
     * @param int    $interval check 频率(单位: s秒)
     * @param int    $timeout  check 超时(单位: s秒)
     *
     * @return array
     */
    public function checkTcpPackage(string $id, string $name, string $tcp, int $interval = 10, int $timeout = 3)
    {
        return [
            'id'       => $id,
            'name'     => $name,
            'tcp'      => $tcp,
            'interval' => $interval . 's',
            'timeout'  => $timeout . 's'
        ];
    }

    /**
     * 服务健康检查数据包 - TTL
     *
     * @param string $id    check id
     * @param string $name  check 名称
     * @param string $notes check 备注
     * @param int    $ttl   check 频率(单位: s秒)
     *
     * @return array
     */
    public function checkTtlPackage(string $id, string $name, string $notes, int $ttl = 30)
    {
        return [
            'id'    => $id,
            'name'  => $name,
            'notes' => $notes,
            'ttl'   => $ttl . 's'
        ];
    }

    /**
     * 服务健康检查数据包 - Script
     *
     * @param string $id       check id
     * @param string $name     check 名称
     * @param string $script   check 脚本
     * @param int    $interval check 频率(单位: s秒)
     * @param int    $timeout  check 超时(单位: s秒)
     *
     * @return array
     */
    public function checkScriptPackage(string $id, string $name, string $script, int $interval = 10, int $timeout = 3)
    {
        return [
            'id'       => $id,
            'name'     => $name,
            'script'   => $script,
            'interval' => $interval . 's',
            'timeout'  => $timeout . 's'
        ];
    }

    /**
     * 请求成功
     *
     * @param string|array $data    数据
     * @param null         $message 信息
     *
     * @return array
     */
    public static function success($data, $message = null)
    {
        return ['error' => false, 'msg' => $message, 'data' => $data];
    }

    /**
     * 请求失败
     *
     * @param string|array $data    数据
     * @param null         $message 信息
     *
     * @return array
     */
    public static function error($message = null, $data = null)
    {
        return ['error' => true, 'msg' => $message, 'data' => $data];
    }
}
