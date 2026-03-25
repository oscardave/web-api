CREATE TABLE `order_setting` (
      `id` int unsigned NOT NULL AUTO_INCREMENT,
      `min` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
      `max` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
      `rate` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
      `day` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
      PRIMARY KEY (`id`)
) ENGINE=InnoDB ;

insert order_setting(min,max,rate,day) values(100,499,1,1);
insert order_setting(min,max,rate,day) values(500,999,10,7);
insert order_setting(min,max,rate,day) values(1000,1999,30,15);
insert order_setting(min,max,rate,day) values(2000,4999,60,30);
insert order_setting(min,max,rate,day) values(5000,9999,130,66);
insert order_setting(min,max,rate,day) values(10000,19999,200,99);
insert order_setting(min,max,rate,day) values(20000,49999,250,99);
insert order_setting(min,max,rate,day) values(50000,99999,310,180);


CREATE TABLE `invite_setting` (
     `id` int unsigned NOT NULL AUTO_INCREMENT,
     `rate` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
     `level` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
     `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
     `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
     PRIMARY KEY (`id`)
) ENGINE=InnoDB ;
insert into invite_setting(level,rate) values(1,15);
insert into invite_setting(level,rate) values(2,7);
insert into invite_setting(level,rate) values(3,3);
insert into invite_setting(level,rate) values(4,1);
insert into invite_setting(level,rate) values(5,1);

insert into `system`(`key`,value) values("sign_day","0.5");
insert into `system`(`key`,value) values("sign_seven","1");
insert into `system`(`key`,value) values("sign_thirty","3");

insert into `system`(`key`,value) values("platfrom_start","2025-01-17");



CREATE TABLE `turntable` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `rate` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
  `money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
  `level` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
  `image` varchar(100)  NOT NULL DEFAULT '' COMMENT '添加时间',
  `title` varchar(100)  NOT NULL DEFAULT '' COMMENT '添加时间',
  `status` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
  `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
  `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB ;

CREATE TABLE `turntable_log` (
     `id` int unsigned NOT NULL AUTO_INCREMENT,
     `money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
     `user_id` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
     `type` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
     `count` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
     `status` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
     `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
     `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
     PRIMARY KEY (`id`)
) ENGINE=InnoDB ;


CREATE TABLE `invite_reward` (
     `id` int unsigned NOT NULL AUTO_INCREMENT,
     `money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
     `num` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
     `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
     `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
     PRIMARY KEY (`id`)
) ENGINE=InnoDB ;

CREATE TABLE `invite_reward_log` (
         `id` int unsigned NOT NULL AUTO_INCREMENT,
         `money` decimal(10,2) NOT NULL DEFAULT 0 COMMENT '域名',
         `user_id` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
         `lower_id` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
         `created` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
         `updated` int  NOT NULL DEFAULT 0 COMMENT '添加时间',
         PRIMARY KEY (`id`)
) ENGINE=InnoDB ;