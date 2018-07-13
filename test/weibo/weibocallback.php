<?php
/**
 * @authors ShenYan (52o@qq52o.cn)
 * @boke    https://qq52o.me
 */
include_once './../../src/WeiboConnect.php';
include_once './../../src/Common.php';

use Auth\WeiboConnect;

$appKey = ''; # 网站应用申请后的App Key
$appSecret = ''; # 网站应用申请后的App Secret
$callback = ''; # 授权回调地址

$weiboAuth = new WeiboConnect($appKey,$appSecret,$callback);

$res = $weiboAuth->weiboCallBack();
var_dump($res);