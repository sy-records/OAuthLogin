<?php
/**
 * @authors ShenYan (52o@qq52o.cn)
 * @boke    https://qq52o.me
 */
include_once './../../src/GithubConnect.php';
include_once './../../src/Common.php';

use Auth\GithubConnect;

# https://github.com/settings/developers 创建OAuth App
$clientId = ''; # 创建OAuth App得到的Client ID
$clientSecret = ''; # 创建OAuth App得到的Client Secret
$callback = ''; # Authorization callback URL

$githubAuth = new GithubConnect($clientId,$clientSecret,$callback);

$githubAuth->githubLogin();