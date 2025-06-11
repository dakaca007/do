# 基于预配置好的桌面环境镜像
FROM dorowu/ubuntu-desktop-lxde-vnc:latest

# 更换为国内软件源以加速（可选）
RUN sed -i 's/archive.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list && \
    sed -i 's/security.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list && \
    rm -f /etc/apt/sources.list.d/google-chrome.list

# 添加缺失的公钥（如果需要使用 dl.google.com）
RUN apt-get update && apt-get install -y wget && \
    wget -q -O - https://dl.google.com/linux/linux_signing_key.pub | apt-key add -

# 更新系统和安装所需软件
RUN apt-get update && apt-get upgrade -y && \
    apt-get install -y wget && \
    wget https://golang.org/dl/go1.20.6.linux-amd64.tar.gz && \
    tar -C /usr/local -xzf go1.20.6.linux-amd64.tar.gz && \
    rm go1.20.6.linux-amd64.tar.gz && \
    echo "export PATH=\$PATH:/usr/local/go/bin" >> /etc/profile && \
    echo "export GOPATH=/root/go" >> /etc/profile && \
    echo "export PATH=\$PATH:\$GOPATH/bin" >> /etc/profile && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# 设置环境变量（确保 Go 可用）
ENV PATH="/usr/local/go/bin:$PATH"
ENV GOPATH="/root/go"

# 安装一些常用开发工具（可选）
RUN apt-get update && apt-get install -y git build-essential
# 替换APT为阿里云镜像源
RUN sed -i 's/archive.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list && \
    sed -i 's/security.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list

# 安装基础依赖
RUN apt update && DEBIAN_FRONTEND=noninteractive apt install -y \
    curl \
    bash \
    procps \
    ncurses-bin \
    openssl \
    nginx \
    python3 \
    python3-pip \
    vim \
    && rm -rf /var/lib/apt/lists/*

# 配置Nginx目录权限
RUN mkdir -p /var/log/nginx /var/lib/nginx /var/www/html/php \
    && chown -R www-data:www-data /var/log/nginx /var/lib/nginx /var/www/html \
    && chmod 755 /var/log/nginx /var/lib/nginx

# 下载并安装GoTTY（保持原地址）
RUN curl -LO https://github.com/yudai/gotty/releases/download/v1.0.1/gotty_linux_amd64.tar.gz \
    && tar zxvf gotty_linux_amd64.tar.gz \
    && mv gotty /usr/local/bin/ \
    && chmod +x /usr/local/bin/gotty \
    && rm gotty_linux_amd64.tar.gz

# 安装PHP-FPM及相关扩展
RUN apt update && DEBIAN_FRONTEND=noninteractive apt install -y \
    php8.1-fpm \
    php8.1-mysql \
    php8.1-odbc \
    php8.1-pdo \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html/php
RUN mkdir -p /var/www/uploads

# PHP运行环境配置
RUN mkdir -p /run/php && chown www-data:www-data /run/php
RUN echo "<?php phpinfo(); ?>" > /var/www/html/php/info.php \
    && echo "<?php echo 'Hello from PHP test!'; ?>" > /var/www/html/php/test.php \
    && chown -R www-data:www-data /var/www/html/php \
    && chmod 755 /var/www/html/php/*.php
COPY ./myphp /var/www/html/php

# 配置Nginx
COPY nginx.conf /etc/nginx/sites-available/default

# 使用阿里云PyPI镜像安装Python依赖
COPY ./flaskapp/requirements.txt /tmp/requirements.txt
RUN python3 -m pip install --no-cache-dir -r /tmp/requirements.txt \
    -i https://mirrors.aliyun.com/pypi/simple/ \
    --trusted-host mirrors.aliyun.com \
    && rm /tmp/requirements.txt

# 部署Flask应用
COPY ./flaskapp /var/www/html/flaskapp
RUN mkdir -p /var/www/html/flaskapp/static/uploads \
    && chown -R www-data:www-data /var/www/html/flaskapp/static \
    && chmod 755 /var/www/html/flaskapp

# 配置非root用户
RUN useradd -m appuser \
    && apt update && apt install -y sudo \
    && usermod -aG sudo appuser \
    && echo 'appuser ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers \
    && openssl req -x509 -newkey rsa:4096 -nodes -days 365 \
      -subj "/CN=localhost" \
      -keyout /home/appuser/.gotty.key \
      -out /home/appuser/.gotty.crt \
    && chown appuser:appuser /home/appuser/.gotty.*

# 配置启动脚本
COPY start.sh /start.sh
RUN chown appuser:appuser /start.sh && chmod +x /start.sh

# 暴露端口
EXPOSE 80
# 默认启动命令（继承原镜像的 VNC 服务）
CMD ["/start.sh"]
