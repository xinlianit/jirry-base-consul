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
     * 构造函数
     *
     * Service constructor.
     *
     * @param array $httpConfig Http请求配置
     * @param array $options    请求选项
     */
    protected function __construct(array $httpConfig = [], array $options = [])
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

    /**
     * 服务发现 - 单个服务多个记录
     *
     * @param string $serviceName 服务名称
     * @param int    $passing     是否过滤不健康服务; 0: 否、1: 是(默认)
     * @param string $option      服务选项(默认: 全部); node: 节点、service: 服务、checks: 健康检查
     * @param array  $fields      字段明细
     *
     * @return array
     */
    public function getHealthServices(string $serviceName, int $passing = 1, string $option = '', array $fields = [])
    {
        try {
            $restApi  = self::REST_HEALTH_SERVICE . '/' . $serviceName . '?passing=' . $passing;
            $response = self::$httpClient->get($restApi, self::$options);
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }

        // 响应数据体
        $body = json_decode((string)$response->getBody(), true);

        switch (strtolower($option)) {
            case 'node':
                $result = array_column($body, 'Node');
                break;
            case 'service':
                $result = array_column($body, 'Service');
                break;
            case 'checks':
                $result = array_column($body, 'Checks');
                break;
            default:
                $result = $body;
                break;
        }

        // 过滤非指定字段
        $resultDetail = [];
        $fields && array_walk($result, function ($row) use ($fields, &$resultDetail) {
            foreach ($row as $field => $value) {
                if (!in_array($field, $fields)) unset($row[$field]);
            }
            $resultDetail[] = $row;
        });

        return self::success($resultDetail ? $resultDetail : $result);
    }

    /**
     * 服务发现 - 单个服务随机选出单个记录
     *
     * @param string $serviceName 服务名称
     * @param int    $passing     是否过滤不健康服务; 0: 否、1: 是(默认)
     * @param string $option      服务选项(默认: 全部); node: 节点、service: 服务、checks: 健康检查
     * @param array  $fields      字段明细
     *
     * @return array
     */
    public function getHealthService(string $serviceName, int $passing = 1, string $option = '', array $fields = [])
    {
        // 单个服务，多个记录列表
        $services = $this->getHealthServices($serviceName, $passing, $option, $fields);

        if ($services['error'] || !$services['data']) return $services;

        $randIndex = mt_rand(0, count($services['data']) - 1);

        return self::success($services['data'][$randIndex]);
    }

}