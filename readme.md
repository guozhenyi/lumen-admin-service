# 单篇管理服务端

## 安装步骤

复制配置文件

    php -r "copy('.env.example', '.env');"

在根目录执行以下命令获得随机值，填充到.env的APP_KEY字段

    php artisan key:generate

如果要更新Lumen的安全修复小版本，可执行

    composer update --prefer-dist


## composer题外话

如果composer官方包下载慢，可以切换成国内镜像

https://pkg.phpcomposer.com/

