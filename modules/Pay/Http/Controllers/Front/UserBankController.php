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

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Account\Repositories\Models\User;
use Modules\Pay\Repositories\Criteria\BaseBankCriteria;
use Modules\Pay\Repositories\Criteria\UserBankCardCriteria;
use Modules\Pay\Services\BaseBankService;
use Modules\Pay\Services\UserBankCardService;

class UserBankController extends BaseController
{
    private $userBankCardService;
    private $baseBankService;
    private $userId;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserBankCardService $userBankCardService, BaseBankService $baseBankService)
    {
        $this->userBankCardService = $userBankCardService;
        $this->baseBankService = $baseBankService;

        $this->userId = User::getUserId();
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $base_list = $this->baseBankService->list($request, new BaseBankCriteria($request));
        $data['bank_list'] = $base_list['data'];

        $user_bank_list = $this->userBankCardService->list($request, new UserBankCardCriteria($request));
        $data['user_bank_list'] = $user_bank_list['data'];

        return Respond::success($data);
    }


    /**
     * 获取银行卡信息
     */
    public function get(Request $request)
    {
        $user_bank_id = $request->get('user_bank_id', -1);
        $data = $this->userBankCardService->get($user_bank_id);

        return Respond::success($data);
    }


    /**
     * 新增/修改银行卡信息
     */
    public function addOrEditUserBank(Request $request)
    {
        $user_bank_row = [
            'user_id' => $this->userId,
            'bank_id' => $request->input('bank_id', 0),  //别名
            'bank_name' => $request->input('bank_name', ''),  //银行名称
            'user_bank_card_address' => $request->input('user_bank_card_address', ''), //开户支行名称
            'user_bank_card_code' => $request['user_bank_card_code'],       //银行卡卡号
            'user_bank_card_name' => $request->input('user_bank_card_name', ''),  //卡号账户名称
            'user_bank_card_mobile' => $request->input('user_bank_card_mobile', ''), //银行预留手机号
            'user_intl' => $request->input('currency_id', '86'),       //国家区号
            'user_bank_default' => 0,
            'user_bank_begin_date' => 0,
            'user_bank_amount_money' => 0
        ];
        $user_bank_id = $request->get('user_bank_id', -1);
        if ($user_bank_id) {
            $data = $this->userBankCardService->edit($user_bank_id, $user_bank_row);
        } else {
            $data = $this->userBankCardService->add($user_bank_row);
        }

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $user_bank_id = $request->get('user_bank_id', -1);
        $data = $this->userBankCardService->remove($user_bank_id);

        return Respond::success($data);
    }


}
