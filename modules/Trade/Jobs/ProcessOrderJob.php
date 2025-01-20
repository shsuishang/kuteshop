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


namespace Modules\Trade\Jobs;

use App\Support\LevelCode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Pay\Services\UserResourceService;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;

class ProcessOrderJob implements ShouldQueue
{
    public $data;
    protected $configBaseRepository;
    protected $userResourceService;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle(ConfigBaseRepository $configBaseRepository, UserResourceService $userResourceService)
    {
        $this->configBaseRepository = $configBaseRepository;
        $this->userResourceService = $userResourceService;
        $data = $this->data;

        if ($data['order_id']) {
            $order_info = $data['order_info'];
            $exp_consume_rate = $this->configBaseRepository->getConfig('exp_consume_rate'); //经验值比例
            $exp_consume_max = $this->configBaseRepository->getConfig('exp_consume_max'); //单笔允许最大值
            $order_exp = ceil($order_info['order_payment_amount'] * $exp_consume_rate);
            $user_exp = min($order_exp, $exp_consume_max);
            Log::info("订单经验值", ['user_exp' => $user_exp]);

            $experience_row = [
                'user_id' => $order_info['user_id'],
                'exp' => $user_exp,
                'exp_type_id' => LevelCode::EXP_TYPE_CONSUME,
                'desc' => __('用户消费') . $order_info['order_id']
            ];
            $this->userResourceService->experience($experience_row);
        }

        Log::info("订单商品信息处理完成", ['order_id' => $data['order_id']]);
    }

}
