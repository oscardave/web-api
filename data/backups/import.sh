#!/bin/bash

# 数据库配置
DB_HOST="127.0.0.1"
DB_PORT="5432"
DB_DATABASE="synapse_sq01"
DB_USERNAME="postgres"
DB_PASSWORD="qwe123QWE"

# 备份目录
BACKUP_DIR="$(cd "$(dirname "$0")" && pwd)"

# 设置环境变量
export PGPASSWORD="${DB_PASSWORD}"

# 检查参数
if [ $# -eq 0 ]; then
    echo "用法: $0 <备份文件.tar.gz>"
    echo ""
    echo "可用的备份文件:"
    ls -lh "${BACKUP_DIR}"/*.tar.gz 2>/dev/null | awk '{print "  " $9 " (" $5 ")"}'
    exit 1
fi

BACKUP_FILE="$1"

# 检查备份文件是否存在
if [ ! -f "${BACKUP_FILE}" ]; then
    # 如果不是绝对路径，尝试在备份目录中查找
    if [ ! -f "${BACKUP_DIR}/${BACKUP_FILE}" ]; then
        echo "错误: 备份文件不存在: ${BACKUP_FILE}"
        exit 1
    else
        BACKUP_FILE="${BACKUP_DIR}/${BACKUP_FILE}"
    fi
fi

# 检查文件扩展名
if [[ ! "${BACKUP_FILE}" =~ \.tar\.gz$ ]]; then
    echo "错误: 备份文件必须是 .tar.gz 格式"
    exit 1
fi

# 临时解压目录
TEMP_DIR=$(mktemp -d)
SQL_FILE="${TEMP_DIR}/$(basename "${BACKUP_FILE}" .tar.gz).sql"

echo "开始导入数据库 ${DB_DATABASE}..."
echo "备份文件: ${BACKUP_FILE}"

# 解压备份文件
echo "正在解压备份文件..."
tar -xzf "${BACKUP_FILE}" -C "${TEMP_DIR}"

if [ $? -ne 0 ]; then
    echo "解压失败！"
    rm -rf "${TEMP_DIR}"
    exit 1
fi

# 查找解压后的SQL文件
SQL_FILE=$(find "${TEMP_DIR}" -name "*.sql" -type f | head -n 1)

if [ -z "${SQL_FILE}" ] || [ ! -f "${SQL_FILE}" ]; then
    echo "错误: 在备份文件中未找到SQL文件"
    rm -rf "${TEMP_DIR}"
    exit 1
fi

echo "找到SQL文件: ${SQL_FILE}"

# 确认操作
echo ""
echo "警告: 此操作将覆盖数据库 ${DB_DATABASE} 的所有数据！"
read -p "确认继续? (yes/no): " CONFIRM

if [ "${CONFIRM}" != "yes" ]; then
    echo "操作已取消"
    rm -rf "${TEMP_DIR}"
    exit 0
fi

# 导入数据库
echo "正在导入数据库..."
psql -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USERNAME}" -d "${DB_DATABASE}" -f "${SQL_FILE}"

if [ $? -eq 0 ]; then
    echo ""
    echo "数据库导入成功！"
else
    echo ""
    echo "数据库导入失败！"
    rm -rf "${TEMP_DIR}"
    exit 1
fi

# 清理临时文件
rm -rf "${TEMP_DIR}"

# 清除密码环境变量
unset PGPASSWORD
