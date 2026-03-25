#!/bin/bash

# 数据库配置
DB_HOST="127.0.0.1"
DB_PORT="5432"
DB_DATABASE="synapse_sq01"
DB_USERNAME="postgres"
DB_PASSWORD="qwe123QWE"

# 备份目录
BACKUP_DIR="$(cd "$(dirname "$0")" && pwd)"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/${DB_DATABASE}_${TIMESTAMP}.tar.gz"
SQL_FILE="${BACKUP_DIR}/${DB_DATABASE}_${TIMESTAMP}.sql"

# 设置环境变量
export PGPASSWORD="${DB_PASSWORD}"

# 导出数据库
echo "开始导出数据库 ${DB_DATABASE}..."
pg_dump -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USERNAME}" -d "${DB_DATABASE}" -F p -f "${SQL_FILE}"

if [ $? -eq 0 ]; then
    echo "数据库导出成功: ${SQL_FILE}"
    
    # 压缩SQL文件
    echo "正在压缩备份文件..."
    tar -czf "${BACKUP_FILE}" -C "${BACKUP_DIR}" "$(basename "${SQL_FILE}")"
    
    if [ $? -eq 0 ]; then
        echo "备份文件压缩成功: ${BACKUP_FILE}"
        # 删除临时SQL文件
        rm -f "${SQL_FILE}"
        echo "临时SQL文件已删除"
        
        # 显示备份文件信息
        echo ""
        echo "备份完成！"
        echo "备份文件: ${BACKUP_FILE}"
        echo "文件大小: $(du -h "${BACKUP_FILE}" | cut -f1)"
    else
        echo "压缩失败！"
        exit 1
    fi
else
    echo "数据库导出失败！"
    exit 1
fi

# 清除密码环境变量
unset PGPASSWORD
