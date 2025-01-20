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


namespace Modules\Pay\Services;

use App\Support\LevelCode;
use App\Support\PointsType;
use App\Support\StateCode;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Pay\Repositories\Contracts\ConsumeRecordRepository;
use Modules\Pay\Repositories\Contracts\UserExpHistoryRepository;
use Modules\Pay\Repositories\Contracts\UserPointsHistoryRepository;
use Modules\Pay\Repositories\Contracts\UserResourceRepository;
use App\Exceptions\ErrorException;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use Illuminate\Support\Facades\DB;
use Modules\Account\Repositories\Contracts\UserLevelRepository;
use Modules\Sys\Services\NumberSeqService;

/**
 * Class UserResourceService.
 *
 * @package Modules\Pay\Services
 */
class UserResourceService extends BaseService
{
    private $userPointsHistoryRepository;
    private $configBaseRepository;
    private $userExpHistoryRepository;
    private $userLevelRepository;
    private $userInfoRepository;
    private $consumeRecordRepository;

    public function __construct(
        UserResourceRepository      $userResourceRepository,
        UserPointsHistoryRepository $userPointsHistoryRepository,
        ConfigBaseRepository        $configBaseRepository,
        UserExpHistoryRepository    $userExpHistoryRepository,
        UserLevelRepository         $userLevelRepository,
        UserInfoRepository          $userInfoRepository,
        ConsumeRecordRepository     $consumeRecordRepository
    )
    {
        $this->repository = $userResourceRepository;
        $this->userPointsHistoryRepository = $userPointsHistoryRepository;
        $this->configBaseRepository = $configBaseRepository;
        $this->userExpHistoryRepository = $userExpHistoryRepository;
        $this->userLevelRepository = $userLevelRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->consumeRecordRepository = $consumeRecordRepository;
    }


    /**
     * 修改用户余额
     * @param $request
     * @return true
     * @throws ErrorException
     */
    public function updateUserPoints($request)
    {
        $points = $request->get('points', 0);
        $user_id = $request->get('user_id', 0);
        $user_resource = $this->repository->getOne($user_id);
        if (empty($user_resource)) {
            throw new ErrorException(__('支付会员不存在'));
        }
        if ($points != 0) {
            try {
                if ($points < 0 && bcsub($user_resource['user_points'], abs($points), 2) < 0) {
                    throw new ErrorException(__('数值有误，用户积分不足'));
                }

                DB::beginTransaction();

                $desc = __('管理员调整');
                $this->points([
                    'user_id' => $user_id,
                    'points' => $points,
                    'points_type_id' => PointsType::POINTS_TYPE_OTHER,
                    'points_log_desc' => $desc
                ]);

                DB::commit();
                return true;
            } catch (\Exception $e) {
                DB::rollBack();
                throw new ErrorException(__('修改失败: ') . $e->getMessage());
            }
        } else {
            throw new ErrorException(__('修改数值不能为0'));
        }
    }


    /**
     * 修改用户积分
     * @param $request
     * @return true
     * @throws ErrorException
     */
    public function updateUserMoney($request)
    {
        $record_total = $request->get('record_total', 0);
        $user_id = $request->get('user_id', 0);
        $user_resource = $this->repository->getOne($user_id);
        if (empty($user_resource)) {
            throw new ErrorException(__('支付会员不存在'));
        }
        if ($record_total != 0) {
            try {
                DB::beginTransaction();

                //todo 1、增加流水记录
                $numberSeqService = app(NumberSeqService::class);
                $trade['record_total'] = $record_total;
                $trade['order_id'] = $numberSeqService->createNextSeq('CZ');
                $desc = __('管理员调整');
                $deposit = [
                    'payment_channel_id' => 0,
                    'deposit_subject' => $desc,
                    'deposit_body' => $desc,
                    'deposit_total_fee' => $record_total
                ];
                $this->consumeRecordRepository->addConsumeRecord($user_id, $trade, $deposit);

                //todo 2、调整余额
                if ($record_total > 0) {
                    $this->repository->incrementFieldByIds([$user_id], 'user_money', $record_total);
                } else {
                    if (bcsub($user_resource['user_money'], abs($record_total), 2) < 0) {
                        throw new ErrorException(__('金额有误，用户余额不足'));
                    }
                    $this->repository->decrementFieldByIds([$user_id], 'user_money', abs($record_total));
                }

                DB::commit();
                return true;
            } catch (\Exception $e) {
                DB::rollBack();
                throw new ErrorException(__('修改失败: ') . $e->getMessage());
            }
        } else {
            throw new ErrorException(__('修改金额不能为0'));
        }
    }


