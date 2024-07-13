FROM alpine:latest

# 安装 PHP 和 Nginx
RUN apk add --no-cache php81 php81-fpm nginx

# 复制 Nginx 配置文件
COPY nginx.conf /etc/nginx/nginx.conf

# 复制项目文件
COPY chat /var/www/html/

# 配置 PHP
RUN sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php81/php.ini

# 设置工作目录
WORKDIR /var/www/html

# 暴露端口
EXPOSE 80

# 启动 Nginx 和 PHP-FPM
CMD ["sh", "-c", "nginx; php-fpm81 -F"]
