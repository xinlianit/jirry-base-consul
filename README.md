# Consul - Consul for PHP

PHP Consul 助手

## Installation

Install Jirry/Consul

```bash
$ composer require Jirry/Consul
```

## Basic Usage

### 单元测试
```sybase
$ php tests/ServerTest --command=${command}
```

### Composer 引用
composer.json 文件 "repositories" 段增加 "jirry"
```json
{
  ...
  "repositories": {
    "packagist": {
      "type": "composer",
      "url": "https://packagist.laravel-china.org"
    },
    "jirry": {
      "type": "vcs",
      "url": "https://github.com/xinlianit/jirry-consul.git"
    }
  }
}
```
安装 jirry/consul
```sybase
$ php composer require jirry/consul
```

## command 参数

- services 获取服务列表
- service 获取服务信息
- register 服务注册
- deregister 服务注销

## host Consul 主机地址
例：--host=http://localhost:8500

## service 服务配置
```json
{
    "id": "user.services.jirry.com",
    "name": "user.services.jirry.com",
    "address": "192.168.134.215",
    "port": 60000,
    "tags": [
        "user"
    ],
    "checks": [
        {
            "http": "http://192.168.134.215:60000",
            "interval": "5s"
        }
    ] 
}
```

## service-id 服务ID
例：--service-id=user.services.jirry.com