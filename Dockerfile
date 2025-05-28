FROM ubuntu:22.04

# 安装所有依赖
RUN apt update && DEBIAN_FRONTEND=noninteractive apt install -y \
    curl \
    openssl \
    nginx \
    php8.1-fpm \
    php8.1-mysql \
    php8.1-odbc \
    php8.1-pdo \
    && rm -rf /var/lib/apt/lists/*

# 创建目录并设置权限
RUN mkdir -p /var/log/nginx /var/lib/nginx /var/www/html/php /run/php /var/www/uploads \
    && chown -R www-data:www-data /var/log/nginx /var/lib/nginx /var/www /run/php \
    && chmod 755 /var/log/nginx /var/lib/nginx

# 安装 GoTTY
RUN curl -sL https://github.com/yudai/gotty/releases/download/v1.0.1/gotty_linux_amd64.tar.gz | tar xz \
    && mv gotty /usr/local/bin/ \
    && chmod +x /usr/local/bin/gotty

# 创建 PHP 测试文件
RUN mkdir -p /var/www/html/php \
    && echo "<?php phpinfo(); ?>" > /var/www/html/php/info.php \
    && echo "<?php echo 'Hello from PHP test!'; ?>" > /var/www/html/php/test.php \
    && chown -R www-data:www-data /var/www/html/php \
    && chmod 755 /var/www/html/php/*.php

# 创建非 root 用户并生成证书
RUN useradd -m appuser \
    && openssl req -x509 -newkey ec:<(openssl ecparam -name prime256v1) \
        -nodes -days 365 -subj "/CN=localhost" \
        -keyout /home/appuser/.gotty.key -out /home/appuser/.gotty.crt \
    && chown appuser:appuser /home/appuser/.gotty.*

# 复制配置文件和启动脚本
COPY ./myphp /var/www/html/php
COPY nginx.conf /etc/nginx/sites-available/default
COPY start.sh /start.sh
RUN chown appuser:appuser /start.sh && chmod +x /start.sh

# 设置用户并暴露端口
USER appuser
EXPOSE 80

# 启动服务
CMD ["/start.sh"]