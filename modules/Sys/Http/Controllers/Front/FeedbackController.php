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


namespace Modules\Sys\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Sys\Repositories\Criteria\FeedbackBaseCriteria;
use Modules\Sys\Services\FeedbackBaseService;

class FeedbackController extends BaseController
{
    private $feedbackBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FeedbackBaseService $feedbackBaseService)
    {
        $this->feedbackBaseService = $feedbackBaseService;
    }


    /**
     * 反馈类型分类列表
     */
    public function getCategory()
    {
        $data = $this->feedbackBaseService->getCategory();

        return Respond::success($data);
    }


    /**
     * 反馈列表
     */
    public function list(Request $request)
    {
        $user_id = User::getUserId();
        $request['user_id'] = $user_id;
        $data = $this->feedbackBaseService->list($request, new FeedbackBaseCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $user_id = checkLoginUserId();
        $user = User::getUser();
        $data = $this->feedbackBaseService->add([
            'feedback_category_id' => $request['feedback_category_id'],   //分类编号
            'user_id' => $user_id,   //用户编号
            'user_nickname' => $user['user_nickname'], //用户昵称
            'feedback_question' => $request->input('feedback_question', ''), //反馈问题:在这里描述您遇到的问题
            'feedback_question_url' => $request->input('feedback_question_url', ''), //页面链接
            'feedback_question_time' => getDateTime(), //反馈时间
            'feedback_question_status' => $request->boolean('feedback_question_status'), //举报状态(BOOL):0-未处理;1-已处理
            'item_id' => $request->input('item_id', 0)
        ]);

        return Respond::success($data);
    }


}
