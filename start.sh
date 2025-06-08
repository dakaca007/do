#!/bin/bash

# 启动虚拟显示
Xvfb :0 -screen 0 ${DISPLAY_WIDTH:-1280}x${DISPLAY_HEIGHT:-720}x16 -ac +extension GLX +render -noreset &
export DISPLAY=:0

# 启动窗口管理器
fluxbox -log /dev/null &

# 启动 Firefox（优化性能）
firefox --no-remote --disable-gpu --disable-dev-shm-usage --disable-setuid-sandbox --disable-infobars about:blank &

# 启动 VNC 服务器
x11vnc -forever -shared -passwd "$(cat /home/remoteuser/.vnc/passwd | tail -1)" -display :0 -noxrecord -noxfixes -noxdamage &

# 启动 noVNC
websockify --web /usr/share/novnc 6080 localhost:5900