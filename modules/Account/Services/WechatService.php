<?php
// +----------------------------------------------------------------------
// | ShopSuite商城系统 [ 赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | 版权所有 随商信息技术（上海）有限公司
// +----------------------------------------------------------------------
// | 未获商业授权前，不得将本软件用于商业用途。禁止整体或任何部分基础上以发展任何派生版本、
// | 修改版本或第三方版本用于重新分发。
// +----------------------------------------------------------------------
// | 官方网站: https://www.kuteshop.cn  https://www.kuteshop.cn
// +----------------------------------------------------------------------
// | 版权和免责声明:
// | 本公司对该软件产品拥有知识产权（包括但不限于商标权、专利权、著作权、商业秘密等）
// | 均受到相关法律法规的保护，任何个人、组织和单位不得在未经本团队书面授权的情况下对所授权
// | 软件框架产品本身申请相关的知识产权，禁止用于任何违法、侵害他人合法权益等恶意的行为，禁
// | 止用于任何违反我国法律法规的一切项目研发，任何个人、组织和单位用于项目研发而产生的任何
// | 意外、疏忽、合约毁坏、诽谤、版权或知识产权侵犯及其造成的损失 (包括但不限于直接、间接、
// | 附带或衍生的损失等)，本团队不承担任何法律责任，本软件框架只能用于公司和个人内部的
// | 法律所允许的合法合规的软件产品研发，详细见https://www.modulithshop.cn/policy
// +----------------------------------------------------------------------


namespace Modules\Account\Services;

use App\Exceptions\ErrorException;
use App\Support\StateCode;
use Illuminate\Support\Facades\Http;
use Modules\Sys\Services\ConfigBaseService;
use Illuminate\Support\Facades\Redis;

/**
 * Class WechatService.
 *
 * @package Modules\Account\Services
 */
class WechatService
{

    const GET_TOKEN_URL = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=APPID&secret=APPSECRET";
    private $configBaseService;

    public function __construct(ConfigBaseService $configBaseService)
    {
        $this->configBaseService = $configBaseService;
    }


    /**
     * 获取AccessToken  向外提供
     */
    public function getXcxAccessToken($useCacheFlag)
    {
        if ($useCacheFlag) {
            if (!Redis::exists(StateCode::WX_XCX_ACCESSTOKEN)) {
                return $this->getXcxToken();
            }
            return Redis::get(StateCode::WX_XCX_ACCESSTOKEN);
        } else {
            return $this->getXcxToken();
        }
    }


    /**
     * 发送get请求获取AccessToken
     */
    public function getXcxToken()
    {
        $wechat_app_id = $this->configBaseService->getConfig('wechat_xcx_app_id');
        $wechat_app_secret = $this->configBaseService->getConfig('wechat_xcx_app_secret');
        $url = str_replace(['APPID', 'APPSECRET'], [$wechat_app_id, $wechat_app_secret], WechatService::GET_TOKEN_URL);
        $response = Http::get($url);

        if ($response->successful()) {
            $data = $response->json();
            $access_token = $data['access_token'];
        } else {
            throw new ErrorException('获取小程序access_token失败！');
        }

        $expires_in = $data['expires_in'];
        Redis::set(StateCode::WX_XCX_ACCESSTOKEN,$access_token,$expires_in);

        return $access_token;
    }


}
