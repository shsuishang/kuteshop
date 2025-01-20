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


namespace Modules\Shop\Services;

use App\Support\PointsType;
use App\Support\StateCode;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Marketing\Repositories\Contracts\ActivityBaseRepository;
use Modules\Pay\Repositories\Contracts\UserResourceRepository;
use Modules\Pay\Services\UserResourceService;
use Modules\Shop\Repositories\Contracts\UserVoucherNumRepository;
use Modules\Shop\Repositories\Contracts\UserVoucherRepository;
use App\Exceptions\ErrorException;

/**
 * Class UserVoucherService.
 *
 * @package Modules\Shop\Services
 */
class UserVoucherService extends BaseService
{
    private $userVoucherNumRepository;
    private $activityBaseRepository;
    private $userInfoRepository;
    private $userResourceRepository;

    public function __construct(
        UserVoucherRepository    $userVoucherRepository,
        UserVoucherNumRepository $userVoucherNumRepository,
        ActivityBaseRepository   $activityBaseRepository,
        UserInfoRepository       $userInfoRepository,
        UserResourceRepository   $userResourceRepository
    )
    {
        $this->repository = $userVoucherRepository;
        $this->userVoucherNumRepository = $userVoucherNumRepository;
        $this->activityBaseRepository = $activityBaseRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->userResourceRepository = $userResourceRepository;
    }


    /**
     * 我的优惠券列表
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list($criteria, $limit);

        //todo 优惠券状态
        if (!empty($data['data'])) {

            $user_ids = array_column($data['data'], 'user_id');
            $user_info_rows = $this->userInfoRepository->gets($user_ids);

            $current_time = getTime();
            foreach ($data['data'] as $k => $item) {
                $data['data'][$k]['voucher_effect'] = true;
                if ($item['voucher_state_id'] == StateCode::VOUCHER_STATE_UNUSED) {
                    if ($item['voucher_end_date'] <= $current_time) {
                        $this->repository->edit($item['user_voucher_id'], [
                            'voucher_state_id' => StateCode::VOUCHER_STATE_TIMEOUT
                        ]);

                        $data['data'][$k]['VOUCHER_STATE_TIMEOUT'] = StateCode::VOUCHER_STATE_TIMEOUT;
                    }

                    // 是否生效标记
                    if ($item['voucher_start_date'] > $current_time) {
                        $data['data'][$k]['voucher_effect'] = false;
                    }
                }

                if (isset($user_info_rows[$item['user_id']])) {
                    $data['data'][$k]['user_nickname'] = $user_info_rows[$item['user_id']]['user_nickname'];
                }

            }
        }

        return $data;
    }


    /**
     * 获取用户优惠券数量
     * @param $user_id
     * @return array
     */
    public function getEachVoucherNum($user_id)
    {
        $voucher_count_res = [];

        // 全部优惠券数量
        $voucher_count_res['voucher_all_num'] = $this->repository->getNum(['user_id' => $user_id]);

        // 未使用优惠券
        $voucher_count_res['voucher_unused_num'] = $this->repository->getNum([
            'user_id' => $user_id,
            'voucher_state_id' => StateCode::VOUCHER_STATE_UNUSED
        ]);


        // 已使用优惠券
        $voucher_count_res['voucher_used_num'] = $this->repository->getNum([
            'user_id' => $user_id,
            'voucher_state_id' => StateCode::VOUCHER_STATE_USED
        ]);

        // 已过期优惠券
        $voucher_count_res['voucher_timeout_num'] = $this->repository->getNum([
            'user_id' => $user_id,
            'voucher_state_id' => StateCode::VOUCHER_STATE_TIMEOUT
        ]);

        return $voucher_count_res;

    }