    /**
     * 获取签到积分日志
     * @param $user_id
     * @return int
     */
    public function getSignState($user_id)
    {
        $cur_date = getCurDate();

        // 登录签到，每天只可以触发一次
        $user_points_history_num = $this->userPointsHistoryRepository->getNum([
            'user_id' => $user_id,
            'points_type_id' => PointsType::POINTS_TYPE_LOGIN,
            'points_log_date' => $cur_date
        ]);

        return $user_points_history_num > 0 ? 1 : 0;
    }


    /**
     * 用户签到
     * @param $user_id
     * @return array
     * @throws ErrorException
     */
    public function sign($user_id)
    {
        $points_login = $this->configBaseRepository->getConfig("points_login", 0);
        $desc = __("签到获取积分 :points", ['points' => $points_login]);

        //执行积分相关操作
        $points_data = [
            'user_id' => $user_id,
            'points' => $points_login,
            'points_type_id' => PointsType::POINTS_TYPE_LOGIN,
            'points_log_desc' => $desc
        ];
        $this->points($points_data);

        //执行经验值相关操作
        $exp_login = $this->configBaseRepository->getConfig("exp_login", 0);
        $this->experience([
            'user_id' => $user_id,
            'exp' => $exp_login,
            'exp_type_id' => LevelCode::EXP_TYPE_LOGIN,
            'desc' => ''
        ]);

        return $points_data;
    }


    /**
     * 签到日志类型数据
     * @param $row
     * @return bool
     * @throws ErrorException
     */
    public function points($row = [])
    {
        $date = getCurDate();

        switch ($row['points_type_id']) {
            case PointsType::POINTS_TYPE_REG:

                //是否发放注册积分判断
                $count = $this->userPointsHistoryRepository->getNum([
                    'user_id' => $row['user_id'],
                    'points_type_id' => $row['points_type_id']
                ]);
                if ($count > 0) {
                    throw new ErrorException(__('已经注册'));
                }

                $points_kind_id = PointsType::POINTS_ADD;
                break;
            case PointsType::POINTS_TYPE_LOGIN:

                //是否发放签到积分判断
                $num = $this->userPointsHistoryRepository->getNum([
                    'user_id' => $row['user_id'],
                    'points_type_id' => $row['points_type_id'],
                    'points_log_date' => $date
                ]);
                if ($num > 0) {
                    throw new ErrorException(__('已经签到'));
                }

                $points_kind_id = PointsType::POINTS_ADD;
                break;
            case PointsType::POINTS_TYPE_EXCHANGE_PRODUCT:
            case PointsType::POINTS_TYPE_EXCHANGE_VOUCHER:
            case PointsType::POINTS_TYPE_EXCHANGE_SP:
            case PointsType::POINTS_TYPE_TRANSFER_MINUS:
            case PointsType::POINTS_TYPE_DEDUCTION:
                $points_kind_id = PointsType::POINTS_MINUS;
                break;
            default:
                if ($row['points'] > 0) {
                    $points_kind_id = PointsType::POINTS_ADD;
                } else {
                    $points_kind_id = PointsType::POINTS_MINUS;
                }
                break;
        }

        $points = abs($row['points']); // 取正数
        $data = [
            'points_kind_id' => $points_kind_id,
            'points_type_id' => $row['points_type_id'],
            'user_id' => $row['user_id'],
            'points_log_points' => $points,
            'points_log_desc' => $row['points_log_desc'],
            'points_log_date' => $date,
            'points_log_time' => getTime(),
            'store_id' => 0,
            'user_id_other' => 0,
        ];

        return $this->addPoints($data);
    }


    /**
     * 变更用户积分 & 增加积分日志
     * @param $history
     * @return true
     * @throws ErrorException
     */
    public function addPoints($history)
    {
        $user_id = $history['user_id'];
        $points_log_points = $history['points_log_points'];

        // points_log_points 均为正数，增减由 points_kind_id 控制和判断
        if ($history['points_kind_id'] === PointsType::POINTS_ADD) {
            $change_points = $points_log_points;
        } else {
            $change_points = -$points_log_points;
        }

        // 获取用户当前积分
        $user_resource = $this->repository->getOne($user_id);
        if ($change_points < 0 && $user_resource['user_points'] < -$change_points) {
            throw new ErrorException(__('积分不足'));
        }

        DB::beginTransaction();

        // todo 1、变更用户积分
        $user_points = $user_resource['user_points'] + $change_points;
        if (!$this->repository->edit($user_id, ['user_points' => $user_points])) {
            DB::rollBack();
            throw new ErrorException(__('修改积分数据失败！'));
        }

        //todo 2、增加积分日志
        $history['user_points'] = $user_resource['user_points'];
        if (!$this->userPointsHistoryRepository->add($history)) {
            DB::rollBack();
            throw new ErrorException(__('保存积分日志失败！'));
        }

        DB::commit();

        return true;
    }


