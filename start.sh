# start.sh
#!/bin/bash

# 启动虚拟显示
Xvfb :0 -screen 0 1280x720x16 &
export DISPLAY=:0

# 启动窗口管理器
fluxbox &

# 启动 Firefox（禁用更新和沙箱）
firefox --no-remote --disable-gpu --disable-dev-shm-usage &

# 启动 VNC 服务器
x11vnc -forever -shared -passwd 'vncpassword' -display :0 &

# 启动 noVNC
websockify --web /usr/share/novnc 6080 localhost:5900