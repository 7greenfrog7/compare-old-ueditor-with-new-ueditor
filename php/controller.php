<?php
include '../../system/config.inc.php';
include '../../system/aws_app.inc.php';
include '../../system/system.php';
include '../../system/class/cls_helper.inc.php';
//header('Access-Control-Allow-Origin: http://www.baidu.com'); //设置http://www.baidu.com允许跨域访问
//header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
date_default_timezone_set("Asia/chongqing");
error_reporting(E_ERROR);
header("Content-Type: text/html; charset=utf-8");

$logfile = "../../my-errors.log";

$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("config.json")), true);
$action = $_GET['action'];


//get user name
if (! AWS_APP::session()->client_info AND $_COOKIE[G_COOKIE_PREFIX . '_user_login'])
{
    $auth_hash_key = md5(G_COOKIE_HASH_KEY . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    // 解码 Cookie
    $sso_user_login = H::decode_hash($_COOKIE[G_COOKIE_PREFIX . '_user_login'], $auth_hash_key);
    if ($sso_user_login['user_name'])
    {
        $username = $sso_user_login['user_name'];
    }
}

switch ($action) {
    case 'config':
        $result =  json_encode($CONFIG);
        break;

    /* 上传图片 */
    case 'uploadimage':
    /* 上传涂鸦 */
    case 'uploadscrawl':
    /* 上传视频 */
    case 'uploadvideo':
    /* 上传文件 */
    case 'uploadfile':
        $result = include("action_upload.php");
        break;

    /* 列出图片 */
    case 'listimage':
        $result = include("action_list.php");
        break;
    /* 列出文件 */
    case 'listfile':
        $result = include("action_list.php");
        break;

    /* 抓取远程文件 */
    case 'catchimage':
        $result = include("action_crawler.php");
        break;

    default:
        $result = json_encode(array(
            'state'=> '请求地址出错'
        ));
        break;
}

/* 输出结果 */
if (isset($_GET["callback"])) {
    if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
        echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
    } else {
        echo json_encode(array(
            'state'=> 'callback参数不合法'
        ));
    }
} else {
    echo $result;
}