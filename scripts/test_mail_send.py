#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
邮件发送测试脚本：使用与 web-api 相同的 SMTP 配置发送一封验证码测试邮件。
配置从环境变量读取（与 web-api .env 中的 MAIL_* 一致），不写数据库、不依赖 PHP。

用法：
  cd web-api && python scripts/test_mail_send.py
  # 或指定收件人
  MAIL_HOST=smtp.example.com MAIL_USERNAME=... MAIL_PASSWORD=... python scripts/test_mail_send.py your@email.com

  cd /home/an/work/synapse/web-api &&
  python scripts/test_mail_send.py
  # 指定收件人
  python scripts/test_mail_send.py your@example.com
  # 不依赖 .env，完全用环境变量
  MAIL_HOST=smtpdm.aliyun.com MAIL_USERNAME=shequan@shequanmail.cc MAIL_PASSWORD=你的密码 python scripts/test_mail_send.py your@example.com
"""

import os
import sys
import smtplib
from email.mime.text import MIMEText
from email.utils import formataddr

# 从环境变量读取，与 config/autoload/mail.php 保持一致
def get_config():
    return {
        "host": os.environ.get("MAIL_HOST", "smtpdm.aliyun.com"),
        "port": int(os.environ.get("MAIL_PORT", "465")),
        "encryption": (os.environ.get("MAIL_ENCRYPTION", "ssl") or "ssl").lower(),
        "username": os.environ.get("MAIL_USERNAME", "shequan@shequanmail.cc"),
        "password": os.environ.get("MAIL_PASSWORD", ""),
        "from_address": os.environ.get("MAIL_FROM_ADDRESS") or os.environ.get("MAIL_USERNAME", "shequan@shequanmail.cc"),
        "from_name": os.environ.get("MAIL_FROM_NAME", "验证码"),
    }


def load_dotenv_if_exists():
    """若存在 .env 则加载，便于在 web-api 目录下直接运行（仅加载 MAIL_ 开头的变量）"""
    env_path = os.path.join(os.path.dirname(__file__), "..", ".env")
    if not os.path.isfile(env_path):
        return
    with open(env_path, "r", encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith("#"):
                continue
            if "=" in line:
                k, v = line.split("=", 1)
                k = k.strip()
                v = v.strip()
                if v.startswith(('"', "'")):
                    v = v[1:-1].replace("\\n", "\n")
                if k and k not in os.environ:
                    os.environ[k] = v


def send_test_email(to_email: str, config: dict) -> None:
    subject = "验证码通知"
    body = "您的验证码是 123456，5分钟内有效，请勿泄露给他人。（本邮件为配置测试，非真实验证码）"

    msg = MIMEText(body, "plain", "utf-8")
    msg["Subject"] = subject
    msg["From"] = formataddr((config["from_name"], config["from_address"]))
    msg["To"] = to_email

    use_ssl = config["encryption"] == "ssl"
    port = config["port"]
    host = config["host"]

    if use_ssl:
        server = smtplib.SMTP_SSL(host, port)
    else:
        server = smtplib.SMTP(host, port)
        server.starttls()

    try:
        server.login(config["username"], config["password"])
        server.sendmail(config["from_address"], [to_email], msg.as_string())
        print("发送成功：邮件已发至", to_email)
    finally:
        server.quit()


def main():
    load_dotenv_if_exists()
    config = get_config()

    if not config["password"]:
        print("错误：未设置 MAIL_PASSWORD（可在 .env 或环境变量中配置）", file=sys.stderr)
        sys.exit(1)
    if not config["host"] or not config["username"]:
        print("错误：MAIL_HOST / MAIL_USERNAME 未配置", file=sys.stderr)
        sys.exit(1)

    print("config:", config)

    to_email = (sys.argv[1] if len(sys.argv) > 1 else config["username"]).strip()
    if not to_email or "@" not in to_email:
        print("用法：python test_mail_send.py [收件人邮箱]", file=sys.stderr)
        print("未指定收件人时将使用 MAIL_USERNAME 作为收件人。", file=sys.stderr)
        sys.exit(1)

    print("配置：", config["host"], ":", config["port"], config["encryption"], "发件人:", config["from_address"])
    print("收件人：", to_email)
    try:
        send_test_email(to_email, config)
    except Exception as e:
        print("发送失败：", e, file=sys.stderr)
        sys.exit(1)


if __name__ == "__main__":
    main()
