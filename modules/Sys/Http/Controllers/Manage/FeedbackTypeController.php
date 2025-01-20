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

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Repositories\Criteria\FeedbackTypeCriteria;
use Modules\Sys\Repositories\Validators\FeedbackTypeValidator;
use Modules\Sys\Services\FeedbackTypeService;

class FeedbackTypeController extends BaseController
{
    private $feedbackTypeService;
    private $feedbackTypeValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FeedbackTypeService $feedbackTypeService, FeedbackTypeValidator $feedbackTypeValidator)
    {
        $this->feedbackTypeService = $feedbackTypeService;
        $this->feedbackTypeValidator = $feedbackTypeValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->feedbackTypeService->list($request, new FeedbackTypeCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->feedbackTypeValidator->with($request->all())->passesOrFail('create');
        $data = $this->feedbackTypeService->add([
            'feedback_type_name' => $request['feedback_type_name'],   //名称
            'feedback_type_genus' => $request->input('feedback_type_genus', 1), //所属身份
            'feedback_type_enable' => $request->boolean('feedback_type_enable', 0) //是否启用
        ]);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $feedback_type_id = $request['feedback_type_id'];
        $this->feedbackTypeValidator->setId($feedback_type_id);
        $this->feedbackTypeValidator->with($request->all())->passesOrFail('update');
        $data = $this->feedbackTypeService->edit($feedback_type_id, [
            'feedback_type_name' => $request['feedback_type_name'],   //名称
            'feedback_type_genus' => $request->input('feedback_type_genus', 1), //所属身份
            'feedback_type_enable' => $request->boolean('feedback_type_enable', 0) //是否启用
        ]);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->feedbackTypeService->remove($request['feedback_type_id']);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $feedback_type_id = $request->get('feedback_type_id');
        $state_data = [];

        if ($request->has('feedback_type_enable')) {
            $state_data['feedback_type_enable'] = $request->boolean('feedback_type_enable');
        }

        // 更新状态
        if ($feedback_type_id && !empty($state_data)) {
            $this->feedbackTypeService->edit($feedback_type_id, $state_data);
        } else {
            throw new ErrorException(__('数据有误'));
        }

        return Respond::success($state_data);
    }

}
