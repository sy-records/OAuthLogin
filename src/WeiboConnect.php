<?php
/**
 * @authors ShenYan (52o@qq52o.cn)
 * @boke    https://qq52o.me
 */
namespace Auth;

use Auth\Common;

class WeiboConnect
{
    protected $appId; //应用App Key
    protected $appSecret; //应用的App Secret
    protected $callBackUrl; //成功授权后的回调地址

    protected $authCodeUrl = "https://api.weibo.com/oauth2/authorize"; //获取code
    protected $accessTokenUrl = "https://api.weibo.com/oauth2/access_token"; //获取access token
    protected $userInfoUrl = "https://api.weibo.com/2/users/show.json"; //获取用户信息

    public function __construct($appId, $appSecret, $callBackUrl)
    {
        $this->appid = $appId;
        $this->appkey = $appSecret;
        $this->callbackurl = $callBackUrl;
    }

    public function weiboLogin() {
        //-------生成唯一随机串防CSRF攻击
        $state = md5(uniqid(rand(), TRUE));
        //判断是否开启 自动完成session_start()
        if (ini_get('session.auto_start') == 0) {
            die('请先开启php.ini中的session.auto_start配置');
        }
        $_SESSION["state"] = $state;

        //-------构造请求参数列表
        $keysArr = array(
            "client_id" => $this->appid,
            "redirect_uri" => $this->callbackurl,
            "state" => $state,
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
            "grant_type" => "authorization_code",
            "code" => $_GET['code'],
            "redirect_uri" => $this->callbackurl,
        );

        //------构造请求access_token的url
        $url = Common::combineURL($this->accessTokenUrl,$keysArr);
        $response = Common::postContents($url);
        $res =json_decode($response);
        return $res;
    }

    public function weiboCallBack(){
        $accessToken = $this->getAccessToken();
        //-------请求参数列表
        $keysArr = array(
            "access_token" => $accessToken->access_token,
            "uid" => $accessToken->uid
        );

        //------构造请求access_token的url
        $url = Common::combineURL($this->userInfoUrl,$keysArr);
        $response = Common::getContents($url);
        return $response;
    }
}