# Dockerfile
FROM ubuntu:22.04

RUN apt update && apt install -y \
    firefox \
    x11vnc \
    xvfb \
    fluxbox \
    novnc \
    websockify \
    net-tools

# 创建用户
RUN useradd -m -s /bin/bash remoteuser && \
    echo 'remoteuser:password123' | chpasswd

# 配置 VNC 和 noVNC
RUN mkdir -p /home/remoteuser/.vnc && \
    x11vnc -storepasswd "vncpassword" /home/remoteuser/.vnc/passwd

COPY start.sh /start.sh
RUN chmod +x /start.sh

USER remoteuser
WORKDIR /home/remoteuser

EXPOSE 6080
CMD ["/start.sh"]