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

# 启动 Firefox（使用 ESR 版本）
firefox --no-remote --disable-gpu --disable-dev-shm-usage --disable-setuid-sandbox --disable-infobars about:blank &

# 修复 VNC 密码读取问题
sleep 3
x11vnc -storepasswd admin123 ~/.vnc/passwd  # 确保密码文件存在
x11vnc -forever -shared -passwd admin123 -display :0 -noxrecord -noxfixes -noxdamage &

# 启动 noVNC（前台运行）
websockify --web /usr/share/novnc 6080 localhost:5900
