# 基于预配置好的桌面环境镜像
FROM dorowu/ubuntu-desktop-lxde-vnc:latest

# 更换为国内软件源以加速（可选，根据需要修改）
RUN sed -i 's/archive.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list && \
    sed -i 's/security.ubuntu.com/mirrors.aliyun.com/g' /etc/apt/sources.list

# 更新系统和安装所需软件
RUN apt-get update && apt-get upgrade -y && \
    # 安装 PHP 和相关扩展
    apt-get install -y php php-cli php-curl php-mbstring php-xml php-zip && \
    # 安装 Python3 和 pip
    apt-get install -y python3 python3-pip && \
    # 安装 Go
    apt-get install -y wget && \
    wget https://golang.org/dl/go1.20.6.linux-amd64.tar.gz && \
    tar -C /usr/local -xzf go1.20.6.linux-amd64.tar.gz && \
    rm go1.20.6.linux-amd64.tar.gz && \
    # 配置 Go 环境变量
    echo "export PATH=\$PATH:/usr/local/go/bin" >> /etc/profile && \
    echo "export GOPATH=/root/go" >> /etc/profile && \
    echo "export PATH=\$PATH:\$GOPATH/bin" >> /etc/profile && \
    # 清理缓存
    apt-get clean && rm -rf /var/lib/apt/lists/*

# 设置环境变量（确保 Go 可用）
ENV PATH="/usr/local/go/bin:$PATH"
ENV GOPATH="/root/go"

# 安装一些常用开发工具（可选）
RUN apt-get update && apt-get install -y vim git build-essential

# 默认启动命令（继承原镜像的 VNC 服务）
CMD ["/startup.sh"]
