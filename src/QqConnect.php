<?php
/**
 * @authors ShenYan (52o@qq52o.cn)
 * @boke    https://qq52o.me
 */
namespace Auth;

use Auth\Common;

class QqConnect {

	protected $appId; //申请QQ登录成功后，分配给应用的APP ID
	protected $appKey; //申请QQ登录成功后，分配给应用的APP Key
	protected $callBackUrl; //成功授权后的回调地址

	protected $authCodeUrl = "https://graph.qq.com/oauth2.0/authorize"; //获取code
    protected $accessTokenUrl = "https://graph.qq.com/oauth2.0/token"; //获取access token
    protected $getUserOpenIdUrl = "https://graph.qq.com/oauth2.0/me"; //获取用户openid
    protected $userInfoUrl = "https://graph.qq.com/user/get_user_info"; //获取用户信息

	public function __construct($appId, $appKey,$callBackUrl) {
		$this->appid = $appId;
		$this->appkey = $appKey;
		$this->callbackurl = $callBackUrl;
	}

	public function qqLogin() {
		//-------生成唯一随机串防CSRF攻击
		$state = md5(uniqid(rand(), TRUE));
        //判断是否开启 自动完成session_start()
        if (ini_get('session.auto_start') == 0) {
            die('请先开启php.ini中的session.auto_start配置');
        }
        $_SESSION["state"] = $state;

		//-------构造请求参数列表
		$keysArr = array(
			"response_type" => "code",
			"client_id" => $this->appid,
			"redirect_uri" => $this->callbackurl,
			"state" => $state,
            "scope" => "get_user_info", //不传则默认请求对接口get_user_info进行授权
		);
		$login_url = Common::combineURL($this->authCodeUrl, $keysArr);

		header("Location:$login_url");
	}

    /**
     * 获取AccessToken
     * @return int|mixed
     */
    public function getAccessToken()
    {
        $state = $_SESSION['state'];

        //--------验证state防止CSRF攻击
        if(!$state || $_GET['state'] != $state){
            die("stare错误");
        }

        //-------请求参数列表
        $keysArr = array(
            "grant_type" => "authorization_code",
            "client_id" => $this->appid,
            "redirect_uri" => urlencode($this->callbackurl),
            "client_secret" => $this->appkey,
            "code" => $_GET['code']
        );

        //------构造请求access_token的url
        $token_url = Common::combineURL($this->accessTokenUrl, $keysArr);
        $response = Common::getContents($token_url);

        if(strpos($response, "callback") !== false){

            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
            $msg = json_decode($response);

            if(isset($msg->error)){
                die($msg->error);
            }
        }

        $params = array();
        parse_str($response, $params);
        return $params["access_token"];
    }

    /**
     * 获取用户openid
     * @return mixed
     */
    public function getUserOpenid($accessToken)
    {
        //-------请求参数列表
        $keysArr = array(
            "access_token" => $accessToken,
        );

        //------构造请求access_token的url
        $getUserInfoUrl = Common::combineURL($this->getUserOpenIdUrl, $keysArr);
        $UserInfo = Common::getContents($getUserInfoUrl);
        $res = json_decode(substr($UserInfo, 9, -3));
        return $res->openid;
    }

    public function qqCallBack(){
        $accessToken = $this->getAccessToken();
        $userOpenId = $this->getUserOpenid($accessToken);
        //-------请求参数列表
        $keysArr = array(
            "access_token" => $accessToken,
            "oauth_consumer_key" => $this->appid,
            "openid" => $userOpenId
        );

        //------构造请求access_token的url
        $getUserInfoUrl = Common::combineURL($this->userInfoUrl, $keysArr);
        $userInfo = Common::getContents($getUserInfoUrl);

        return $userInfo; //用户昵称nickname 头像链接 figureurl_qq_2 100*100 figureurl_qq_1 40*40
    }

}