    /**
     * @param $exp_type_id
     * @param $user_id
     * @param $cur_date
     * @return void
     * @throws ErrorException
     */
    protected function handleLevelCode($exp_type_id, $user_id, $cur_date)
    {
        // 通用判断， 注册和
        switch ($exp_type_id) {
            case LevelCode::EXP_TYPE_REG:
                // 注册只可以触发一次
                $num = $this->userExpHistoryRepository->getNum([
                    'user_id' => $user_id,
                    'exp_type_id' => $exp_type_id
                ]);

                if ($num > 0) {
                    throw new ErrorException(__('已经发放'));
                }
                break;

            case LevelCode::EXP_TYPE_LOGIN:
                // 登录，每天只可以触发一次
                $num = $this->userExpHistoryRepository->getNum([
                    'user_id' => $user_id,
                    'exp_type_id' => $exp_type_id,
                    'exp_log_date' => $cur_date
                ]);

                if ($num > 0) {
                    throw new ErrorException(__('已经发放'));
                }
                break;
            /*case LevelCode::EXP_TYPE_EVALUATE_PRODUCT:
                break;
            case LevelCode::EXP_TYPE_EVALUATE_STORE:
                break;
            case LevelCode::EXP_TYPE_CONSUME:
                break;
            case LevelCode::EXP_TYPE_OTHER:
                break;
            case LevelCode::EXP_TYPE_EXCHANGE_PRODUCT:
                break;
            case LevelCode::EXP_TYPE_EXCHANGE_VOUCHER:
                break;
            default:
                break;*/
        }
    }


    /**
     * @param $experience_row
     * @return mixed
     * @throws ErrorException
     */
    public function experience($experience_row)
    {
        $cur_date = getCurDate();
        $exp_kind_id = $experience_row['exp'] > 0 ? 1 : 2;

        $this->handleLevelCode($experience_row['exp_type_id'], $experience_row['user_id'], $cur_date);

        // 日志经验数据
        $exp_history = [
            'exp_kind_id' => $exp_kind_id, // 类型(ENUM):1-获取;2-消费;
            'exp_type_id' => $experience_row['exp_type_id'], // 类型
            'user_id' => $experience_row['user_id'], // 会员编号
            'exp_log_value' => max($experience_row['exp'], 0), // 改变值
            'exp_log_desc' => $experience_row['desc'], // 描述
            'exp_log_time' => getDateTime(), // 时间
            'exp_log_date' => $cur_date, // 日期
        ];

        return $this->addExp($exp_history);
    }


    /**
     * @param $exp_history
     * @return true
     * @throws ErrorException
     */
    public function addExp($exp_history = [])
    {
        $user_id = $exp_history['user_id'];

        // 获取用户当前经验值
        $user_resource = $this->repository->getOne($user_id);
        $user_exp = $user_resource['user_exp'] ?? 0;
        $exp_history['user_exp'] = $user_exp;

        $user_resource['user_exp'] += $exp_history['exp_log_value'];

        DB::beginTransaction();

        // todo 1、更新用户经验值
        if (!$this->repository->edit($user_id, ['user_exp' => $user_resource['user_exp']])) {
            DB::rollBack();
            throw new ErrorException(__('修改经验值数据失败！'));
        }

        // todo 2、保存经验值日志
        if (!$this->userExpHistoryRepository->add($exp_history)) {
            DB::rollBack();
            throw new ErrorException(__('保存经验值日志失败！'));
        }

        // todo 根据经验值判断用户是否需要升级
        $user_ext_val = $user_resource['user_exp'];
        $user_level = $this->userLevelRepository->findOne([
            ['user_level_exp', '<=', $user_ext_val],
            ['user_level_exp', '>', 0]
        ]);
        if ($user_level) {
            $user_info = $this->userInfoRepository->getOne($user_id);

            // 判断是否需要更新用户等级
            if ($user_info['user_level_id'] < $user_level['user_level_id']) {
                // 更新用户等级
                if (!$this->userInfoRepository->edit($user_id, ['user_level_id' => $user_level['user_level_id']])) {
                    DB::rollBack();
                    throw new ErrorException(__('用户等级更新失败！'));
                }
            }
        }

        DB::commit();

        return true;
    }


