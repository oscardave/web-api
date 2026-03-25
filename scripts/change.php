<?php

// 目录位置
const TMPL_PATH = '/Users/ai/work/sports/admin/templates'; // 模板文件位置
const SAVE_PATH = '/Users/ai/work/sports/admin/templates_trans'; // 视图文件保存路径
const REG_LINE = '/_lang/'; // 查找行
const REG_REP = '{{_lang(\'\1\', LANG)}}'; // 视图语言替换

const REG = '/([\x{4e00}-\x{9fa5}]+)/u'; // 中文获取

const CONT_PATH = '/Users/ai/work/sports/admin/src/controllers'; // 控制器文件位置
const SAVE_CONT_PATH = '/Users/ai/work/sports/admin/src/controllers_t'; // 视图文件保存路径


// 所有视图文件列表
function get_view_files(string $path, array &$tArr, array &$vArr)
{
    $files = scandir($path);
    foreach ($files as $f) {
        if ($f == '.' || $f == '..') { // 跳过不是目录的部分
            continue;
        }
        $real_path = $path . '/' . $f;
        $sArr = explode('templates/', $path);
        $save_path = SAVE_PATH . '/' . $sArr[1] . '/' . $f;
        if (is_dir($real_path)) { // 如果是目录, 则递归
            if (!is_dir($save_path)) {  // 如果存在文件
                mkdir($save_path);
            }
            get_view_files($real_path, $tArr, $vArr);
            continue;
        }
        $tArr[] = $real_path;
        $vArr[] = $save_path;
    }
}

// 所有控制文件列表
function get_controller_files(string $path, array &$tArr, array &$vArr)
{
    $files = scandir($path);
    foreach ($files as $f) {
        if ($f == '.' || $f == '..') { // 跳过不是目录的部分
            continue;
        }
        $real_path = $path . '/' . $f;
        $save_path = SAVE_CONT_PATH . '/' . $f;

        $tArr[] = $real_path;
        $vArr[] = $save_path;
    }
}

// 替换视图文件
function rep_view_file(string $path, string $to_path)
{
    if (!is_file($path)) {
        echo "源文件并不存在: $path \n";
        return;
    }

    $lines = file($path);
    $wLines = [];
    foreach ($lines as $line) {
        if (preg_match(REG_LINE, $line)) {  // 如果已经匹配过了, 直接把这个文件挪过来
            file_put_contents($to_path, file_get_contents($path));
            return;
        }

        $real_line = trim($line);
        if ($real_line == '') {
            continue;
        }

        $rep = REG_REP;
        if (strpos($real_line, "'") > 0) {// 如果有双引号
            $rep = str_replace("'", '"', $rep);
        }
        $dArr = explode('//', $real_line);
        if (count($dArr) > 1) {
            if (trim($dArr[0]) == '') { // 表示当前行是注释
                continue;
            }
            $s = preg_replace(REG, $rep, $dArr[0]);
            $dArr[0] = $s . ' //';
            $wLines[] = implode('', $dArr) . "\n";
            continue;
        }

        $wLine = preg_replace(REG, $rep, $line);
        $wLines[] = $wLine;
    }

    $file = fopen($to_path, 'w+');
    if (!$file) {
        echo "打开文件出现错误: $to_path \n";
        return;
    }

    foreach ($wLines as $line) {
        fwrite($file, $line);
    }
    fclose($file);
    echo "写入文件成功: $to_path \n";
}


// 替换控制器文件
function rep_controller_file(string $path, string $to_path)
{
    if (!is_file($path)) {
        echo "源文件并不存在: $path \n";
        return;
    }

    $lines = file($path);
    $wLines = [];
    $reg = '/("[a-zA-Z0-9]*[\x{4e00}-\x{9fa5}\-]+[a-zA-Z0-9]*\s*\,*[\x{4e00}-\x{9fa5}\-]+[a-zA-Z0-9]*!?")/u'; // 中文获取
    foreach ($lines as $line) {
        $real_line = trim($line);

        if (preg_match('/RenderErr/', $real_line) || preg_match('/errors\.New/', $real_line)) {  // 只替换相关错误
            $rep_value = 'lang(\1, c)';
            $wLine = preg_replace($reg, $rep_value, $line);
            $wLines[] = $wLine;
            continue;
        }

        $wLines[] = $line;
    }

    $file = fopen($to_path, 'w+');
    if (!$file) {
        echo "打开文件出现错误: $to_path \n";
        return;
    }

    foreach ($wLines as $line) {
        fwrite($file, $line);
    }
    fclose($file);
    echo "写入文件成功: $to_path \n";
}

// main
function main()
{
    $fArr = [];
    $tArr = [];
    // get_view_files(TMPL_PATH, $fArr, $tArr);
    // foreach ($fArr as $k => $f) {
    //     rep_view_file($f, $tArr[$k]);
    // }

    get_controller_files(CONT_PATH, $fArr, $tArr);
    foreach ($fArr as $k => $f) {
        rep_controller_file($f, $tArr[$k]);
    }
}

main();

