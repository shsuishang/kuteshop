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
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserBindConnectRepository;
use Modules\Account\Repositories\Contracts\UserDeliveryAddressRepository;
use Modules\Account\Repositories\Contracts\UserDistributionRepository;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Contracts\UserLevelRepository;
use Modules\Account\Repositories\Contracts\UserLoginRepository;
use Modules\Account\Repositories\Contracts\UserMessageRepository;
use Modules\Account\Repositories\Contracts\UserRepository;
use Modules\Account\Repositories\Contracts\UserTagBaseRepository;
use Modules\Account\Repositories\Contracts\UserTagGroupRepository;
use Modules\Admin\Repositories\Contracts\MenuBaseRepository;
use Modules\Admin\Repositories\Contracts\UserAdminRepository;
use Modules\Admin\Repositories\Contracts\UserRoleRepository;
use Modules\Analytics\Repositories\Models\AnalyticsOrder;
use Modules\Analytics\Repositories\Models\AnalyticsTrade;
use Modules\Pay\Repositories\Contracts\DistributionCommissionRepository;
use Modules\Pay\Repositories\Contracts\UserResourceRepository;
use Modules\Shop\Repositories\Contracts\UserFavoritesItemRepository;
use Modules\Shop\Repositories\Contracts\UserVoucherRepository;
use Modules\Trade\Repositories\Contracts\DistributionOrderRepository;
use Modules\Trade\Repositories\Contracts\OrderInfoRepository;

class UserInfoService extends BaseService
{

    private $userRepository;
    private $userAdminRepository;
    private $userResourceRepository;
    private $userRoleRepository;
    private $menuBaseRepository;
    private $userLevelRepository;
    private $userTagBaseRepository;
    private $userTagGroupRepository;
    private $userDeliveryAddressRepository;
    private $userLoginRepository;
    private $userDistributionRepository;
    private $distributionCommissionRepository;
    private $distributionOrderRepository;
    private $orderInfoRepository;
    private $userFavoritesItemRepository;
    private $userMessageRepository;
    private $userBindConnectRepository;
    private $userVoucherRepository;

    private $analyticsOrder;
    private $analyticsTrade;

    public function __construct(
        UserInfoRepository               $userInfoRepository,
        UserRepository                   $userRepository,
        UserResourceRepository           $userResourceRepository,
        UserAdminRepository              $userAdminRepository,
        UserRoleRepository               $userRoleRepository,
        MenuBaseRepository               $menuBaseRepository,
        UserVoucherRepository            $userVoucherRepository,
        UserLevelRepository              $userLevelRepository,
        UserTagBaseRepository            $userTagBaseRepository,
        UserTagGroupRepository           $userTagGroupRepository,
        UserDeliveryAddressRepository    $userDeliveryAddressRepository,
        UserLoginRepository              $userLoginRepository,
        UserDistributionRepository       $userDistributionRepository,
        DistributionCommissionRepository $distributionCommissionRepository,
        DistributionOrderRepository      $distributionOrderRepository,
        OrderInfoRepository              $orderInfoRepository,
        UserFavoritesItemRepository      $userFavoritesItemRepository,
        UserMessageRepository            $userMessageRepository,
        UserBindConnectRepository        $userBindConnectRepository,


        AnalyticsOrder                   $analyticsOrder,
        AnalyticsTrade                   $analyticsTrade
    )
    {
        $this->repository = $userInfoRepository;
        $this->userRepository = $userRepository;
        $this->userResourceRepository = $userResourceRepository;
        $this->userAdminRepository = $userAdminRepository;
        $this->userRoleRepository = $userRoleRepository;
        $this->menuBaseRepository = $menuBaseRepository;
        $this->userVoucherRepository = $userVoucherRepository;
        $this->userLevelRepository = $userLevelRepository;
        $this->userTagBaseRepository = $userTagBaseRepository;
        $this->userTagGroupRepository = $userTagGroupRepository;
        $this->userDeliveryAddressRepository = $userDeliveryAddressRepository;
        $this->userLoginRepository = $userLoginRepository;
        $this->userDistributionRepository = $userDistributionRepository;
        $this->distributionCommissionRepository = $distributionCommissionRepository;
        $this->distributionOrderRepository = $distributionOrderRepository;
        $this->orderInfoRepository = $orderInfoRepository;
        $this->userFavoritesItemRepository = $userFavoritesItemRepository;
        $this->userMessageRepository = $userMessageRepository;
        $this->userBindConnectRepository = $userBindConnectRepository;

        $this->analyticsOrder = $analyticsOrder;
        $this->analyticsTrade = $analyticsTrade;
    }


