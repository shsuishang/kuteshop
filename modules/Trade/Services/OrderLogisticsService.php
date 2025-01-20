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


namespace Modules\Trade\Services;

use GuzzleHttp\Client;
use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use Modules\Sys\Repositories\Contracts\ExpressBaseRepository;
use Modules\Trade\Repositories\Contracts\OrderDeliveryAddressRepository;
use Modules\Trade\Repositories\Contracts\OrderLogisticsRepository;
use App\Exceptions\ErrorException;

/**
 * Class OrderLogisticsService.
 *
 * @package Modules\Trade\Services
 */
class OrderLogisticsService extends BaseService
{

    private $orderDeliveryAddressRepository;
    private $configBaseRepository;
    private $expressBaseRepository;
    private $orderService;

    public function __construct(
        OrderLogisticsRepository       $orderLogisticsRepository,
        OrderDeliveryAddressRepository $orderDeliveryAddressRepository,
        ConfigBaseRepository           $configBaseRepository,
        ExpressBaseRepository          $expressBaseRepository,
        OrderService                   $orderService)
    {
        $this->repository = $orderLogisticsRepository;
        $this->orderDeliveryAddressRepository = $orderDeliveryAddressRepository;
        $this->configBaseRepository = $configBaseRepository;
        $this->expressBaseRepository = $expressBaseRepository;
        $this->orderService = $orderService;
    }

    private static $stateMap = [
        "0" => "没有记录",
        "1" => "已揽收",
        "2" => "运输途中",
        "201" => "到达目的城市",
        "202" => "派件中",
        "211" => "已投放快递柜或驿站",
        "3" => "已签收",
        "301" => "正常签收",
        "302" => "派件异常后最终签收",
        "304" => "代收签收",
        "311" => "快递柜或驿站签收",
        "4" => "问题件",
        "401" => "发货无信息",
        "402" => "超时未签收",
        "403" => "超时未更新",
        "404" => "拒收(退件)",
        "405" => "派件异常",
        "406" => "退货签收",
        "407" => "退货未签收",
        "412" => "快递柜或驿站超时未取"
    ];


    /**
     * 物流跟踪
     * @param $req
     * @return array
     * @throws ErrorException
     */
    public function trace($req)
    {
        $result = [];
        $order_id = $req->input('order_id', '');
        $order_delivery_address = $this->orderDeliveryAddressRepository->getOne($order_id);
        if (empty($order_delivery_address)) {
            throw new ErrorException(__("未找到收货地址"));
        }

        $mobile = $order_delivery_address['da_mobile'];
        $order_tracking_number = $req->input('order_tracking_number', '');
        if (trim($order_tracking_number) == '') {
            throw new ErrorException(__("订单物流单号为空"));
        }

        $order_logistics_id = $req->input('order_logistics_id', 0);
        $express_id = $req->input('express_id', 0);
        if ($order_logistics_id && !$express_id) {
            $order_logistics_row = $this->repository->getOne($order_logistics_id);
            $express_id = $order_logistics_row['express_id'];
        }

        $channel = $this->configBaseRepository->getConfig('logistics_channel', "kuaidi100");
        $express_base = $this->expressBaseRepository->getOne($express_id);
        if (empty($express_base)) {
            throw new ErrorException(__("快递公司有误！"));
        }

        if ($channel === 'kuaidi100') {
            $shipping_code = $express_base['express_pinyin_100'];
            $logistics_info = $this->kd100($order_tracking_number, $shipping_code, $mobile);
        } else {
            $shipping_code = $express_base['express_pinyin'];
            $logistics_info = $this->kdNiao($order_tracking_number, $shipping_code, $mobile);
        }

        $result['state'] = $logistics_info['State'];
        $result['express_state'] = $logistics_info['express_state'];
        $result['traces'] = $logistics_info['Traces'];

        return $result;
    }


    /**
     * 快递鸟接口物流数据
     * @param $order_tracking_number
     * @param $shipping_code
     * @param $mobile
     * @return mixed
     * @throws \Exception
     */
    public function kdNiao($order_tracking_number, $shipping_code, $mobile)
    {
        $logistics_info_str = $this->apiKdNiao($order_tracking_number, $shipping_code, $mobile);
        $logistics_info = json_decode($logistics_info_str, true);

        $state = $logistics_info['State'];
        if ($state == 0) {
            $reason = $logistics_info['Reason'];
            throw new ErrorException(__("非系统错误，请联系管理员检查物流配置项，或检查发货信息是否真实有效！错误信息：{" . $reason . "}"));
        }

        if (!isset($logistics_info['StateEx'])) {
            throw new ErrorException(__("物流状态异常！"));
        }

        $logistics_info['express_state'] = self::$stateMap[$logistics_info['StateEx']];

        return $logistics_info;
    }


    /**
     * 请求快递鸟接口
     * @param $orderTrackingNumber
     * @param $shipperCode
     * @param $CustomerName
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function apiKdNiao($orderTrackingNumber, $shipperCode, $CustomerName)
    {
        // 组装应用级参数
        $request_data = json_encode([
            'OrderCode' => '',
            'shipperCode' => $shipperCode,
            'CustomerName' => $CustomerName,
            'logisticCode' => $orderTrackingNumber
        ]);

        $app_key = $this->configBaseRepository->getConfig("kuaidiniao_app_key");
        $business_id = $this->configBaseRepository->getConfig("kuaidiniao_e_business_id");
        $api_url = "https://api.kdniao.com/Ebusiness/EbusinessOrderHandle.aspx";

        // 组装系统级参数
        $params = [
            'RequestData' => urlencode($request_data),
            'EBusinessID' => $business_id,
            'RequestType' => "8002", // 快递查询接口指令8002/地图版快递查询接口指令8004
            'DataSign' => urlencode($this->encrypt($request_data, $app_key)),
            'DataType' => "2"
        ];

        try {
            $client = new Client(['verify' => false]); // 禁用 SSL 验证
            $response = $client->post($api_url, [
                'form_params' => $params
            ]);
            $result = $response->getBody()->getContents();

            return $result;
        } catch (RequestException $e) {
            throw new \Exception('Error sending POST request: ' . $e->getMessage());
        }
    }


    /**
     * 数据加密方法
     */
    private function encrypt($data, $appKey)
    {
        return base64_encode(md5($data . $appKey));
    }


    //快递100接口
    public function kd100()
    {

    }

}
