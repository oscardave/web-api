<?php

// 目录位置
const TMPL_PATH = '/Users/ai/work/sports/admin/src/controllers';
// const TMPL_PATH = '/Users/ai/work/sports/admin/templates';
const TMPL_SPLT = 'controllers';
// const TMPL_SPLT = '/templates';
const REG = '/[\x{4e00}-\x{9fa5}]+/u';
const APP_KEY = '4f5ec2bc77675643';
const APP_SECRET = 'CRLvL9IYU9JSAWSLE1sDUirBSx4WJLUL';
const APP_URL = 'https://openapi.youdao.com/api';
// const API_URL = 'https://openapi.youdao.com/api';
const APP_TIMEOUT = 2000;
const LANG_VI = 'vi';
const LANG_TH = 'th';
const LANG_EN = 'en';
const LANG_ZH = 'zh-CHS';
const LANG_TW = 'zh-TWS';
const CURL_TIMEOUT = 3000;
// const VIEW_PATH = '/Users/ai/work/sports/admin/languages/views'; // 视图文件语言文件
const VIEW_PATH = '/Users/ai/work/sports/admin/languages/controllers'; // 视图文件语言文件

const CHIN_PATH = '/Users/ai/work/sports/admin/languages/content_words'; // 控制器文件位置
const TRAN_PATH = '/Users/ai/work/sports/admin/languages/contents'; // 控制器文件位置

require 'ZhCovert.php';

// 所有文件列表
function get_files(string $path, array &$result)
{
    $files = scandir($path);
    foreach ($files as $f) {
        if ($f == '.' || $f == '..') {
            continue;
        }
        $real_path = $path . '/' . $f;
        if (is_dir($real_path)) { // 如果是目录, 则递归
            get_files($real_path, $result);
            continue;
        }
        $result[] = $real_path;
    }
}

// 提到所有中文
function get_words(string $path): array
{
    $wArr = [];
    $content = file_get_contents($path);
    preg_match_all(REG, $content, $wArr);
    return $wArr;
}


// 所送请求
function do_request($q, string $code_from, string $code_to)
{
    $salt = create_guid();
    $args = [
        'q' => $q,
        'appKey' => APP_KEY,
        'salt' => $salt,
    ];
    $args['from'] = $code_from;
    $args['to'] = $code_to;
    $args['signType'] = 'v3';
    $current_time = strtotime("now");
    $args['curtime'] = $current_time; // 时间
    $signStr = APP_KEY . truncate($q) . $salt . $current_time . APP_SECRET; //
    $args['sign'] = hash("sha256", $signStr); // 加密
    // $args['vocabId'] = '您的用户词表ID'; // 一般用不上
    return call(APP_URL, $args);
}

// 发起网络请求
function call($url, $args = null, $method = "post", $testflag = 0, $timeout = CURL_TIMEOUT, $headers = array())
{
    $ret = false;
    $i = 0;
    while ($ret === false) {
        if ($i > 1)
            break;
        if ($i > 0) {
            sleep(1);
        }
        $ret = callOnce($url, $args, $method, false, $timeout, $headers);
        $i++;
    }
    return $ret;
}

function callOnce($url, $args = null, $method = "post", $withCookie = false, $timeout = CURL_TIMEOUT, $headers = array())
{
    $ch = curl_init();
    if ($method == "post") {
        $data = convert($args);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_POST, 1);
    } else {
        $data = convert($args);
        if ($data) {
            if (stripos($url, "?") > 0) {
                $url .= "&$data";
            } else {
                $url .= "?$data";
            }
        }
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    if ($withCookie) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);
    }
    $r = curl_exec($ch);
    curl_close($ch);
    return $r;
}

function convert(&$args)
{
    $data = '';
    if (is_array($args)) {
        foreach ($args as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $k => $v) {
                    $data .= $key . '[' . $k . ']=' . rawurlencode($v) . '&';
                }
            } else {
                $data .= "$key=" . rawurlencode($val) . "&";
            }
        }
        return trim($data, "&");
    }
    return $args;
}

// uuid generator
function create_guid()
{
    $microTime = microtime();
    list($a_dec, $a_sec) = explode(" ", $microTime);
    $dec_hex = dechex($a_dec * 1000000);
    $sec_hex = dechex($a_sec);
    ensure_length($dec_hex, 5);
    ensure_length($sec_hex, 6);
    $guid = "";
    $guid .= $dec_hex;
    $guid .= create_guid_section(3);
    $guid .= '-';
    $guid .= create_guid_section(4);
    $guid .= '-';
    $guid .= create_guid_section(4);
    $guid .= '-';
    $guid .= create_guid_section(4);
    $guid .= '-';
    $guid .= $sec_hex;
    $guid .= create_guid_section(6);
    return $guid;
}

