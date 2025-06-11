FROM ubuntu:22.04

# 设置非交互式环境
ENV DEBIAN_FRONTEND=noninteractive \
    TZ=Asia/Shanghai

# 预配置时区
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && \
    echo $TZ > /etc/timezone

# 安装依赖（使用firefox-esr替代firefox）
RUN apt update && apt install -y --no-install-recommends \
    firefox-esr \
    x11vnc \
    xvfb \
    fluxbox \
    novnc \
    websockify \
    net-tools \
    tzdata \
    && rm -rf /var/lib/apt/lists/*

# 创建用户
RUN useradd -m -s /bin/bash remoteuser && \
    echo 'remoteuser:admin123' | chpasswd

# 配置 VNC 密码（注意：使用 remoteuser 用户运行 storepasswd 以避免权限问题）
RUN mkdir -p /home/remoteuser/.vnc && \
    chown -R remoteuser:remoteuser /home/remoteuser/.vnc && \
    # 使用 su 以 remoteuser 身份运行 storepasswd，避免权限问题
    su - remoteuser -c "x11vnc -storepasswd admin123 /home/remoteuser/.vnc/passwd"

# 复制启动脚本并设置权限
COPY start.sh /start.sh
RUN chmod +x /start.sh

USER remoteuser
WORKDIR /home/remoteuser

EXPOSE 6080
CMD ["/start.sh"]
