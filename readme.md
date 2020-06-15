# 通用后台管理服务端

接口对应文档地址： 

<https://www.showdoc.cc/868693074265637?page_id=4687471993853335>

## composer 国内镜像

[阿里云composer镜像站](https://developer.aliyun.com/composer)

## 安装步骤

    git clone https://github.com/guozhenyi/lumen-admin-service.git

复制配置文件

    php -r "copy('.env.example', '.env');"

在根目录执行以下命令获得随机值，填充到.env的APP_KEY字段

    php artisan key:generate

安装依赖包

    composer install --prefer-dist

如果要更新Lumen的安全修复小版本，可继续执行

    composer update --prefer-dist



