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


namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\MessageTemplateCriteria;
use Modules\Sys\Repositories\Validators\MessageTemplateValidator;
use Modules\Sys\Services\MessageTemplateService;

class MessageTemplateController extends BaseController
{
    private $messageTemplateService;
    private $messageTemplateValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(MessageTemplateService $messageTemplateService, MessageTemplateValidator $messageTemplateValidator)
    {
        $this->messageTemplateService = $messageTemplateService;
        $this->messageTemplateValidator = $messageTemplateValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->messageTemplateService->list($request, new MessageTemplateCriteria($request));

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
            'message_name' => $request->input('message_name', ''),  //模板名称
            'message_email_title' => $request->input('message_email_title', ''),  //邮件标题
            'message_email_content' => $request->input('message_email_content', ''),   //邮件内容
            'message_content' => $request->input('message_content', ''),  //站内消息
            'message_sms' => $request->input('message_sms', ''),   //短信内容
            'message_app' => $request->input('message_app', ''),   //APP内容
            'message_type' => $request->input('message_type', 2),   //消息类型(ENUM):1-用户;2-商家;3-平台;
            'message_enable' => $request->input('message_enable', 0),   //站内通知(BOOL):0-禁用;1-启用
            'message_sms_enable' => $request->input('message_sms_enable', 0),   //短息通知(BOOL):0-禁用;1-启用
            'message_email_enable' => $request->input('message_email_enable', 0),   //邮件通知(BOOL):0-禁用;1-启用
            'message_wechat_enable' => $request->input('message_wechat_enable', 0),   //微信通知(BOOL):0-禁用;1-启用
            'message_xcx_enable' => $request->input('message_xcx_enable', 0),   //小程序通知(BOOL):0-禁用;1-启用
            'message_app_enable' => $request->input('message_app_enable', 0),   //APP推送(BOOL):0-禁用;1-启用
            'message_sms_force' => $request->input('message_sms_force', 0),   //手机短信(BOOL):0-不强制;1-强制
            'message_email_force' => $request->input('message_email_force', 0),   //邮件(BOOL):0-不强制;1-强制
            'message_app_force' => $request->input('message_app_force', 0),   //APP(BOOL):0-不强制;1-强制
            'message_force' => $request->input('message_force', 0),   //站内信(BOOL):0-不强制;1-强制
            'message_category' => $request->input('message_category', 0),   //消息分组(ENUM):0-默认消息;1-公告消息;2-订单消息;3-商品消息;4-余额卡券;5-服务消息
            'message_order' => $request->input('message_order', 0),   //消息排序
            'message_tpl_id' => $request->input('message_tpl_id', ''),   //备案模板编号
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->messageTemplateValidator->with($request->all())->passesOrFail('create');
        $data = $this->messageTemplateService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $message_id = $request['message_id'];
        $this->messageTemplateValidator->setId($message_id);
        $this->messageTemplateValidator->with($request->all())->passesOrFail('update');
        $data = $this->messageTemplateService->edit($message_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $message_id = $request['message_id'];
        $data = $this->messageTemplateService->remove($message_id);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $data = $this->messageTemplateService->editState($request);

        return Respond::success($data);
    }

}