    /**
     * 获取签到信息
     * @param $user_id
     * @return int[]
     */
    public function getSignInfo($user_id = 0)
    {
        // 获取本月开始与结束时间
        $time_range = getMonth();
        $start_time = $time_range['start'];
        $end_time = $time_range['end'];

        $points_history_list = array_values($this->userPointsHistoryRepository->find([
            'user_id' => $user_id,
            'points_type_id' => PointsType::POINTS_TYPE_LOGIN,
            ['points_log_time', '>=', $start_time],
            ['points_log_time', '<=', $end_time]
        ]));

        $today_is_sign = 0;  // 今日是否签到
        $sign_day_arr = []; // 已签到日期集合
        $continue_sign_days = 0; // 连续签到标识
        $count_days_end = true; // 判断连续签到情况
        $today_date = getCurDate(); // 获取当天日期 yyyy-mm-dd

        foreach ($points_history_list as $user_points_history) {
            $points_log_date = $user_points_history['points_log_date'];
            $sign_day_arr[] = $points_log_date;

            // 判断今日是否已签到
            if ($today_date == $points_log_date) {
                $today_is_sign = 1;
            }

            // 判断连续签到情况
            if ($count_days_end) {
                if ($points_log_date != $today_date) {
                    $day_difference = (strtotime($today_date) - strtotime($points_log_date)) / 86400;
                    if ($day_difference == 1) {
                        $continue_sign_days++;
                    } else {
                        $count_days_end = false;
                    }
                } else {
                    $continue_sign_days++;
                }

                // 更新今天的日期为当前记录的日期
                $today_date = $points_log_date;
            }
        }

        // 处理签到点数列表
        $sign_point_step = $this->configBaseRepository->getConfig('sign_point_step');
        $step_rows = [];
        if (!empty($sign_point_step)) {
            $step_rows = json_decode($sign_point_step, true);
        }

        $sign_info = [
            'today_is_sign' => $today_is_sign,
            'continue_sign_days' => $continue_sign_days,
            'sign_list' => $this->dealSignPointList($step_rows),
            'sign_day_arr' => $sign_day_arr
        ];

        return $sign_info;
    }


    /**
     * 格式化数据
     * @param $step_rows
     * @return array
     */
    public function dealSignPointList($step_rows = [])
    {
        array_multisort(array_column($step_rows, 'days'), SORT_ASC, $step_rows);

        $days_list = [];
        $hasOne = false;
        $points = $this->configBaseRepository->getConfig('points_login', 0);

        foreach ($step_rows as $step_row) {
            if ($step_row['days'] == 1) {
                $hasOne = true;
                $days_list[] = ['days' => $step_row['days'], 'value_str' => $points . '积分'];
            } else {
                $days_list[] = ['days' => $step_row['days'], 'value_str' => $step_row['times'] . '倍'];
            }
        }

        if (!$hasOne) {
            array_unshift($days_list, ['days' => 1, 'value_str' => $points . '积分']);
        }

        return $days_list;
    }


    /**
     * 注册初始化用户积分
     * @param $user_id
     * @return true
     * @throws ErrorException
     */
    public function initUserPoints($user_id)
    {
        // 创建用户资源
        $this->repository->add([
            'user_id' => $user_id
        ]);

        // 获取注册积分配置
        $points_reg = $this->configBaseRepository->getConfig('points_reg', 0);
        $desc = sprintf(__('注册赠送积分 %d'), $points_reg);

        // 创建用户积分记录
        $user_points_row = [
            'user_id' => $user_id,
            'points' => $points_reg,
            'points_type_id' => PointsType::POINTS_TYPE_REG,
            'points_log_desc' => $desc
        ];

        // 保存用户积分
        $this->points($user_points_row);

        return true;
    }


    /**
     * 初始化用户经验等级
     * @param $user_id
     * @return true
     * @throws ErrorException
     */
    public function initUserExperience($user_id)
    {
        // 获取注册经验值配置
        $exp_reg = $this->configBaseRepository->getConfig('exp_reg', 0);

        // 创建用户经验记录
        $experience_row = [
            'user_id' => $user_id,
            'exp' => $exp_reg,
            'exp_type_id' => LevelCode::EXP_TYPE_REG,
            'desc' => __('用户注册')
        ];

        // 保存用户经验信息
        $this->experience($experience_row);

        return true;
    }


}