    /**
     * 领取优惠券
     * @param $activity_id
     * @param $user_id
     * @return array
     * @throws ErrorException
     */
    public function addVoucher($activity_id, $user_id)
    {
        DB::beginTransaction();

        try {

            $activity_base = $this->activityBaseRepository->getOne($activity_id);
            if (empty($activity_base)) {
                throw new ErrorException(__("活动不存在！"));
            }

            if ($activity_base['activity_state'] != StateCode::ACTIVITY_STATE_NORMAL) {
                throw new ErrorException(__("活动未开启,领取失败！"));
            }

            $activity_rule = $activity_base['activity_rule'];
            if (empty($activity_rule)) {
                throw new ErrorException(__("活动规则为空！"));
            }

            $voucher_rule = $activity_rule['voucher'];
            if (empty($voucher_rule)) {
                throw new ErrorException(__("活动优惠券信息为空！"));
            }
            $voucher_quantity = $voucher_rule['voucher_quantity'];
            $voucher_quantity_free = isset($voucher_rule['voucher_quantity_free']) ?? 0;
            if ($voucher_quantity + $voucher_quantity_free <= 0) {
                throw new ErrorException(__("代金券已经被抢完,领取失败！"));
            }
            $voucher_rule['voucher_quantity_free'] = $voucher_quantity_free;
            $voucher_pre_quantity = $voucher_rule['voucher_pre_quantity'];

            //todo 是否需要积分
            $need_points = 0;
            if (isset($activity_rule['requirement']) && isset($activity_rule['requirement']['points'])) {
                if ($activity_rule['requirement']['points']['needed'] > 0) {
                    $need_points = $activity_rule['requirement']['points']['needed'];
                    $user_resource = $this->userResourceRepository->getOne($user_id);
                    if (bcsub($user_resource['user_points'] - $need_points, 2) < 0) {
                        throw new ErrorException(__("当前积分不足，需要积分") . $need_points);
                    }
                }
            }

            //todo 用户等级信息校验
            if ($activity_base['activity_use_level']) {
                $user_level_ids = explode(',', $activity_base['activity_use_level']);
                $user_info = $this->userInfoRepository->getOne($user_id);
                if (!$user_info) {
                    throw new ErrorException(__("用户信息不存在！"));
                }

                if (!in_array($user_info['user_level_id'], $user_level_ids)) {
                    throw new ErrorException(__("不属于该优惠券指定的会员等级，领取失败！"));
                }
            }

            $voucher_row = [
                'user_voucher_time' => getDateTime(),
                'activity_id' => $activity_id,
                'user_id' => $user_id,
                'voucher_state_id' => StateCode::VOUCHER_STATE_UNUSED
            ];

            //todo 用户已领优惠券数量
            $user_voucher_num = 0;
            $uvn_id = 0;
            $user_voucher_num_rows = $this->userVoucherNumRepository->find(['activity_id' => $activity_id, 'user_id' => $user_id]);
            if (!empty($user_voucher_num_rows)) {
                $user_voucher_num_row = current($user_voucher_num_rows);
                $user_voucher_num = $user_voucher_num_row['uvn_num'];
                $uvn_id = $user_voucher_num_row['uvn_id'];
            }

            if ($user_voucher_num < $voucher_pre_quantity) {
                $requirement = $activity_rule['requirement'];
                $buy = $requirement['buy'];
                $item = $buy['item'];
                $subtotal = $buy['subtotal'];
                $voucher_price = $voucher_rule['voucher_price'];

                $voucher_row['voucher_subtotal'] = $subtotal;
                $voucher_row['voucher_price'] = $voucher_price;
                $voucher_row['voucher_start_date'] = $voucher_rule['voucher_start_date'];
                $voucher_row['voucher_end_date'] = $voucher_rule['voucher_end_date'];
                $voucher_row['store_id'] = $activity_base['store_id'];
                $voucher_row['activity_name'] = $activity_base['activity_name'];
                $voucher_row['activity_rule'] = $activity_base['activity_rule'];
                if (!empty($item)) {
                    $voucher_row['item_id'] = implode(',', $item);
                }

                $activity_type = $activity_base['activity_type'];

                //todo 添加优惠券
                $this->repository->add($voucher_row);

                //todo 更新用户优惠券领取数量信息
                if ($uvn_id) {
                    $num = $user_voucher_num + 1;
                    $this->userVoucherNumRepository->edit($uvn_id, ['uvn_num' => $num]);
                } else {
                    $this->userVoucherNumRepository->add([
                        'user_id' => $user_id,
                        'activity_id' => $activity_id,
                        'uvn_num' => 1
                    ]);
                }

                //todo 更新优惠券活动信息
                $voucher_quantity_use = 1;
                if (isset($voucher_rule['voucher_quantity_use'])) {
                    $voucher_rule['voucher_quantity_use']++;
                }
                $voucher_rule['voucher_quantity_use'] = $voucher_quantity_use;
                $voucher_rule['voucher_quantity_free'] -= 1;
                $activity_rule['voucher'] = $voucher_rule;
                $update_activity = ['activity_rule' => $activity_rule];
                if ($voucher_quantity <= $voucher_rule['voucher_quantity_use']) {
                    $update_activity['activity_state'] = StateCode::ACTIVITY_STATE_FINISHED;
                }
                $this->activityBaseRepository->edit($activity_id, $update_activity);

                if ($activity_type == StateCode::GET_VOUCHER_BY_POINT && $need_points > 0) {
                    // TODO: 积分操作
                    $userResourceService = app(UserResourceService::class);
                    $desc = sprintf(__('兑换优惠券 %d'), $need_points);

                    // 创建用户积分记录
                    $user_points_row = [
                        'user_id' => $user_id,
                        'points' => $need_points,
                        'points_type_id' => PointsType::POINTS_TYPE_EXCHANGE_VOUCHER,
                        'points_log_desc' => $desc
                    ];
                    $userResourceService->points($user_points_row);
                }
            } else {
                throw new ErrorException(__("领取数量超限！"));
            }

            /*$messageId = "coupons-to-the-accounts";
            $args = [
                'name' => $activityBase->activity_title,
                'endtime' => Carbon::parse($activityBase->activity_endtime)->format('Y-m-d H:i:s')
            ];
            $this->messageService->sendNoticeMsg($user_id, $messageId, $args);*/

            DB::commit();

            return $voucher_row;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('领取优惠券失败: ') . $e->getMessage());
        }
    }


    /**
     * 更新优惠券状态
     * @return void
     */
    public function updateVoucherState()
    {
        $time = getTime();
        $column_row = [
            'voucher_state_id' => StateCode::VOUCHER_STATE_UNUSED,
            ['voucher_end_date', '<', $time]
        ];
        $user_voucher_ids = $this->repository->findKey($column_row);
        if (!empty($user_voucher_ids)) {
            $this->repository->edits($user_voucher_ids, ['voucher_state_id' => StateCode::VOUCHER_STATE_TIMEOUT]);
        }
    }

}
