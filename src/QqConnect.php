<?php
/**
 * @authors ShenYan (52o@qq52o.cn)
 * @boke    https://qq52o.me
 */
namespace Auth;

class QqAuth {

	protected $appId; //申请QQ登录成功后，分配给应用的APP ID
	protected $appKey; //申请QQ登录成功后，分配给应用的APP Key
	protected $callBackUrl; //成功授权后的回调地址

	protected $authCodeUrl = "https://graph.qq.com/oauth2.0/authorize"; //获取code
    protected $accessTokenUrl = "https://graph.qq.com/oauth2.0/token"; //获取access token
    protected $getUserOpenIdUrl = "https://graph.qq.com/oauth2.0/me"; //获取用户openid

	public function __construct($appId, $appKey,$callBackUrl) {
		$this->appid = $appId;
		$this->appkey = $appKey;
		$this->callbackurl = $callBackUrl;
	}

	public function qqLogin() {
		//-------生成唯一随机串防CSRF攻击
		$state = md5(uniqid(rand(), TRUE));
        $_SESSION["state"] = $state;

		//-------构造请求参数列表
		$keysArr = array(
			"response_type" => "code",
			"client_id" => $this->appid,
			"redirect_uri" => $this->callbackurl,
			"state" => $state,
            "scope" => "get_user_info", //不传则默认请求对接口get_user_info进行授权
		);
		$login_url = CommonAuth::combineURL($this->authCodeUrl, $keysArr);

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
            return 30001;
        }
        session_unset($_SESSION['state']);

        //-------请求参数列表
        $keysArr = array(
            "grant_type" => "authorization_code",
            "client_id" => $this->appid,
            "redirect_uri" => urlencode($this->callbackurl),
            "client_secret" => $this->appkey,
            "code" => $_GET['code']
        );

        //------构造请求access_token的url
        $token_url = CommonAuth::combineURL($this->accessTokenUrl, $keysArr);
        $response = CommonAuth::getContents($token_url);

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
        $res = CommonAuth::getContents($this->getUserOpenIdUrl.'?access_token='.$accessToken);
        return $res;
    }

    public function qqCallBack(){
        $accessToken = self::getAccessToken();
        $userOpenId = self::getUserOpenid($accessToken);
        $objUserId = json_decode(substr($userOpenId, 9, -3));
        $getUserInfoUrl = "https://graph.qq.com/user/get_user_info?access_token=".$accessToken."&oauth_consumer_key=".$this->appid."&openid=".$objUserId->openid;
        $userInfo = CommonAuth::getContents($getUserInfoUrl);
        return $userInfo; //用户昵称nickname 头像链接 figureurl_qq_2 100*100 figureurl_qq_1 40*40
    }

}