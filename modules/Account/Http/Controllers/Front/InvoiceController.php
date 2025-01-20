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


namespace Modules\Account\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserInvoiceCriteria;
use Modules\Account\Repositories\Validators\UserInvoiceValidator;
use Modules\Account\Services\UserInvoiceService;
use Modules\Sys\Services\ConfigBaseService;

class InvoiceController extends BaseController
{

    private $userId;
    private $userInvoiceService;
    private $userInvoiceValidator;
    private $configBaseService;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserInvoiceService   $userInvoiceService,
        UserInvoiceValidator $userInvoiceValidator,
        ConfigBaseService    $configBaseService
    )
    {
        $this->userId = checkLoginUserId();

        $this->userInvoiceService = $userInvoiceService;
        $this->userInvoiceValidator = $userInvoiceValidator;
        $this->configBaseService = $configBaseService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $request['user_id'] = $this->userId;
        $data = $this->userInvoiceService->list($request, new UserInvoiceCriteria($request));

        return Respond::success($data);
    }


    /**
     * 获取数据
     */
    public function get(Request $request)
    {
        $data = $this->userInvoiceService->get($request['user_invoice_id']);
        checkDataRights($this->userId, $data);

        return Respond::success($data);
    }


    /**
     * 格式化请求数组
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $data = [
            'user_id' => $request->input('user_id', 0), //所属用户
            'invoice_title' => $request->input('invoice_title', ''), //发票抬头
            'invoice_company_code' => $request->input('invoice_company_code', '+86'), //纳税人识别号
            'invoice_content' => $request->input('invoice_content', ''), //发票内容
            'invoice_is_company' => $request->boolean('invoice_is_company'), //公司开票(BOOL):0-个人;1-公司
            'invoice_is_electronic' => $request->boolean('invoice_is_electronic'), //电子发票(ENUM):0-纸质发票;1-电子发票
            'invoice_type' => $request->input('invoice_type', 1), //发票类型(ENUM):1-普通发票;2-增值税专用发票
            'invoice_address' => $request->input('invoice_address', ''), //单位地址
            'invoice_phone' => $request->input('invoice_phone', ''), //单位电话
            'invoice_bankname' => $request->input('invoice_bankname', ''), //开户银行
            'invoice_bankaccount' => $request->input('invoice_bankaccount', ''), //银行账号
            'invoice_contact_mobile' => $request->input('invoice_contact_mobile', ''), //收票人手机
            'invoice_contact_email' => $request->input('invoice_contact_email', ''), //收票人邮箱
            'invoice_is_default' => $request->input('invoice_is_default', 0), //是否默认
            'invoice_contact_name' => $request->input('invoice_contact_name', ''), //收票人
            'invoice_contact_area' => $request->input('invoice_contact_area', ''), //收票人地区
            'invoice_contact_address' => $request->input('invoice_contact_address', ''), //收票详细地址
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->userInvoiceValidator->with($request->all())->passesOrFail('create');

        $request['user_id'] = $this->userId;
        $add_row = $this->formatRequest($request);
        $add_row['invoice_datetime'] = getDateTime();

        $data = $this->userInvoiceService->add($add_row);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $user_invoice_id = $request['user_invoice_id'];
        $row = $this->userInvoiceService->get($user_invoice_id);
        checkDataRights($this->userId, $row);

        $this->userInvoiceValidator->with($request->all())->passesOrFail('update');

        $request['user_id'] = $this->userId;
        $data = $this->userInvoiceService->edit($user_invoice_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $row = $this->userInvoiceService->get($request['user_invoice_id']);
        checkDataRights($this->userId, $row);

        $data = $this->userInvoiceService->remove($request['user_invoice_id']);

        return Respond::success($data);
    }


    /**
     * 发票说明
     */
    public function getInvoiceTips()
    {
        $invoice_tips = $this->configBaseService->getConfig('invoice_tips');
        return Respond::ok($invoice_tips);
    }

}
