<?php

declare(strict_types=1);

/**
 * 域名相关配置（静态资源、IM 等）
 * 对应 .env 中的 STATIC_DOMAIN、IM_DOMAIN、DEFAULT_HOME_SERVER
 */
return [
    'static_domain' => env('STATIC_DOMAIN', ''),
    'im_domain' => env('IM_DOMAIN', ''),
    /**
     * Matrix MXID 的 server 部分（须与 Synapse server_name 一致）。
     * web-api 注册/登录拼 user_id 时仅读此配置，忽略请求体中的 home_server。
     */
    'default_home_server' => env('DEFAULT_HOME_SERVER', env('IM_DOMAIN', 'im-sq01.bleiworc.xyz')),
];
