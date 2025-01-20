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


namespace Modules\Pay\Http\Controllers\Front;

use App\Support\StateCode;
use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Account\Repositories\Models\User;
use Modules\Pay\Services\ConsumeTradeService;
use Modules\Pay\Services\UserResourceService;

use Yansongda\Pay\Pay;

class PaymentIndexController extends BaseController
{
    private $userResourceService;
    private $consumeTradeService;
    private $userId;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserResourceService $userResourceService, ConsumeTradeService $consumeTradeService)
    {
        $this->userResourceService = $userResourceService;
        $this->consumeTradeService = $consumeTradeService;

        $this->userId = User::getUserId();
    }


    /**
     * 余额支付
     */
    public function moneyPay(Request $request)
    {
        // 获取请求参数
        $req = $request->all();
        $req['payment_channel_id'] = StateCode::PAYMENT_MET_MONEY;
        $req['deposit_payment_type'] = StateCode::PAYMENT_TYPE_ONLINE;

        // 处理订单支付
        $result = $this->consumeTradeService->processMoneyPayment($this->userId, $req);

        return Respond::success($result);
    }


    public function wechatH5Pay()
    {
        $result = [];
        return Respond::success($result);
    }


    /**
     * 支付宝配置信息
     * @return array
     */
    public function getAlipayConfig()
    {
        $root_bath = base_path();
        $config = [
            'alipay' => [
                'default' => [
                    'app_id' => '2021004121611368', // 必填-支付宝分配的 app_id
                    // 必填-应用私钥 字符串或路径
                    'app_secret_cert' => 'MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQC5qkmZfDfsdjRJodPnqQQZUfHVQpbPUUTH+e7al6Jm3DOueSvpCIwMF3N22cQeviaGtRM1xCEe6iHHtQJyvXZDgOu68pmn0hBXNbMk0sXNWrR2Atujxk6FlISuIy9Jf3XrU1JVFsCIntDhBNEUo6OH5PmgdUNIycjUNysrnVhZss3+DyUaB+7CxqH0Y17/AZue/OgTeGkGO6/CKHZMAxKgivDqVNt72f9UNzSaA2ZoaQxzqBxDvQxOHkxutl1hrEUhIhPkXrsnXkOGkDZdsMBIjxp/Px862AYAOlBd5i4yGsGz9a43J2KyRsOe78S7YwORmFg5JtH990JDYGIusWWNAgMBAAECggEAZWgX6OgK13E8X9cumUIcRgQW1QcYvcVCjwL4rZXSkuHErI/sJsyPSW9plkmcr7nl6v9trZkhCfSRXLWFz8uhk38Pwb0Npba7TBa9cOhaNy5KkIZBFrOSYa1bxozbIAapDk4lEuppYHV12uE5nU8/W1L58OT7Sf9EXHyBbMH05pAuxwUCCeo/WyPxSQ6PWRv/xt2+4Xu9ogWcAL6MsxsiE6QjlYo3QuViawKbEj9sUPBW3poyMrjTNpK64x0gdjK1lOcJY0gCQcJsKoa2+AMo1MXxCe8Su2MRC9Nb2SuhlTv6eoockaUylvsP3Na2N6HHCEEQyHNsU8hfb1CSKwDUQQKBgQDrupqIhpFFbV7xU6TdzyK/aIsh3swmibWtuszpLjQ2eV1B30zfwSyiAPzFZCFlXZyrOego73sjhDgTRD267Zm70Nk4KFqB1Qj6KLoDRs2Cfkp/mdUro49a1Ts0o1owDjYEpIPYxYZOGkX8NDtycCIkOvqqJa6aS3qOlIhR5wYRfQKBgQDJoZFtC6QUsbgELs1c/nEUkB4ZfLzNE4+2eWWYrR/5jHfnZuBikI4v/J6NaeqCvsxaQMIVSzXQ5+T/PooH6DPk5UnoV0IyqGLnes9F5pN6YYYYhOCo/KJMq+fNhQMNFqiZzE5fym2XuoNdkC0HnoIK173c7I4TPioljLN6frPhUQKBgQChLg956GEuUpFHe0TQcVA2BnqTpy5571EtP/vaOMB0utk8MD31BLXK89fh9AwtritwnICUdOMCruZUriVzSgEC/dN45Ya1HYAs5GoD0Ya1gjrYMswiMYzUs9XusP76uszOsdqA/tZNUwOlZeV74xZFJZq9elR/pbpgAUmQjuGEVQKBgQCldNrUY9ASVz/M1ucYn4cFu7mnao+3rYypzXaMUczCR/2Auw/4cezr/d3R549UGOOyUB+zv5L6ycBFn/k+wdILzAfZC/m7figjEckS8EInE+4pIqkEosNALXS7VqIJVIWoJ1pNCtzhvGDeH1iEPxMxeJZJuyhfLA0D4TDKnTxY8QKBgQDXXPoWCwV8Xm69kIF031agU+ZuzEPxFXPqFBaXvmJEckZtYZoBINPqHPpNUKRE4HpXSJcHubY6Se2XCa25L6CQLhtQQD+AT0bwPkmNPQE5jjf9+NgteVd+4/aeiwoLZtQ8LU4riJdjaHfipYizLUzZ/Lx/1tVaihW1I73aMP8KzA==',
                    'app_public_cert_path' => $root_bath . '/certs/alipay/appCertPublicKey_2021004121611368.crt', // 必填-应用公钥证书 路径
                    'alipay_public_cert_path' => $root_bath . '/certs/alipay/alipayCertPublicKey_RSA2.crt', // 必填-支付宝公钥证书 路径
                    'alipay_root_cert_path' => $root_bath . '/certs/alipay/alipayRootCert.crt', // 必填-支付宝根证书 路径
                    'return_url' => env('URL_PC') . '/front/pay/callback/alipayReturn',
                    'notify_url' => env('URL_PC') . '/front/pay/callback/alipayNotify',
                    'app_auth_token' => '',  // 选填-第三方应用授权token
                    'service_provider_id' => '', // 选填-服务商模式下的服务商 id，当 mode 为 Pay::MODE_SERVICE 时使用该参数
                    'mode' => Pay::MODE_NORMAL, // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
                ],
            ],
            /*'logger' => [ // optional
                'enable' => false,
                'file' => './logs/alipay.log',
                'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],*/
            'http' => [ // optional
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
            ],
        ];

        return $config;
    }


    /**
     * 支付宝网页支付
     */
    public function alipayPay(Request $request)
    {
        $return_flag = $request->get('return_flag', 0);
        $order_id = $request->get('order_id', 0);
        $trade_info = $this->consumeTradeService->getTradeInfo($order_id);

        $config = $this->getAlipayConfig();
        $order = [
            '_return_rocket' => true,
            'out_trade_no' => '' . time(),
            'total_amount' => $trade_info['trade_amount'],
            'subject' => $trade_info['trade_title'],
        ];

        $result = Pay::alipay($config)->h5($order);

        if ($return_flag) {
            $result = $result->toArray();
            $mweb_url = $result['radar']['url'] . '&' . $result['radar']['body'];
            $data['mweb_url'] = $mweb_url;
            $data['status_code'] = 200;
            $data['statusCode'] = 200;
            $data['order_id'] = $order_id;
        } else {
            $data = $result;
            $data['status_code'] = 200;
            $data['statusCode'] = 200;
            $data['order_id'] = $order_id;
        }

        return Respond::success($data);
    }


}
