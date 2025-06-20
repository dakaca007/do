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
    apt-get install -y php php-fpm php-cli php-curl php-mbstring php-xml php-zip && \
    apt-get install -y wget && \
    apt-get clean && rm -rf /var/lib/apt/lists/*



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

WORKDIR /var/www/html/php
RUN mkdir -p /var/www/uploads

# PHP运行环境配置
RUN mkdir -p /run/php && chown www-data:www-data /run/php
RUN echo "<?php phpinfo(); ?>" > /var/www/html/php/info.php \
    && echo "<?php echo 'Hello from PHP test!'; ?>" > /var/www/html/php/test.php \
    && chown -R www-data:www-data /var/www/html/php \
    && chmod 755 /var/www/html/php/*.php


# 配置Nginx
COPY nginx.conf /etc/nginx/sites-available/default

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
