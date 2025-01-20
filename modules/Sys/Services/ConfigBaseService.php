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


namespace Modules\Sys\Services;

use App\Exceptions\ErrorException;
use App\Support\ErrorTypeEnum;
use App\Support\StateCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use Modules\Sys\Repositories\Contracts\ConfigTypeRepository;
use Modules\Account\Repositories\Contracts\UserLevelRepository;
use Modules\Sys\Repositories\Contracts\CurrencyBaseRepository;
use Modules\Sys\Repositories\Contracts\LangStandardRepository;


/**
 * Class ConfigBaseService.
 *
 * @package Modules\Sys\Services
 */
class ConfigBaseService extends BaseService
{

    private $configTypeRepository;
    private $userLevelRepository;
    private $currencyBaseRepository;
    private $langStandardRepository;


    public function __construct(
        ConfigBaseRepository   $configBaseRepository,
        ConfigTypeRepository   $configTypeRepository,
        UserLevelRepository    $userLevelRepository,
        CurrencyBaseRepository $currencyBaseRepository,
        LangStandardRepository $langStandardRepository
    )
    {
        $this->repository = $configBaseRepository;
        $this->configTypeRepository = $configTypeRepository;
        $this->userLevelRepository = $userLevelRepository;
        $this->currencyBaseRepository = $currencyBaseRepository;
        $this->langStandardRepository = $langStandardRepository;
    }


    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public static function getConfig($key, $value = null)
    {
        $res = app(ConfigBaseRepository::class);
        return $res->getConfig($key, $value);
    }


    /**
     * 编辑站点配置
     *
     * @param array $config_row 配置数据数组
     * @return bool
     * @throws ErrorException
     */
    public function editSite(array $config_row): bool
    {
        // 开启数据库事务
        DB::beginTransaction();

        try {
            // 检查配置数组是否为空
            if (empty($config_row)) {
                throw new ErrorException(__('配置数据不能为空'));
            }

            // 遍历配置数据
            foreach ($config_row as $key => $val) {
                // 如果值为数组，转为字符串
                $val = is_array($val) ? implode(',', $val) : $val;

                // 查询记录
                $config_base = $this->repository->getOne($key);
                if (!$config_base) {
                    throw new ErrorException(__('配置项不存在: :key', ['key' => $key]));
                }

                // 更新记录
                $this->repository->edit($key, ['config_value' => $val ?? '']);
            }

            // 提交事务
            DB::commit();
            return true;
        } catch (\Throwable $e) {
            // 回滚事务并抛出异常
            DB::rollBack();
            DB::rollBack();
            throw new ErrorException(__('操作失败: ') . $e->getMessage());
        }
    }


    /**
     * @param $map
     * @param $options
     * @param $optionsList
     * @return void
     */
    private function manageSplit(&$map, $options, &$optionsList)
    {
        foreach ($options as $option) {
            if ($option !== null) {
                $str = preg_replace("/:|：|\s+/", "|", $option);
                $item = explode("|", $str);
                if (count($item) >= 2) {
                    $optionsList[$item[0]] = $item[1];
                }
            }
        }

        $map["optionsList"] = $optionsList;
    }


    /**
     * @param $configBase
     * @param $map
     * @return void
     */
    private function manageSingle($configBase, &$map)
    {
        $newString = preg_replace("/\r?\n/", "|", $configBase['config_options']);
        $options = explode("|", $newString);
        $optionsList = [];

        if (!empty($options)) {
            $this->manageSplit($map, $options, $optionsList);
        }
    }