function create_guid_section($characters)
{
    $return = "";
    for ($i = 0; $i < $characters; $i++) {
        $return .= dechex(mt_rand(0, 15));
    }
    return $return;
}

//
function truncate(string $q)
{
    $len = abslength($q);
    return $len <= 20 ? $q : (mb_substr($q, 0, 10) . $len . mb_substr($q, $len - 10, $len));
}

//
function abslength($str)
{
    if (empty($str)) {
        return 0;
    }
    if (function_exists('mb_strlen')) {
        return mb_strlen($str, 'utf-8');
    } else {
        preg_match_all("/./u", $str, $ar);
        return count($ar[0]);
    }
}

//
function ensure_length(string &$string, int $length)
{
    $str_len = strlen($string);
    if ($str_len < $length) {
        $string = str_pad($string, $length, "0");
        return;
    }
    if ($str_len > $length) {
        $string = substr($string, 0, $length);
    }
}

//
function get_trans(string $word, string $from_code, string $to_code)
{
    if ($to_code == LANG_TW) {
        return ZhConvert::zh_hans_to_zh_hant($word);
    }

    $result_content = do_request($word, $from_code, $to_code);
    $result = json_decode($result_content, true);
    if ($result['errorCode'] != 0) {
        echo "翻译文本失败: $word\n";
        return;
    }
    return $result['translation'][0] ?? '###翻译错误###';
}

function get_controller_name(string $file): string
{
    $rArr = explode('/', trim($file, '/'));
    if (count($rArr) < 2) {
        return 'index';
    }
    return $rArr[0];
}

function get_action_name(string $file): string
{
    $rArr = explode('/', trim($file, '/'));
    if (count($rArr) < 2) {
        return '';
    }
    return $rArr[1];
}

function get_trans_all(string $word)
{
    return [
        LANG_ZH => $word,
        LANG_EN => get_trans($word, LANG_ZH, LANG_EN),
        LANG_TH => get_trans($word, LANG_ZH, LANG_TH),
        LANG_VI => get_trans($word, LANG_ZH, LANG_VI),
        LANG_TW => get_trans($word, LANG_ZH, LANG_TW),
    ];
}

function get_template_files()
{
    $template_files = []; // 模板文件
    get_files(TMPL_PATH, $template_files);

    // $max = 2;
    $file_count = count($template_files);
    foreach ($template_files as $k => $file) {
        $rArr = [];
        $current = $k + 1;
        $tArr = explode(TMPL_SPLT, $file); // 拆分视图文件
        $view_file = trim($tArr[1], '/'); // 视图文件

        $json_file = str_replace('/', '-', $view_file); // json文件
        echo "[$current/$file_count] 正在翻译: $view_file ...\n";
        $lang_json = VIEW_PATH . "/${json_file}.json";
        if (is_file($lang_json)) {
            echo "文件已被翻译: $lang_json, 跳过 ...\n";
            continue;
        }

        $wArr = get_words($file); // 提取
        if (count($wArr) > 0) {
            $sArr = $wArr[0];
            $sCount = count($sArr);
            if (count($sArr) > 0) {
                echo "[$current/$file_count] 提取中文字符串: $sCount 个 ... \n";
                $rows = array_values($sArr); // 提取所有文字
                foreach ($rows as $r) { // 针对每个字
                    $rArr[] = get_trans_all($r); // 翻译出来的每字
                }
            }
        }

        echo "[$current/$file_count] 保存到文件: $lang_json. \n";
        $result = json_encode($rArr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($lang_json, $result . "\n");
        // if ($k >= $max) {
        //     break;
        // }
    }
}

function translate_words()
{
    $files = scandir(CHIN_PATH);
    $file_count = count($files);
    foreach ($files as $fk => $file) {
        if ($file == '.' || $file == '..') {
            continue;
        }

        $save_path = TRAN_PATH . '/' . str_replace('.txt', '.json', $file); // 要保存文件的路径
        if (is_file($save_path)) {
            echo "文件已经存在, 跳过: $save_path ...\n";
            continue;
        }

        $real_path = CHIN_PATH . '/' . $file;
        $current_k = $fk + 1;
        echo "处理文件[$current_k/$file_count]: $real_path ...\n";

        $sArr = [];
        $lines = file($real_path);
        foreach ($lines as $line) {
            $real_line = trim($line);
            if ($real_line == '') {
                continue;
            }

            echo "正在翻译: $real_line \n";
            $sArr[] = get_trans_all($real_line);
        }

        $content = json_encode($sArr, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($save_path, $content . "\n");
    }
}

// main
function main()
{
    translate_words();
}


main();