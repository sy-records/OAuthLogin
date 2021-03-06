<?php
/**
 * @authors ShenYan (52o@qq52o.cn)
 * @boke    https://qq52o.me
 */
include_once './../../src/QqConnect.php';
include_once './../../src/Common.php';

use Auth\QqConnect;

//QQ互联管理中心 https://connect.qq.com/manage.html#/ 创建应用拿到 APP ID 和 APP Key
$appId = '';
$appKey = '';
$callBackUrl = ''; // http://wiki.connect.qq.com/回调地址常见问题及修改方法

$qqAuth = new QqConnect($appId,$appKey,$callBackUrl);

$qqAuth->qqLogin();