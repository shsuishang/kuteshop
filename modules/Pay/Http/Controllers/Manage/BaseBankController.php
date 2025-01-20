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


namespace Modules\Pay\Http\Controllers\Manage;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Pay\Repositories\Criteria\BaseBankCriteria;
use Modules\Pay\Services\BaseBankService;

class BaseBankController extends BaseController
{
    private $baseBankService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BaseBankService $baseBankService)
    {
        $this->baseBankService = $baseBankService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->baseBankService->list($request, new BaseBankCriteria($request));

        return Respond::success($data);
    }

    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $data = [
            'bank_name' => $request->input('bank_name', ''),   //银行名称
            'bank_remark' => $request->input('bank_remark', ''), //备注
            'bank_order' => $request->input('bank_order', 0),   //排序
            'bank_enable' => $request->boolean('bank_enable'),   //是否启用
            'settlement_account_type_id' => $request->input('settlement_account_type_id', 1004), //账户类别(ENUM):1001-微信;1002-支付宝;1003-现金;1004-银行
        ];

        return $data;
    }

    /**
     * 新增
     */
    public function add(Request $request)
    {
        $data = $this->baseBankService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $bank_id = $request->input('bank_id', -1);
        $data = $this->baseBankService->edit($bank_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $bank_id = $request->input('bank_id', -1);
        $data = $this->baseBankService->remove($bank_id);

        return Respond::success($data);
    }

}