    /**
     * 获取配置信息列表
     * @param $request
     * @return array
     */
    public function getConfigList($request)
    {
        $data = [];
        $config_type_module = $request->get('config_type_module');
        $config_type_list = $this->configTypeRepository->find([
            'config_type_module' => $config_type_module,
            'config_type_enable' => 1
        ]);

        if (!empty($config_type_list)) {
            $config_type_ids = array_column($config_type_list, 'config_type_id');
            $config_base_list = $this->repository->findByTypeIdAndSort($config_type_ids);

            foreach ($config_type_list as $config_type) {
                $items = [];

                if (!empty($config_base_list)) {
                    foreach ($config_base_list as $config_base) {
                        if ($config_base['config_type_id'] == $config_type['config_type_id']) {
                            $map = [
                                'config_key' => $config_base['config_key'],
                                'config_title' => $config_base['config_title'],
                                'config_value' => $config_base['config_value'],
                                'config_datatype' => $config_base['config_datatype'],
                                'config_note' => $config_base['config_note'],
                            ];
                            switch ($config_base['config_datatype']) {
                                case 'checkbox':
                                    // 复选框
                                    $newString = preg_replace("/\r?\n/", "|", $config_base['config_options']);
                                    $options = explode("|", $newString);
                                    $optionsList = [];

                                    if (!empty($options)) {
                                        $this->manageSplit($map, $options, $optionsList);
                                        // 将复选框的选择值分割并放入 $map
                                        $map["config_value"] = explode(",", $config_base['config_value']);
                                    }

                                    break;
                                case 'select':
                                case 'radio':
                                    // 下拉选择框或单选框
                                    $this->manageSingle($config_base, $map);
                                    $map['config_value'] = intval($map['config_value']);
                                    break;
                                case 'images':
                                    // 多图片
                                    $images = explode(",", $config_base['config_value']);

                                    if (!empty($images)) {
                                        // 图片地址列表
                                        $list = $images;
                                        $map["config_value"] = $list;
                                    }
                                    break;
                                default:
                                    break;
                            }

                            $items[] = $map;
                        }

                    }
                }

                $data[] = [
                    'config_type_id' => $config_type['config_type_id'],
                    'config_type_name' => $config_type['config_type_name'],
                    'items' => $items,
                ];
            }
        }

        return $data;
    }


    /**
     * 订单状态配置数组
     * @return array
     */
    public function getOrderStateList()
    {
        $data = [];
        $sc_order_process = $this->repository->getOne('sc_order_process');
        if ($sc_order_process) {
            $config_base = $sc_order_process;
            $state_id_list = explode(',', $config_base['config_value']);
            sort($state_id_list);

            $config_options = preg_replace("/\r?\n/", "|", $config_base['config_options']);
            $options = explode("|", $config_options);

            if (!empty($options)) {
                foreach ($options as $k => $v) {
                    $item = explode(':', $v);
                    if (in_array($item[0], $state_id_list)) {
                        $data[] = [
                            'label' => $item[1],
                            'value' => intval($item[0])
                        ];
                    }
                }
            }
        }

        $data[] = [
            'label' => __('交易取消'),
            'value' => StateCode::ORDER_STATE_CANCEL
        ];

        return $data;
    }


    /**
     * 退单状态配置数组
     * @return array
     */
    public function getReturnStateList()
    {
        $data = [];
        $sc_return_process = $this->repository->getOne('sc_return_process');
        if (!empty($sc_return_process)) {
            $config_base = $sc_return_process;
            $state_id_list = explode(',', $config_base['config_value']);
            sort($state_id_list);

            $config_options = preg_replace("/\r?\n/", "|", $config_base['config_options']);
            $options = explode("|", $config_options);

            if (!empty($options)) {
                foreach ($options as $k => $v) {
                    $item = explode(':', $v);
                    if (in_array($item[0], $state_id_list)) {
                        $data[] = [
                            'label' => $item[1],
                            'value' => intval($item[0])
                        ];
                    }
                }
            }
        }

        return $data;
    }


