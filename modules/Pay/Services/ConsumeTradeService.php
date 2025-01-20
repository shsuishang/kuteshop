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

use App\Support\StateCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Kuteshop\Core\Service\BaseService;
use Modules\Pay\Repositories\Contracts\ConsumeRecordRepository;
use Modules\Pay\Repositories\Contracts\ConsumeTradeRepository;
use App\Exceptions\ErrorException;
use Modules\Pay\Repositories\Contracts\UserPayRepository;
use Modules\Pay\Repositories\Contracts\UserResourceRepository;
use Modules\Trade\Services\OrderService;

/**
 * Class ConsumeTradeService.
 *
 * @package Modules\Pay\Services
 */
class ConsumeTradeService extends BaseService
{
    private $consumeRecordRepository;
    private $userResourceRepository;
    private $userPayRepository;
    private $orderService;

    public function __construct(
        ConsumeTradeRepository  $consumeTradeRepository,
        ConsumeRecordRepository $consumeRecordRepository,
        UserResourceRepository  $userResourceRepository,
        UserPayRepository       $userPayRepository,
        OrderService            $orderService
    )
    {
        $this->repository = $consumeTradeRepository;
        $this->consumeRecordRepository = $consumeRecordRepository;
        $this->userResourceRepository = $userResourceRepository;
        $this->userPayRepository = $userPayRepository;
        $this->orderService = $orderService;
    }


    /**
     * 余额支付
     * @param $user_id
     * @param $req
     * @return array
     * @throws ErrorException
     */
    public function processMoneyPayment($user_id, $req)
    {
        $res = [];
        $res['order_id'] = $order_id = $req['order_id'];

        // 获取用户资源信息
        $user_resource = $this->userResourceRepository->getOne($user_id);

        // 判断余额是否足够支付订单
        if ($user_resource && $user_resource['user_money'] >= $req['pm_money']) {
            // 校验支付密码
            $this->checkPayPasswd($user_id, $req['password']);

            // 处理订单支付结果
            $pay_info = [];
            $pay_info['payment_met_id'] = StateCode::PAYMENT_MET_MONEY;
            $pay_info['payment_channel_id'] = $req['payment_channel_id'];
            $pay_info['payment_type_id'] = $req['deposit_payment_type'];
            $pay_info['pm_money'] = $req['pm_money'];

            $process_pay_res = $this->processPay($order_id, $pay_info);
            if ($process_pay_res) {
                $res['paid'] = true;
                $res['status_code'] = 200;
            } else {
                $res['status_code'] = 250;
            }
        } else {
            $res['paid'] = false;
            $res['status_code'] = 250;
        }

        return $res;
    }


    /**
     * 检验支付密码
     * @param $user_id
     * @param $password
     * @return void
     * @throws ErrorException
     */
    public function checkPayPasswd($user_id, $password)
    {
        $user_pay = $this->userPayRepository->getOne($user_id);

        if (!empty($user_pay) && $user_pay['user_pay_passwd']) {
            $user_salt = $user_pay['user_pay_salt'];
            $hash_password = Hash::make($user_salt . md5($password));
            if ($hash_password !== $user_pay['user_pay_passwd']) {
                // 密码不匹配
                throw new ErrorException(__('支付密码错误！'));
            }
        } else {
            // 用户支付密码不存在或为空
            throw new ErrorException(__('支付密码不存在！'));
        }
    }


