#!/bin/bash

# 启动虚拟显示
Xvfb :0 -screen 0 ${DISPLAY_WIDTH:-1280}x${DISPLAY_HEIGHT:-720}x16 -ac +extension GLX +render -noreset &
export DISPLAY=:0

# 等待 X 服务器初始化完成
for i in {1..10}; do
  if xdpyinfo >/dev/null 2>&1; then
    break
  fi
  echo "等待 Xvfb 启动 ($i)..."
  sleep 1
done

# 启动窗口管理器
fluxbox -log /dev/null &

# 启动 Firefox（优化性能）
firefox --no-remote --disable-gpu --disable-dev-shm-usage --disable-setuid-sandbox --disable-infobars about:blank &

# 启动 VNC 服务器（关键修复）
x11vnc -forever -shared -passwd "$(cat /home/remoteuser/.vnc/passwd)" -display :0 -noxrecord -noxfixes -noxdamage -localhost -rfbport 5900 &

# 确保 VNC 服务器启动
sleep 3

# 检查 VNC 端口是否监听
netstat -tulpn | grep 5900 || echo "警告：VNC 端口未监听"

# 启动 noVNC（前台运行保持容器存活）
websockify --web /usr/share/novnc 6080 localhost:5900
