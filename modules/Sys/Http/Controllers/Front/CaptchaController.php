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


namespace Modules\Sys\Http\Controllers\Front;

use App\Exceptions\ErrorException;
use App\Support\PhoneNumberUtils;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Sys\Services\ConfigBaseService;
use Modules\Sys\Services\VerifyCodeService;

class CaptchaController extends BaseController
{
    private $configBaseService;
    private $verifyCodeService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ConfigBaseService $configBaseService, VerifyCodeService $verifyCodeService)
    {
        $this->configBaseService = $configBaseService;
        $this->verifyCodeService = $verifyCodeService;
    }


    /**
     * 图形验证码
     */
    public function index(Request $request)
    {
        $verify_key = $request->get('verify_key');
        $builder = new CaptchaBuilder(4);
        $builder->build(120);

        $code = $builder->getPhrase();
        $expiredAt = Carbon::now()->addMinutes(1);
        Cache::put($verify_key, $code, $expiredAt);

        header('Content-type: image/jpeg');
        $builder->output();
    }


    /**
     * 发送短信验证码
     */
    public function sendMobileVerifyCode(Request $request)
    {

        $verify_key = $request->input('mobile');
        if (!PhoneNumberUtils::isValidNumber($verify_key)) {
            throw new ErrorException('手机号码不准确！');
        }

        $phoneModelWithCountry = PhoneNumberUtils::getPhoneModelWithCountry($verify_key);
        if ($phoneModelWithCountry === null) {
            throw new ErrorException("手机号码解析失败！");
        }

        $mobile = $phoneModelWithCountry->nationalNumber;
        $service_user_id = $this->configBaseService->getConfig('service_user_id', '');
        $service_app_key = $this->configBaseService->getConfig('service_app_key', '');

        $verify_code = getVerifyCode(4);
        $sms_params = [
            'rtime' => time(),
            'app_id_from' => 100,
            'user_id_from' => $service_user_id,
            'service_app_key' => $service_app_key,
            'sms_mobile' => (string)$mobile,
            'sms_content' => sprintf("您的验证码: [%s] 5分钟内有效", $verify_code),
        ];

        try {
            $url = "https://account.shopsuite.cn/index.php?mdu=service&ctl=Sms&met=send&typ=json&t=1";
            $response = Http::withOptions(['verify' => false])->asForm()->post($url, $sms_params);
            $result = $response->body();
            $result = json_decode($result, true);
            $flag = $result['data'];
            $flag['verifycode'] = $verify_code;
        } catch (\Exception $e) {
            throw new ErrorException($e->getMessage());
        }

        $this->verifyCodeService->setVerifyCode($verify_key, $verify_code);

        if ($flag) {
            return Respond::success($flag);
        } else {
            return Respond::error('fail');
        }

    }


}