    /**
     * 获取登录用户信息
     * @return array
     * @throws ErrorException
     */
    public function getLoginUser()
    {
        $load = auth()->payload();
        $user_id = $load->get('user_id');
        if (!$user_id) {
            throw new ErrorException(__("Token失效，请重新登录"));
        }

        return [
            'user_id' => $user_id,
            'user_account' => $load->get('user_account'),
            'user_salt' => $load->get('user_salt')
        ];
    }


    /**
     * getUserInfo
     * @return array|mixed
     * @throws ErrorException
     */
    public function getUserInfo()
    {
        $login_user = $this->getLoginUser();
        $user_id = $login_user['user_id'];
        $user_base = $this->userRepository->getOne($user_id);

        if (empty($user_base)) {
            throw new ErrorException(__("账号不存在"));
        }

        //todo 判断token有效性
        if ($user_base['user_salt'] !== $login_user['user_salt']) {
            throw new ErrorException("Token失效，请重新登录");
        }

        //todo 获取userInfo信息
        $data = $this->repository->getOne($user_id);
        if ($data && $data['user_state'] == 0) {
            throw new ErrorException(__("您的账号已被禁用,请联系管理员"));
        }
        $data['user_idcard_image_list'] = explode(',', $data['user_idcard_images']);

        //todo 获取用户资产信息
        $user_resource = $this->userResourceRepository->getOne($user_id);
        if ($user_resource) {
            $data = array_merge($data, $user_resource);
            $data['user_points'] = round($user_resource['user_points'], 2);
        }

        //todo 用户管理表
        $admin_data = $this->getUserPermissions($user_id);
        if (!empty($admin_data)) {
            $data['roles'] = $admin_data['roles'];
            $data['permissions'] = $admin_data['permissions'];
        }

        //todo 获取用户未使用优惠券数量
        $data['voucher'] = $this->userVoucherRepository->getNum(['user_id' => $user_id, 'voucher_state_id' => StateCode::VOUCHER_STATE_UNUSED]);

        //佣金
        $data['commission_amount'] = 0;
        $distribution_commission_row = $this->distributionCommissionRepository->getOne($user_id);
        if ($distribution_commission_row) {
            $data['commission_amount'] = $distribution_commission_row['commission_amount'];
        }

        //待付款订单数量
        $data['wait_pay_num'] = $this->orderInfoRepository->getNum([
            'user_id' => $user_id,
            'order_state_id' => StateCode::ORDER_STATE_WAIT_PAY
        ]);

        //收藏数量
        $data['favorites_goods_num'] = $this->userFavoritesItemRepository->getNum(['user_id' => $user_id]);

        //未读消息数量
        $data['unread_number'] = $this->userMessageRepository->getNum(['user_id' => $user_id, 'message_is_read' => 0]);

        return $data;
    }


    /**
     * 获取用户当前角色权限
     * @param $user_id
     * @return array
     */
    public function getUserPermissions($user_id = null)
    {
        $data = [];

        $admin_user = $this->userAdminRepository->getOne($user_id);
        if (!empty($admin_user)) {
            //todo 获取用户的角色权限菜单
            $user_role_id = $admin_user['user_role_id'];
            $user_role_menu = $this->userRoleRepository->getOne($user_role_id);
            if (!empty($user_role_menu)) {
                $data['roles'] = [$user_role_menu['user_role_code']];

                //todo 获取菜单 menu_permission
                $menu_ids = explode(',', $user_role_menu['menu_ids']);
                $menu_rows = $this->menuBaseRepository->gets($menu_ids);
                $permission_rows = [];
                foreach ($menu_rows as $menu_row) {
                    if ($menu_row['menu_permission'] != '' && !in_array($menu_row['menu_permission'], $permission_rows)) {
                        $permission_rows[] = $menu_row['menu_permission'];
                    }
                }

                $data['permissions'] = $permission_rows;
            }
        }

        return $data;
    }


