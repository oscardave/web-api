#!/usr/bin/env bash

export MYSQL_PWD='Qndv4cUIH05ew0c8'
mysqldump --default-character-set=utf8 -u'debian-sys-maint' red_cattle > red_cattle.sql
