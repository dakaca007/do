FROM ubuntu:22.04

# 安装基础依赖
RUN apt update && DEBIAN_FRONTEND=noninteractive apt install -y \
    curl \
    bash \
    procps \
    ncurses-bin \
    openssl \
    nginx \
    vim \
    && rm -rf /var/lib/apt/lists/*

 # 配置Nginx目录权限（关键步骤）
RUN mkdir -p /var/log/nginx /var/lib/nginx /var/www/html/php \
    && chown -R www-data:www-data /var/log/nginx /var/lib/nginx /var/www/html \
    && chmod 755 /var/log/nginx /var/lib/nginx

# 安装指定版本 PHP-FPM
RUN apt update && DEBIAN_FRONTEND=noninteractive apt install -y \
    php8.1-fpm \  
    php8.1-mysql \
    php8.1-odbc \
    php8.1-pdo \
    && rm -rf /var/lib/apt/lists/*
WORKDIR /var/www/html/php
# 创建 PHP-FPM 运行时目录
RUN mkdir -p /run/php && chown www-data:www-data /run/php
# 创建 PHP 测试文件和目录（添加 index.php）
RUN mkdir -p /var/www/html/php \
    && echo "<?php phpinfo(); ?>" > /var/www/html/php/info.php \
    && echo "<?php echo 'Hello from PHP test!'; ?>" > /var/www/html/php/test.php \
    && chown -R www-data:www-data /var/www/html/php \
    && chmod 755 /var/www/html/php/*.php
COPY ./myphp /var/www/html/php
 

# 复制Nginx配置文件
COPY nginx.conf /etc/nginx/sites-available/default


# 复制启动脚本并设置权限（在切换用户前完成）
COPY start.sh /start.sh
RUN chmod +x /start.sh
USER root

# 暴露端口
EXPOSE 80

# 启动服务
CMD ["/start.sh"]