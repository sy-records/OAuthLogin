<?php
/**
 * @authors ShenYan (52o@qq52o.cn)
 * @boke    https://qq52o.me
 */
include_once './../../src/WeiboConnect.php';
include_once './../../src/Common.php';

use Auth\WeiboConnect;

$clientId = '';
$clientSecret = '';
$callback = "";

$weiboAuth = new WeiboConnect($clientId,$clientSecret,$callback);

$res = $weiboAuth->weiboCallBack();
var_dump($res);