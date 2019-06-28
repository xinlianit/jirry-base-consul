<?php
/**
 * Service 服务注册发现
 *
 * @package  Jirry\Consul
 * @author   jirry <jirenyoucn@gmail.com>
 * @datetime 2019/6/28 11:01
 */

namespace Jirry\Consul;

use Jirry\Consul;

class Service extends Consul
{
    /**
     * 内核实例
     *
     * @var object
     */
    private static $_instance = null;

    /**
     * 请求选项
     *
     * @var array
     */
    private static $options = [];

    /**
     * 构造函数，私有，阻止外部创建对象
     *
     * Service constructor.
     *
     * @param array $httpConfig Http请求配置
     * @param array $options    请求选项
     */
    private function __construct(array $httpConfig = [], array $options = [])
    {
        parent::__construct($httpConfig);
        self::$options = $options;
    }

    /**
     * 客户端实例
     *
     * @param array $httpConfig Http请求配置
     * @param array $options    请求选项
     *
     * @return Service|object
     */
    public static function client(array $httpConfig = [], array $options = [])
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($httpConfig, $options);
        }

        return self::$_instance;
    }

    /**
     * 阻止外部克隆对象
     */
    public function __clone()
    {
        trigger_error('Clone is not allow', E_USER_WARNING);
    }

    /**
     * 获取服务列表
     *
     * @return array
     */
    public function getServices()
    {
        try {
            $response = self::$httpClient->get(self::REST_SERVICES, self::$options);
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }

        return self::success(json_decode((string)$response->getBody(), true));
    }

    /**
     * 获取服务信息
     *
     * @param string $serviceId 服务ID
     *
     * @return array
     */
    public function getService(string $serviceId)
    {
        try {
            $response = self::$httpClient->get(self::REST_SERVICE . '/' . $serviceId, self::$options);
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }

        return self::success(json_decode((string)$response->getBody(), true));
    }

    /**
     * 服务注册
     *
     * @param string|array $service 服务信息
     *
     * @return array
     */
    public function registerService($service = [])
    {
        self::$options['body'] = is_array($service) ? json_encode($service) : $service;

        try {
            $response = self::$httpClient->put(self::REST_REGISTER, self::$options);
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }

        return self::success(json_decode((string)$response->getBody(), true));
    }

    /**
     * 服务注销
     *
     * @param string $serviceId 服务ID
     *
     * @return array
     */
    public function deregisterService(string $serviceId)
    {
        try {
            $response = self::$httpClient->put(self::REST_DEREGISTER . '/' . $serviceId, self::$options);
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }

        return self::success(json_decode((string)$response->getBody(), true));
    }

}