    /**
     * getUserData
     * @param $user_id
     * @return array|mixed
     */
    public function getUserData($user_id = null)
    {
        $user_data = [];

        // 用户基本信息
        $user_base = $this->userRepository->getOne($user_id);
        if (!empty($user_base)) {
            $user_data = $user_base;
        }

        // 用户详情信息
        $user_info = $this->repository->getOne($user_id);
        if (!empty($user_info)) {
            $user_data = array_merge($user_data, $user_info);

            // 身份证图片
            if ($user_info['user_idcard_images']) {
                $user_data['user_idcard_image_list'] = explode(',', $user_info['user_idcard_images']);
            }

            // 用户等级
            $user_level = $this->userLevelRepository->getOne($user_info['user_level_id']);
            if (!empty($user_level)) {
                $user_data['user_level_name'] = $user_level['user_level_name'];
            }

            // 用户标签和分组
            if ($user_info['tag_ids']) {
                $tag_ids = explode(',', $user_info['tag_ids']);
                $tag_rows = $this->userTagBaseRepository->gets($tag_ids);

                if (!empty($tag_rows)) {
                    $tag_title_rows = array_column($tag_rows, 'tag_title');
                    $user_data['tag_titles'] = implode('、', $tag_title_rows);

                    $tag_group_ids = array_column($tag_rows, 'tag_group_id');
                    $tag_group_rows = $this->userTagGroupRepository->gets($tag_group_ids);
                    if (!empty($tag_group_rows)) {
                        $group_name_rows = array_column($tag_group_rows, 'tag_group_name');
                        $user_data['tag_group_names'] = implode('、', $group_name_rows);
                    }
                }
            }
        }

        // 本月订单统计
        $order_state_ids = [
            StateCode::ORDER_STATE_WAIT_PAY,
            StateCode::ORDER_STATE_WAIT_PAID,
            StateCode::ORDER_STATE_WAIT_REVIEW,
            StateCode::ORDER_STATE_WAIT_FINANCE_REVIEW,
            StateCode::ORDER_STATE_PICKING,
            StateCode::ORDER_STATE_WAIT_SHIPPING,
            StateCode::ORDER_STATE_SHIPPED,
            StateCode::ORDER_STATE_RECEIVED,
            StateCode::ORDER_STATE_FINISH,
            StateCode::ORDER_STATE_SELF_PICKUP
        ];
        $month_range = getMonth();
        $month_order_num = $this->analyticsOrder->getOrderNum($month_range['start'], $month_range['end'], $order_state_ids, [], $user_id);
        $user_data['month_order'] = $month_order_num;
        // 总计订单数目
        $total_order_num = $this->analyticsOrder->getOrderNum(0, 0, $order_state_ids, [], $user_id);

        $user_data['total_order'] = $total_order_num;

        // 本月消费金额
        $user_data['month_trade'] = $this->analyticsTrade->getTradeAmount($month_range['start'], $month_range['end'], [], [], $user_id);
        // 总消费金额
        $user_data['total_trade'] = $this->analyticsTrade->getTradeAmount(0, 0, [], [], $user_id);;

        // 用户地址
        $address = $this->userDeliveryAddressRepository->getOne($user_id);
        if ($address) {
            $user_data['ud_address'] = $address['ud_province'] . $address['ud_city'] . $address['ud_county'] . $address['ud_address'];
        }

        // 用户资源
        $user_resource = $this->userResourceRepository->getOne($user_id);
        if ($user_resource) {
            $user_data = array_merge($user_data, $user_resource);
        }

        // 用户登录信息
        $user_login = $this->userLoginRepository->getOne($user_id);
        if ($user_login) {
            $user_data['user_reg_time'] = $user_login['user_reg_time'];
            $user_data['user_login_time'] = $user_login['user_lastlogin_time'];
        }

        // 推广员信息
        $user_distribution = $this->userDistributionRepository->getOne($user_id);
        if ($user_distribution) {
            $user_data['user_parent_id'] = $user_distribution['user_parent_id'];
        }

        // 累计佣金
        $user_data['user_commission_now'] = 0;
        $distribution_commission = $this->distributionCommissionRepository->getOne($user_id);
        if ($distribution_commission) {
            $user_data['user_commission_now'] = $distribution_commission['commission_amount'] - $distribution_commission['commission_settled'];
        }

        // 本月佣金
        $user_data['month_commission_buy'] = 0;
        $month_commission_orders = $this->distributionOrderRepository->find([
            'user_id' => $user_id,
            ['uo_time', '>=', $month_range['start']],
            ['uo_time', '<=', $month_range['end']]
        ]);
        if (!empty($month_commission_orders)) {
            $user_data['month_commission_buy'] = array_sum(array_column($month_commission_orders, 'uo_buy_commission'));
        }

        return $user_data;
    }


