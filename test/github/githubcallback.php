<?php
/**
 * @authors ShenYan (52o@qq52o.cn)
 * @boke    https://qq52o.me
 */
include_once './../../src/GithubConnect.php';
include_once './../../src/Common.php';

use Auth\GithubConnect;

$clientId = '';
$clientSecret = '';
$callback = "";

$githubAuth = new GithubConnect($clientId,$clientSecret,$callback);

$res = $githubAuth->githubCallBack();

var_dump($res);