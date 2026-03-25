#!/bin/bash
php_bin='/usr/bin/php8.1'
alias php=$php_bin
mode='dev'
if [ -z "$1" ]; then
    mode="dev"
elif [ "$1" == "prod" ]; then
    mode="prod"
else
    mode="dev"
fi
app_port=`cat .env| grep APP_PORT | awk '{ print $3 }'`
echo "app_port: $app_port"

# 杀死进程
function kill_process() {
    for p in `lsof -i:${app_port} | awk 'NR > 1  { print $2 }'`; do
        echo "Killing process: $p"
        kill $p
    done
}
echo "kill_process ..."
kill_process

# 启动进程
echo "mode: $mode, start_process ..."
if [ "$1" == "dev" ]; then
    #$php_bin ./bin/hyperf.php start
    $php_bin bin/hyperf.php server:watch
else
    nohup $php_bin ./bin/hyperf.php start > ./run.log 2>&1 &
    echo "进程已启动，PID: $!"
fi
