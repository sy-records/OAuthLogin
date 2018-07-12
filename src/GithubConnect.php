<?php
/**
 * @authors ShenYan (52o@qq52o.cn)
 * @boke    https://qq52o.me
 */
namespace Auth;

use Auth\Common;

class GithubConnect
{
    protected $clientId; //申请OAuth App成功后，分配给应用的Client ID
    protected $clientSecret; //申请OAuth App成功后，分配给应用的Client Secret
    protected $callBackUrl; //成功授权后的回调地址

    protected $authCodeUrl = "https://github.com/login/oauth/authorize"; //获取code
    protected $accessTokenUrl = "https://github.com/login/oauth/access_token"; //获取access token
    protected $userInfoUrl = "https://api.github.com/user"; //获取用户信息

    public function __construct($clientId, $clientSecret,$callBackUrl) {
        $this->appid = $clientId;
        $this->appkey = $clientSecret;
        $this->callbackurl = $callBackUrl;
    }

    public function githubLogin() {
        //-------生成唯一随机串防CSRF攻击
        $state = md5(uniqid(rand(), TRUE));
        //判断是否开启 自动完成session_start()
//        if (!ini_get('session.auto_start' == '1')) {
//            die('请先开启php.ini中的session.auto_start配置');
//        }
        $_SESSION["state"] = $state;

        //-------构造请求参数列表
        $keysArr = array(
            "client_id" => $this->appid,
            "redirect_uri" => $this->callbackurl,
            "state" => $state,
            "scope" => "user", //不传则默认请求对接口user进行授权
            "allow_signup" => true, //是否会在OAuth流程中为未经身份验证的用户提供注册GitHub的选项。默认是true
        );
        $login_url = Common::combineURL($this->authCodeUrl, $keysArr);

        header("Location:$login_url");
    }

    public function getAccessToken()
    {
        $state = $_SESSION['state'];

        //--------验证state防止CSRF攻击
        if(!$state || $_GET['state'] != $state){
            die("stare错误");
        }
        //-------请求参数列表
        $keysArr = array(
            "client_id" => $this->appid,
            "client_secret" => $this->appkey,
            "code" => $_GET['code'],
            "redirect_uri" => $this->callbackurl,
            "state" => $state,
        );

        //------构造请求access_token的url
        $token_url = Common::combineURL($this->accessTokenUrl, $keysArr);

        $accessToken = Common::getContents($token_url);
        // 截取字符串
        $str = substr($accessToken, 13, -29);
        return $str;
    }

    public function githubCallBack(){
        $accessToken = $this->getAccessToken();
        //-------请求参数列表
        $keysArr = array(
            "access_token" => $accessToken,
        );

        //------构造请求access_token的url
        $getUserInfoUrl = Common::combineURL($this->userInfoUrl, $keysArr);
        $userInfo = Common::getContents($getUserInfoUrl);
        return $userInfo;
    }
}