    /**
     * 获取支付渠道
     * @return array
     */
    public function getPaymentChannelList()
    {
        $payment_channel_select_list = [];

        // 获取配置类型列表
        $config_types = $this->configTypeRepository->orderBy('config_type_sort')->findWhere([
            'config_type_module' => 1004,
            'config_type_enable' => 1
        ])->toArray();

        foreach ($config_types as $config_type) {
            try {
                $id = '';
                $ck = '';
                $img = '';

                switch ($config_type['config_type_id']) {
                    case 1403:
                        $id = 'wxpay';
                        $ck = 'wechat_pay_enable';
                        $img = 'wechat_pay_logo';
                        break;
                    case 1401:
                        $id = 'alipay';
                        $ck = 'alipay_enable';
                        $img = 'alipay_logo';
                        break;
                    case 1422:
                        $id = 'offline';
                        $ck = 'offline_pay_enable';
                        $img = 'offline_pay_logo';
                        break;
                    case 1406:
                        $id = 'money';
                        $ck = 'money_pay_enable';
                        $img = 'money_pay_logo';
                        break;
                    case 1413:
                        $id = 'points';
                        $ck = 'points_pay_enable';
                        $img = 'points_pay_logo';
                        break;
                }

                if ($id) {
                    $enable = $this->getConfig($ck);
                    $logo = $this->getConfig($img);
                    $payment_channel_select_list[] = [
                        'value' => $config_type['config_type_id'],
                        'label' => $config_type['config_type_name'],
                        'ext1' => $logo,
                        'ext2' => $id,
                        'enable' => boolval($enable)
                    ];
                }
            } catch (\Exception $e) {
                Log::error($e->getMessage());
            }
        }

        return $payment_channel_select_list;
    }


    /**
     * 获取开发API数据，一些基础性的功能，平台直接提供。
     */
    public function getServiceData($params = array())
    {
        $key = $this->getConfig('service_app_key');
        $url = "https://account.shopsuite.cn/index.php?mdu=service&ctl=Base_Module&met=lists&typ=json&t=1";

        $params['rtime'] = time();
        $params['app_id_from'] = 100;
        $params['user_id_from'] = $this->getConfig('service_user_id');
        $params['service_app_key'] = $key;

        $response = Http::withOptions(['verify' => false])->asForm()->post($url, $params);
        $result = $response->body();
        $result = json_decode($result, true);

        return $result['data'];
    }


    /**
     * 获取移动端用户中心菜单
     * @return array|mixed
     */
    public function getUserCenterMenu()
    {
        $app_member_center = $this->getConfig('app_member_center');
        if (!empty($app_member_center)) {
            //app_member_center 默认配置菜单
            $json_object = json_decode($app_member_center, true);
            $page_code = $json_object['PageCode'];
            $menus = json_decode($page_code, true);
        } else {
            $menus = $this->getAllCenterMenu();
        }

        $type = $menus['type'] ?? 2;
        $menus['type'] = (int)$type;

        return $menus;
    }