    /**
     * 商城资源支付
     * @param $order_id_str
     * @param $deposit
     * @return array|bool
     * @throws ErrorException
     */
    public function processPay($order_id_str, $deposit)
    {
        $order_ids = explode(',', $order_id_str);
        $trades = $this->repository->find([['order_id', 'IN', $order_ids]]);
        if (empty($trades)) {
            throw new ErrorException(__('获取交易订单失败'));
        }

        $deposit_total_fee = 0;
        $flag_row = [];

        //todo 根据支付方式选择对应的 deposit_total_fee
        switch ($deposit['payment_met_id']) {
            case StateCode::PAYMENT_MET_MONEY:
                $deposit_total_fee = $deposit['pm_money'];
                break;
            case StateCode::PAYMENT_MET_POINTS:
                $deposit_total_fee = $deposit['pm_points'];
                break;
            case StateCode::PAYMENT_MET_CREDIT:
                $deposit_total_fee = $deposit['pm_credit'];
                break;
            case StateCode::PAYMENT_MET_RECHARGE_CARD:
                $deposit_total_fee = $deposit['pm_recharge_card'];
                break;
            default:
                throw new ErrorException(__('支付渠道不合法'));
        }

        DB::beginTransaction();

        foreach ($trades as $trade) {
            $order_id = $trade['order_id'];

            $trade_data = [];
            $trade_data['trade_paid_time'] = getTime();

            if (bccomp($deposit_total_fee, 0, 2) > 0 && $trade['trade_is_paid'] != StateCode::ORDER_PAID_STATE_YES) {
                // 当前订单需要支付额度
                $trade_payment_amount = $trade['trade_payment_amount'];
                if (bccomp($deposit_total_fee, $trade_payment_amount, 2) >= 0) {
                    //todo 更改订单状态，可以完成订单支付状态
                    $trade_data['trade_is_paid'] = StateCode::ORDER_PAID_STATE_YES;
                    $trade_data['payment_channel_id'] = $deposit['payment_channel_id'];
                    $trade_data['trade_payment_amount'] = 0;
                    $trade_data['trade_payment_money'] = bcadd($trade['trade_payment_money'], $trade_payment_amount, 2);

                    $flag_row[] = $this->repository->edit($trade['consume_trade_id'], $trade_data);

                    $deposit_total_fee = bcsub($deposit_total_fee, $trade_payment_amount, 2);
                } else {
                    $trade_payment_amount = $deposit_total_fee;

                    // 订单处理 不够支付完成
                    $trade_data['trade_is_paid'] = StateCode::ORDER_PAID_STATE_PART;
                    $trade_data['trade_payment_amount'] = bcsub($trade['trade_payment_amount'], $trade_payment_amount, 2);
                    $trade_data['trade_payment_money'] = bcadd($trade['trade_payment_money'], $trade_payment_amount, 2);

                    $flag_row[] = $this->repository->edit($trade['consume_trade_id'], $trade_data);

                    $deposit_total_fee = 0;
                }

                if (StateCode::TRADE_TYPE_SHOPPING == $trade['trade_type_id']) {
                    //todo 1、增加买家流水记录
                    $trade['record_total'] = -$trade_payment_amount;
                    $flag_row[] = $this->consumeRecordRepository->addConsumeRecord($trade['buyer_id'], $trade, $deposit, $trade['trade_type_id']);

                    //todo 2、扣除买家余额
                    $flag_row[] = $this->userResourceRepository->decrementFieldByIds([$trade['buyer_id']], 'user_money', $trade_payment_amount);

                    // 卖家收益涉及佣金问题， 可以分多次付款，支付完成才扣佣金
                    if ($trade_data['trade_is_paid'] == StateCode::ORDER_PAID_STATE_YES) {
                        if (StateCode::PAYMENT_TYPE_OFFLINE == $deposit['payment_type_id']) {
                            $trade['record_commission_fee'] = 0;
                        } else {
                            $trade['record_commission_fee'] = $trade['order_commission_fee'];
                            $trade['record_money'] = bcsub($trade_payment_amount, $trade['order_commission_fee'], 2);
                        }
                    }

                    //todo 3、增加卖家流水记录
                    $trade['record_total'] = $trade_payment_amount;
                    $flag_row[] = $this->consumeRecordRepository->addConsumeRecord($trade['seller_id'], $trade, $deposit, StateCode::TRADE_TYPE_SALES);
                    if (StateCode::PAYMENT_TYPE_OFFLINE != $deposit['payment_type_id']) {
                        //todo 非线下支付，增加账户余额
                        $flag_row[] = $this->userResourceRepository->incrementFieldByIds([$trade['seller_id']], 'user_money', $trade['record_money']);
                    }

                } else {
                    $trade_data['trade_is_paid'] = StateCode::ORDER_PAID_STATE_YES;
                }

                if (StateCode::ORDER_PAID_STATE_YES == $trade_data['trade_is_paid']) {
                    //todo 更新订单状态
                    $flag_row[] = $this->orderService->setPaidYes($order_id);
                } else if (StateCode::ORDER_PAID_STATE_PART == $trade_data['trade_is_paid']) {
                    //todo 更新部分付款状态
                }
            }
        }

        if (is_ok($flag_row)) {
            DB::commit();
        } else {
            DB::rollBack();
        }

        return is_ok($flag_row);

    }


    public function getTradeInfo($order_id)
    {
        $trade_info = [];
        $order_ids = explode(',', $order_id);
        $trade_rows = $this->repository->find([['order_id', 'IN', $order_ids]]);
        $trade_title = implode('|', array_column($trade_rows, 'trade_title'));
        $trade_amount = array_sum(array_column($trade_rows, 'order_payment_amount'));

        $trade_info['trade_title'] = $trade_title;
        $trade_info['trade_amount'] = $trade_amount;

        return $trade_info;
    }


    /**
     * 检测交易支付状态
     * @param $order_id
     * @return array
     * @throws ErrorException
     */
    public function checkPaid($order_id = '')
    {
        $trade_info = $this->repository->findOne(['order_id' => $order_id]);
        if (!empty($trade_info)) {
            if ($trade_info['trade_is_paid'] == StateCode::ORDER_PAID_STATE_YES) {
                $data['paid'] = true;
            } else {
                $data['paid'] = false;
            }

            return $data;
        } else {
            throw new ErrorException(__('交易不存在'));
        }
    }

}
