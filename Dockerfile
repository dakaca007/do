FROM ubuntu:22.04

# 设置非交互式环境
ENV DEBIAN_FRONTEND=noninteractive \
    TZ=Asia/Shanghai

# 预配置时区
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && \
    echo $TZ > /etc/timezone

# 修复 Firefox 安装问题
RUN apt update && \
    apt install -y --no-install-recommends \
    software-properties-common && \
    add-apt-repository -y ppa:mozillateam/ppa && \
    echo 'Package: firefox*' > /etc/apt/preferences.d/mozilla-firefox && \
    echo 'Pin: release o=LP-PPA-mozillateam' >> /etc/apt/preferences.d/mozilla-firefox && \
    echo 'Pin-Priority: 501' >> /etc/apt/preferences.d/mozilla-firefox && \
    apt install -y --no-install-recommends \
    firefox \
    x11vnc \
    xvfb \
    fluxbox \
    novnc \
    websockify \
    net-tools \
    tzdata \
    && rm -rf /var/lib/apt/lists/*

# 创建用户（密码建议改为更安全的）
RUN useradd -m -s /bin/bash remoteuser && \
    echo 'remoteuser:admin123' | chpasswd

# 修复密码文件权限
RUN mkdir -p /home/remoteuser/.vnc && \
    x11vnc -storepasswd "admin123" /home/remoteuser/.vnc/passwd && \
    chown -R remoteuser:remoteuser /home/remoteuser/.vnc && \
    chmod 600 /home/remoteuser/.vnc/passwd

COPY start.sh /start.sh
RUN chmod +x /start.sh

USER remoteuser
WORKDIR /home/remoteuser

EXPOSE 6080
CMD ["/start.sh"]