    /**
     * 默认个人中心菜单
     * @return array
     */
    public function getAllCenterMenu()
    {
        $menu = array(
            'type' => 2,
            'list' => array(
                array(
                    'id' => 1,
                    'name' => __('我的拼团'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#DB384C',
                    'icon' => 'icon-gouwu',
                    'FeatureKey' => 'FightGrp',
                    'url' => '/activity/fightgroup/order',
                ),
                /*array(
                    'id' => 2,
                    'name' => __('分销佣金'),
                    'isShow' => intval(Base_ConfigModel::getConfig('plantform_fx_enable', 0)) ? true : false,
                    'cat' => 2,
                    'color' => '#44afa4',
                    'icon' => 'icon-xiaojinku',
                    'FeatureKey' => 'MemCashAcct',
                    'url' => '/member/fans/profitlist',
                ),
                array(
                    'id' => 3,
                    'name' => __('我的预约'),
                    'isShow' => true,
                    'cat' => 2,
                    'color' => '#44afa4',
                    'icon' => 'icon-shijian',
                    'FeatureKey' => '',
                    'url' => '/member/order/list?kind_id=1202',
                ),*/
                array(
                    'id' => 36,
                    'name' => __('售后服务'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#44afa4',
                    'icon' => 'zc zc-tuihuanhuo',
                    'FeatureKey' => 'service',
                    'url' => '/member/member/returnlist',
                ),
                array(
                    'id' => 4,
                    'name' => __('我的砍价'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#ffc333',
                    'icon' => 'icon-kanjia',
                    'FeatureKey' => 'CutPrice',
                    'url' => '/activity/cutprice/userlist',
                ),
                /*array(
                    'id' => 45,
                    'name' => __('账户余额'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#DB384C',
                    'icon' => 'icon-youhuiquan',
                    'FeatureKey' => 'UserMoneyKey',
                    'url' => '/member/cash/predeposit',
                ),
                array(
                    'id' => 5,
                    'name' => __('优惠券'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#56ABE4',
                    'icon' => 'icon-youhuiquan',
                    'FeatureKey' => 'Coupon',
                    'url' => '/member/member/coupon',
                ),*/
                array(
                    'id' => 44,
                    'name' => __('签到'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#ffc333',
                    'icon' => 'icon-edit',
                    'FeatureKey' => 'MemSign',
                    'url' => '/member/member/sign',
                ),
                array(
                    'id' => 6,
                    'name' => __('会员中心'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#ffc333',
                    'icon' => 'icon-zuanshi',
                    'FeatureKey' => 'MemGrade',
                    'url' => '/member/member/task',
                ),
                /*array(
                    'id' => 7,
                    'name' => __('店铺收藏'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#7672B8',
                    'icon' => 'icon-store',
                    'FeatureKey' => 'FavProd',
                    'url' => '/member/member/favorites-store',
                ),*/
                array(
                    'id' => 107,
                    'name' => __('商品收藏'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#56ABE4',
                    'icon' => 'icon-liwu',
                    'FeatureKey' => 'FavProd',
                    'url' => '/member/member/favorites',
                ),
                array(
                    'id' => 108,
                    'name' => __('我的足迹'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#56ABE4',
                    'icon' => 'zc zc-zuji',
                    'FeatureKey' => 'FavProd',
                    'url' => '/member/member/browse',
                ),
                array(
                    'id' => 8,
                    'name' => __('收货地址'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#1BC2A6',
                    'icon' => 'icon-shouhuodizhi',
                    'FeatureKey' => 'UserAddress',
                    'url' => '/member/address/list',
                ),
                array(
                    'id' => 120,
                    'name' => __('开票信息'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#1BC2A6',
                    'icon' => 'zc-caiwukaipiao',
                    'FeatureKey' => 'UserInvoice',
                    'url' => '/member/invoice/list',
                ),
                array(
                    'id' => 121,
                    'name' => __('我的发票'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#1BC2A6',
                    'icon' => 'zc-kaipiao',
                    'FeatureKey' => 'OrderInvoice',
                    'url' => '/member/invoice/order',
                ),
                /*array(
                    'id' => 10,
                    'name' => __('我的小店'),
                    'isShow' => intval(Base_ConfigModel::getConfig('plantform_fx_enable', 0) && Base_ConfigModel::getConfig('Plugin_DistributionWeStore', 0)) ? true : false,
                    'cat' => 2,
                    'color' => '#327eac',
                    'icon' => 'zc zc-dianpu',
                    'FeatureKey' => 'WeStore',
                    'url' => '/pagesub/westore/index',
                ),*/
                array(
                    'id' => 21,
                    'name' => __('推广中心'),
                    'isShow' => intval($this->getConfig('plantform_fx_enable', false)) ? true : false,
                    'cat' => 1,
                    'color' => '#327eac',
                    'icon' => 'zc zc-fenxiao',
                    'FeatureKey' => 'fenxiao',
                    'url' => '/member/fans/index',
                ),
                /*array(
                    'id' => 33,
                    'name' => __('我的消息'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#b5dbaf',
                    'icon' => 'zc zc-message',
                    'FeatureKey' => 'Message',
                    'url' => '/member/member/message',
                ),
                array(
                    'id' => 31,
                    'name' => __('用户设置'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#7673db',
                    'icon' => 'zc zc-shezhi',
                    'FeatureKey' => 'Options',
                    'url' => '/member/member/options',
                ),*/
                array(
                    'id' => 32,
                    'name' => __('帮助'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#ac8dd5',
                    'icon' => 'zc zc-bangzhu',
                    'FeatureKey' => 'Help',
                    'url' => '/pagesub/article/list',
                ),
                /*array (
                    'id' => 33,
                    'name' => __('关于'),
                    'isShow' => false,
                    'cat' => 1,
                    'color' => '#b5dbaf',
                    'icon' => 'zc zc-guanyu',
                    'FeatureKey' => 'AbtUs',
                    'url' => '/pagesub/index/about',
                ),
                array (
                    'id' => 10,
                    'name' => __('用户反馈'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#DB384C',
                    'icon' => ' icon-yonghufankui1',
                    'FeatureKey' => '',
                    'url' => '/member/member/feedback',
                ),
                array(
                    'id' => 34,
                    'name' => __('商家中心'),
                    'isShow' => true,
                    'cat' => 2,
                    'color' => '#db384c',
                    'icon' => 'zc zc-dianpu',
                    'FeatureKey' => 'Seller',
                    'url' => '/seller/index/index',
                ),*/
                array(
                    'id' => 11,
                    'name' => __('清除缓存'),
                    'isShow' => true,
                    'cat' => 1,
                    'color' => '#DB384C',
                    'icon' => 'zc zc-qingchuhuancun',
                    'FeatureKey' => 'CleanCacheKey',
                    'url' => '',
                )
            )
        );

        $sourceType = isset($_GET['source_type']) ? (int)$_GET['source_type'] : null;
        $live_mode_aliyun = $this->getConfig('live_mode_aliyun', 0);

        if (!is_null($sourceType) && StateCode::SOURCE_TYPE_H5 !== $sourceType && $live_mode_aliyun == 1) {
            $menu['list'][] = array(
                'id' => 33,
                'name' => __('我的直播'),
                'isShow' => true,
                'cat' => 1,
                'color' => '#ac8dd5',
                'icon' => 'zc zc-zhibo',
                'FeatureKey' => 'Live',
                'url' => '/pagesub/livepush/add',
            );
        }

        if ($this->getConfig('Plugin_Paotui', 0)) {
            $menu['list'][] = array(
                'id' => 109,
                'name' => __('骑手大厅'),
                'isShow' => false,
                'cat' => 2,
                'color' => '#56ABE4',
                'icon' => 'zc zc-zuji',
                'FeatureKey' => 'FavProd',
                'url' => '/paotui/index/index',
            );
        }

        if ($this->getConfig('make_lang_package_enable', 0)) {
            $menu['list'][] = array(
                'id' => 35,
                'name' => __('翻译制作'),
                'isShow' => true,
                'cat' => 2,
                'color' => '#ac8dd5',
                'icon' => 'zc zc-zhibo',
                'FeatureKey' => 'ReloadLang',
                'url' => '',
            );
        }

        if ($this->getConfig('live_mode_xcx', 0)) {
            $menu['list'][] = array(
                'id' => 109,
                'name' => __("申请主播"),
                'isShow' => true,
                'cat' => 2,
                'color' => '#56ABE4',
                'icon' => 'zc zc-15',
                'FeatureKey' => "FavProd",
                'url' => '/xcxlive/anchor/apply',
            );

            $menu['list'][] = array(
                'id' => 993,
                'name' => __("创建房间"),
                'isShow' => true,
                'cat' => 2,
                'color' => '#56ABE4',
                'icon' => 'zc zc-fangjian',
                'FeatureKey' => 'FavProd',
                'url' => '/xcxlive/room/add',
            );

            $menu['list'][] = array(
                'id' => 33,
                'name' => __('房间列表'),
                'isShow' => true,
                'cat' => 2,
                'color' => '#56ABE4',
                'icon' => 'zc zc-fenlei1',
                'FeatureKey' => 'FavProd',
                'url' => '/xcxlive/room/list',
            );
        }

        return $menu;
    }


    /**
     * 读取初始化配置信息
     *
     * @param string $sourceUccCode
     * @return array 包含初始化配置信息的数组
     */
    public function getSiteInfo($sourceUccCode)
    {
        $keys = "site_name,site_meta_keyword,site_meta_description,site_version,copyright,icp_number,site_company_name,site_address,site_tel,account_login_bg,site_admin_logo,site_mobile_logo,site_pc_logo,date_format,time_format,cache_enable,cache_expire,site_status,advertisement_open,wechat_connect_auto,wechat_app_id,product_spec_edit,default_image,product_salenum_flag,b2b_flag,hall_b2b_enable,product_ziti_flag,plantform_fx_enable,plantform_fx_gift_point,plantform_fx_withdraw_min_amount,plantform_poster_bg,plantform_commission_withdraw_mode,product_poster_bg,live_mode_xcx,kefu_type_id,withdraw_received_day,withdraw_monthday,default_shipping_district,points_enable,voucher_enable,b2b_enable,chain_enable,edu_enable,hall_enable,multilang_enable,sns_enable,subsite_enable,supplier_enable";
        $objects = explode(',', $keys);
        $config_bases = $this->repository->findWhereIn('config_key', $objects);

        $res = [];
        foreach ($config_bases as $item) {
            switch ($item['config_datatype']) {
                case 'radio':
                case 'select':
                case 'number':
                    // 下拉选择框、单选框、数字
                    $res[$item['config_key']] = intval($item['config_value']);
                    break;
                default:
                    $res[$item['config_key']] = $item['config_value'];
                    break;
            }
        }

        // 订单状态
        $orderStateList = $this->getOrderStateList();
        $res["order_state_list"] = $orderStateList;

        // 支付渠道
        $paymentChannelList = $this->getPaymentChannelList();
        $res["payment_channel_list"] = $paymentChannelList;

        // 退款退货 卖家处理状态
        $returnStateList = $this->getReturnStateList();
        $res["return_state_list"] = $returnStateList;

        // 会员等级信息
        $user_level_map = [];
        $user_level_rate_map = [];
        $user_levels = $this->userLevelRepository->all()->toArray();
        foreach ($user_levels as $user_level) {
            $user_level_map[$user_level['user_level_id']] = $user_level['user_level_name'];
            $user_level_rate_map[$user_level['user_level_id']] = $user_level['user_level_rate'];
        }
        $res["user_level_map"] = $user_level_map;
        $res["user_level_rate_map"] = $user_level_rate_map;

        // 错误日志异常类型
        $ErrorTypeEnum = new ErrorTypeEnum();
        $res["error_type_list"] = $ErrorTypeEnum->getAllValues();

        // 用户中心菜单
        $userCenterMenu = $this->getUserCenterMenu();
        $res["user_center_menu"] = $userCenterMenu;

        // 项目版本
        $res["site_version"] = env('VERSION');

        return $res;
    }


    public function getPcHelp($key)
    {
        $data = $this->repository->getConfig($key);

        return ["page_pc_help" => $data];
    }


    /**
     * 支付宝支付配置信息
     * @return array
     */
    public function getAlipayConfig()
    {
        $root_bath = base_path();
        return [
            'alipay' => [
                'default' => [
                    'app_id' => $this->repository->getConfig('alipay_app_id'), // 必填-支付宝分配的 app_id
                    // 必填-应用私钥 字符串或路径
                    'app_secret_cert' => $this->repository->getConfig('alipay_rsa_private_key'),
                    'app_public_cert_path' => $root_bath . '/certs/alipay/appCertPublicKey_2021004121611368.crt', // 必填-应用公钥证书 路径
                    'alipay_public_cert_path' => $root_bath . '/certs/alipay/alipayCertPublicKey_RSA2.crt', // 必填-支付宝公钥证书 路径
                    'alipay_root_cert_path' => $root_bath . '/certs/alipay/alipayRootCert.crt', // 必填-支付宝根证书 路径
                    'return_url' => env('URL_PC') . '/front/pay/callback/alipayReturn',
                    'notify_url' => env('URL_PC') . '/front/pay/callback/alipayNotify',
                    'app_auth_token' => '',  // 选填-第三方应用授权token
                    'service_provider_id' => '', // 选填-服务商模式下的服务商 id，当 mode 为 Pay::MODE_SERVICE 时使用该参数
                    'mode' => 0, // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
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
    }


    /**
     * 微信支付配置信息
     * @return array
     */
    public function getWechatConfig()
    {
        $root_path = base_path();
        $config = [
            'wechat' => [
                'default' => [
                    'mch_id' => $this->repository->getConfig('wechat_pay_mchid'), // 必填-商户号-''
                    'mch_secret_key' => $this->repository->getConfig('wechat_pay_v3_key'), // 必填-v3商户秘钥
                    'mch_secret_cert' => $root_path . '/certs/wechat/apiclient_key.pem', // 必填-商户私钥 字符串或路径
                    'mch_public_cert_path' => $root_path . '/certs/wechat/apiclient_cert.pem',// 必填-商户公钥证书路径
                    'notify_url' => env('URL_PC') . '/front/pay/callback/wechatNotify', // 必填
                    'mp_app_id' => $this->repository->getConfig('wechat_app_app_id'), // 公众号app_id-wx449456ef15998b79
                    'mini_app_id' => '',// 选填-小程序 的 app_id
                    'app_id' => $this->repository->getConfig('wechat_app_app_id'), // 选填-app 的 app_id
                    /* 'sub_mp_app_id' => '',// 选填-服务商模式下，子公众号 的 app_id
                     'sub_app_id' => '', // 选填-服务商模式下，子 app 的 app_id
                     'sub_mini_app_id' => '',// 选填-服务商模式下，子小程序 的 app_id
                     'sub_mch_id' => '',// 选填-服务商模式下，子商户id
                     'wechat_public_cert_path' => [
                         '45F59D4DABF31918AFCEC556D5D2C6E376675D57' => __DIR__ . '/Cert/wechatpay_45F***D57.pem',
                     ],// 选填-微信平台公钥证书路径, optional，强烈建议 php-fpm 模式下配置此参数*/
                    'mode' => 0,// 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
                ]
            ],
            'logger' => [ // optional
                'enable' => true,
                'file' => $root_path . '/storage/logs/wechat.log',
                'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
                'type' => 'single', // optional, 可选 daily.
                'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
            ],
            'http' => [ // optional
                'throw' => true, // 状态码非 200、300 时是否抛出异常，默认为开启
                'timeout' => 5.0,
                'connect_timeout' => 5.0,
                // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
            ],
        ];

        return $config;
    }


    /**
     * 删除
     * @param $config_key
     * @return bool
     * @throws ErrorException
     */
    public function removeBase($config_key)
    {
        $row = $this->repository->getOne($config_key);
        if ($row['config_buildin']) {
            throw new ErrorException(__('系统内置，不可删除'));
        }

        $result = $this->repository->remove($config_key);
        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }
    }


    /**
     * 获取多语言配置信息
     *
     * @return array
     */
    public function listTranslateLang(): array
    {
        $data = [];

        // 获取启用的货币语言列表
        $currency_base_list = $this->currencyBaseRepository->find(['currency_status' => true], ['currency_default_lang' => 'DESC', 'currency_sort' => 'ASC']);
        if (empty($currency_base_list)) {
            return ['opt' => [], 'lang' => []];
        }

        $items = [];
        $default_lang = [];

        foreach ($currency_base_list as $item) {
            $currency_base = [
                'lang' => $item['currency_lang'],
                'currency_id' => $item['currency_id'],
                'symbol' => $item['currency_symbol_left'],
                'symbol_right' => $item['currency_symbol_right'],
                'label' => $item['currency_title'],
                'standard' => $item['currency_is_standard'],
                'img' => $item['currency_img']
            ];

            $items[] = $currency_base;

            // 判断是否为默认语言
            if ($item['currency_default_lang']) {
                $default_lang = $currency_base;
            }
        }

        // 如果未设置默认语言，选择第一个语言项
        $default_lang = $default_lang ?: $items[0];

        // 设置语言选项
        $data['opt'] = array_merge($default_lang, ['items' => $items]);

        // 获取语言标准
        $lang = [];
        $lang_standard_rows = $this->langStandardRepository->find(['frontend' => true]);

        foreach ($currency_base_list as $currency_base) {
            $currency_lang = $currency_base['currency_lang'] ?? '';
            if ($currency_lang === '' || $currency_lang === 'zh-CN') {
                continue;
            }

            if ($currency_lang === "en-US") {
                $currency_lang = "en-GB";
            } elseif ($currency_lang === "es-ES") {
                $currency_lang = "es-MX";
            }

            $lang_key = str_replace('-', '_', $currency_lang);
            $map_key = $this->underlineToCamel($lang_key);

            // 构建翻译映射
            $trans_map = [];
            foreach ($lang_standard_rows as $item) {
                if (!empty($item['zh_CN'])) {
                    $trans_map[$item['zh_CN']] = $item[$map_key] ?? null;
                }
            }

            $lang[$lang_key] = $trans_map;
        }

        $data['lang'] = $lang;

        return $data;
    }

    /**
     * 下划线转驼峰
     *
     * @param string $str 输入字符串
     * @return string
     */
    private function underlineToCamel(string $str): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $str))));
    }

}