    /*
     * passWordEdit
     * @param $user_id
     * @param $user_password
     */
    public function passWordEdit($user_id = 0, $user_password = '')
    {
        if (!$user_id) {
            throw new ErrorException(__("用户Id不能为空"));
        }

        if (!$user_password) {
            throw new ErrorException(__("密码不能为空"));
        }

        try {
            $result = $this->userRepository->setUserPassword($user_id, $user_password);

            return $result;
        } catch (\Exception $e) {
            throw new ErrorException(__("密码修改失败"));
        }

    }


    /**
     * 用户修改基本信息
     * @param $user_id
     * @param $request
     * @return mixed
     */
    public function editUserInfo($user_id, $request)
    {
        $result = $this->repository->edit($user_id, [
            'user_nickname' => $request->input('user_nickname', ''),
            'user_avatar' => $request->input('user_avatar', ''),
            'user_email' => $request->input('user_email', ''),
            'user_birthday' => $request->input('user_birthday', ''),
        ]);

        return $result;
    }


    /**
     * 删除用户账号
     *
     * @param int $user_id
     * @return bool
     * @throws ErrorException
     */
    public function removeUser(int $user_id = 0)
    {
        $user_admin = $this->userAdminRepository->getOne($user_id);
        if (!empty($user_admin) && $user_admin['user_is_superadmin']) {
            throw new ErrorException(__("该账号为系统管理员，不可删除！"));
        }
        DB::beginTransaction();

        try {
            $this->userRepository->remove($user_id);
            $this->repository->remove($user_id);
            $this->userLoginRepository->remove($user_id);
            $this->userResourceRepository->remove($user_id);

            // 删除用户绑定连接
            $this->userBindConnectRepository->removeWhere(['user_id' => $user_id]);


            // 分销相关用户来源关系
            $user_distribution = $this->userDistributionRepository->getOne($user_id);
            if (!empty($user_distribution)) {
                if (!$this->userDistributionRepository->remove($user_id)) {
                    throw new ErrorException(__("删除粉丝信息失败！"));
                }

                // 修改上级用户粉丝数量
                $parent_distribution = $this->userDistributionRepository->getOne($user_distribution['user_parent_id']);
                if (!empty($parent_distribution)) {
                    $fans_num = max($parent_distribution['user_fans_num'] - 1, 0);
                    if (!$this->userDistributionRepository->edit($user_distribution['user_parent_id'], ['user_fans_num' => $fans_num])) {
                        throw new ErrorException(__("修改粉丝数量失败！"));
                    }
                }
            }

            // 删除推广粉丝产生的佣金汇总表
            //$this->distributionGeneratedCommissionRepository->removeWhere(['user_id' => $user_id]);

            // 删除讲师

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('删除用户操作失败: ') . $e->getMessage());
        }

        return true;
    }


    /**
     * 绑定用户信息
     * @param array $items
     * @param array $map
     * @param string $key_name
     * @return array
     */
    public function fixUserInfo(array $items = [], array $map = ["user_nickname" => "user_nickname"], string $key_name = "user_id"): array
    {
        return $this->repository->fixUserInfo($items, $map, $key_name);
    }


}
