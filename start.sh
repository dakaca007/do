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

# 启动 Firefox（使用 firefox-esr）
firefox-esr --no-remote --disable-gpu --disable-dev
