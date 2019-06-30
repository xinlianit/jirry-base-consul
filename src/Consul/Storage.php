<?php
/**
 * Storage Key/Value 存储
 *
 * @package  Jirry\Consul
 * @author   jirry <jirenyoucn@gmail.com>
 * @datetime 2019/6/30 0:08
 */

namespace Jirry\Consul;

use Jirry\Consul;

class Storage extends Consul
{
    /**
     * 内核实例
     *
     * @var object
     */
    private static $_instance = null;

    /**
     * 构造函数
     *
     * Service constructor.
     *
     * @param array $httpConfig Http请求配置
     */
    protected function __construct(array $httpConfig = [])
    {
        parent::__construct($httpConfig);
    }

    /**
     * 阻止外部克隆对象
     */
    public function __clone()
    {
        trigger_error('Clone is not allow', E_USER_WARNING);
    }

    /**
     * Storage 实例
     *
     * @param array $httpConfig Http请求配置
     *
     * @return Service|object
     */
    public static function connect(array $httpConfig = [])
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($httpConfig);
        }

        return self::$_instance;
    }

    /**
     * 获取所有 Key/Value
     *
     * @return array
     */
    public function getAllKeys()
    {
        $restApi = self::REST_STORAGE_KEY_ALL;
        $this->dc && $restApi .= '&dc=' . $this->dc;
        try {
            $response = self::$httpClient->get($restApi, self::$options);
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }

        $body = json_decode((string)$response->getBody(), true);

        return $body ? $body : [];
    }

    /**
     * 获取名称空间所有 Key/Value
     *
     * @param string $namespace 名称空间
     *
     * @return array
     */
    public function getNameSpaceKeys(string $namespace)
    {
        $restApi = self::REST_STORAGE_KEY_NAMESPACE . '/' . $namespace . '/?recurse';
        $this->dc && $restApi .= '&dc=' . $this->dc;
        try {
            $response = self::$httpClient->get($restApi, self::$options);
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }

        $body = json_decode((string)$response->getBody(), true);

        return $body ? $body : [];
    }

    /**
     * 获取 Key 明细
     *
     * @param string $key Key名
     *
     * @return array|bool
     */
    public function getKeyDetaild(string $key)
    {
        $restApi = self::REST_STORAGE_KEY . '/' . $key;
        $this->dc && $restApi .= '?dc=' . $this->dc;
        try {
            $response = self::$httpClient->get($restApi, self::$options);
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }

        $body = json_decode((string)$response->getBody(), true);

        return $body ? $body[0] : false;
    }

    /**
     * 获取所有 Key 名
     *
     * @param string|null $namespace 名称空间
     * @param string|null $separator key 定界符
     *
     * @return array
     */
    public function getAllKeyNames(string $namespace = null, string $separator = null)
    {
        $restApi = self::REST_STORAGE_KEY_NAMES;
        $namespace && $restApi .= '/' . $namespace;
        $restApi .= '/?keys';
        $separator && $restApi .= '&separator=' . $separator;
        $this->dc && $restApi .= '&dc=' . $this->dc;
        try {
            $response = self::$httpClient->get($restApi, self::$options);
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }

        $body = json_decode((string)$response->getBody(), true);

        return $body ? $body : [];
    }

    /**
     * 获取 Key 值
     *
     * @param string                 $key     Key名
     * @param bool|null|string|array $default 默认值
     *
     * @return bool|null|string|array
     */
    public function getKey(string $key, $default = false)
    {
        // Key 明细
        $keyDetaild = $this->getKeyDetaild($key);

        if (!$keyDetaild || isset($keyDetaild['error'])) return $default;

        return base64_decode($keyDetaild['Value']);
    }

    /**
     * 设置 Key
     *
     * @param string      $key       Key名
     * @param null        $value     Value值
     * @param string|null $namespace 名称空间
     *
     * @return array|bool
     */
    public function setKey(string $key, $value = null, string $namespace = null)
    {
        $restApi = self::REST_STORAGE_KEY_SET;
        $keyName = '';
        $namespace && $keyName .= '/' . $namespace;
        $key && $keyName .= '/' . $key;
        $this->dc && $restApi .= $keyName . '?dc=' . $this->dc;

        // 获取Key值
        $valueDetaild = $this->getKeyDetaild($keyName);

        if ($valueDetaild && (!isset($valueDetaild['error']) || !$valueDetaild['error'])) {
            return self::error("Key already exists");
        }

        // 设置Key值
        try {
            self::$options['body'] = $value;
            $response              = self::$httpClient->put($restApi, self::$options);
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }

        $body = json_decode((string)$response->getBody(), true);

        return $body ? true : false;
    }

    /**
     * 更新 Key
     *
     * @param string      $key         Key名
     * @param null        $value       Value值
     * @param int         $modifyIndex 修改索引，乐观锁，防止并发修改
     * @param string|null $namespace   名称空间
     *
     * @return array|bool
     */
    public function updateKey(string $key, $value = null, $modifyIndex = 0, string $namespace = null)
    {
        $restApi = self::REST_STORAGE_KEY_SET;
        $keyName = '';
        $namespace && $keyName .= '/' . $namespace;
        $key && $keyName .= '/' . $key;
        $this->dc && $restApi .= $keyName . '?dc=' . $this->dc;
        $unionSymbol = $this->dc ? '&' : '?';
        $modifyIndex && $restApi .= $unionSymbol . 'cas=' . $modifyIndex;

        // 获取Key值
        $valueDetaild = $this->getKeyDetaild($keyName);

        if (!$valueDetaild || (isset($valueDetaild['error']) && $valueDetaild['error'])) {
            return self::error("Key does not exist");
        }

        // 设置Key值
        try {
            self::$options['body'] = $value;
            $response              = self::$httpClient->put($restApi, self::$options);
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }

        $body = json_decode((string)$response->getBody(), true);

        return $body ? true : false;
    }

    /**
     * 删除单个 Key
     *
     * @param string      $key       Key 名
     * @param string|null $namespace 名称空间
     *
     * @return array|bool
     */
    public function deleteKey(string $key, string $namespace = null)
    {
        $restApi = self::REST_STORAGE_KEY_DELETE;
        $namespace && $restApi .= '/' . $namespace;
        $key && $restApi .= '/' . $key;
        $this->dc && $restApi .= '?dc=' . $this->dc;

        // 删除 Key
        try {
            $response = self::$httpClient->delete($restApi, self::$options);
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }

        $body = json_decode((string)$response->getBody(), true);

        return $body ? true : false;
    }

    /**
     * 删除多个 Key
     *
     * @param string|null $namespace 名称空间
     *
     * @return array|bool
     */
    public function deleteKeys(string $namespace = null)
    {
        $restApi = self::REST_STORAGE_KEY_DELETE;
        $namespace && $restApi .= '/' . $namespace;
        $restApi .= '?recurse';
        $this->dc && $restApi .= '&dc=' . $this->dc;

        // 删除 Key
        try {
            $response = self::$httpClient->delete($restApi, self::$options);
        } catch (\Exception $e) {
            return self::error($e->getMessage());
        }

        $body = json_decode((string)$response->getBody(), true);

        return $body ? true : false;
    }
}