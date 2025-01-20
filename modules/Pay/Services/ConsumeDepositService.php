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
use Kuteshop\Core\Service\BaseService;
use Modules\Pay\Repositories\Contracts\ConsumeDepositRepository;
use App\Exceptions\ErrorException;
use Modules\Pay\Repositories\Contracts\ConsumeRecordRepository;
use Modules\Pay\Repositories\Contracts\ConsumeTradeRepository;
use Modules\Pay\Repositories\Contracts\UserResourceRepository;

/**
 * Class ConsumeDepositService.
 *
 * @package Modules\Pay\Services
 */
class ConsumeDepositService extends BaseService
{
    private $consumeTradeRepository;
    private $userResourceRepository;
    private $consumeRecordRepository;
    private $consumeTradeService;

    public function __construct(
        ConsumeDepositRepository $consumeDepositRepository,
        ConsumeTradeRepository   $consumeTradeRepository,
        UserResourceRepository   $userResourceRepository,
        ConsumeRecordRepository  $consumeRecordRepository,

        ConsumeTradeService      $consumeTradeService
    )
    {
        $this->repository = $consumeDepositRepository;
        $this->consumeTradeRepository = $consumeTradeRepository;
        $this->userResourceRepository = $userResourceRepository;
        $this->consumeRecordRepository = $consumeRecordRepository;
        $this->consumeTradeService = $consumeTradeService;
    }


    // ProcessDeposit 新增
    public function processDeposit($request)
    {
        //todo 获取充值记录
        $deposit = $this->repository->findOne([
            'deposit_no' => $request['deposit_no'],
            'deposit_trade_no' => $request['deposit_trade_no'],
        ]);

        DB::beginTransaction();

        if (empty($deposit)) {
            $add_row = $request;
            if (!isset($add_row['deposit_no'])) {
                $add_row['deposit_no'] = $request['order_id'];
            }
            $result = $this->repository->add($add_row);
            if ($result) {
                $last_insert_id = $result->getKey();
                $deposit = $this->repository->getOne($last_insert_id);
            } else {
                throw new ErrorException('充值记录增加失败');
            }
        }

        if ($deposit['deposit_state'] == 0) {
            //todo 获取交易表记录
            $trade_rows = $this->consumeTradeRepository->find([['order_id', 'IN', explode(',', $deposit['order_id'])]]);
            if (empty($trade_rows)) {
                throw new ErrorException('交易订单获取失败');
            }
            $trade = current($trade_rows);

            //todo 处理用户账户增加充值额度
            $resource_flag = $this->userResourceRepository->incrementFieldByIds([$trade['buyer_id']], 'user_money', $deposit['deposit_total_fee']);
            if (!$resource_flag) {
                throw new ErrorException('用户充值失败');
            }

            //todo 写入充值流水
            $record_flag = $this->consumeRecordRepository->addConsumeRecord($trade['buyer_id'], $trade, $deposit);
            if (!$record_flag) {
                throw new ErrorException('充值流水写入失败');
            }

            //todo 修改充值成功状态
            $deposit_result = $this->repository->edit($deposit['deposit_id'], ['deposit_state' => 1]);
            if (!$deposit_result) {
                return new Exception('修改充值状态失败');
            }

            //todo 处理订单支付结果
            $pay_info = [
                'payment_met_id' => StateCode::PAYMENT_MET_MONEY,
                'payment_channel_id' => $deposit['payment_channel_id'],
                'payment_type_id' => $deposit['deposit_payment_type'],
                'pm_money' => $deposit['deposit_total_fee'],
            ];

            $result = $this->consumeTradeService->processPay($deposit['order_id'], $pay_info);
            if (!$result) {
                return new Exception('处理订单支付结果失败');
            }

        } else {
            // 处理充值已完成状态
            // 只是简单说明本次充值已经操作完成
        }

        DB::commit();
    }


    /**
     * 线下支付
     * @param $request
     * @return Exception|null
     * @throws ErrorException
     */
    public function offlinePay($request)
    {
        $consume_deposit = [
            'deposit_time' => $request->input('deposit_time', 0),
            'deposit_notify_time' => getDateTime(),   //通知时间
            'deposit_trade_no' => $request['deposit_trade_no'], //交易号
            'deposit_total_fee' => $request['deposit_total_fee'],   //交易金额
            'payment_channel_id' => $request['payment_channel_id'],   //支付渠道
            'order_id' => $request['order_id'], //商户网站唯一订单号(DOT):合并支付则为多个订单号, 没有创建联合支付交易号
            'deposit_no' => $request['deposit_trade_no'],
            'deposit_payment_type' => StateCode::PAYMENT_TYPE_OFFLINE
        ];

        $result = $this->processDeposit($consume_deposit);

        return $result;
    }

}
