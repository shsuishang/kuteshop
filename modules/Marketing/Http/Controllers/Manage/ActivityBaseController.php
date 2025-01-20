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


namespace Modules\Marketing\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Marketing\Repositories\Criteria\ActivityBaseCriteria;
use Modules\Marketing\Repositories\Validators\ActivityBaseValidator;
use Modules\Marketing\Services\ActivityBaseService;

class ActivityBaseController extends BaseController
{
    private $activityBaseService;
    private $activityBaseValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        ActivityBaseService   $activityBaseService,
        ActivityBaseValidator $activityBaseValidator
    )
    {
        $this->activityBaseService = $activityBaseService;
        $this->activityBaseValidator = $activityBaseValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->activityBaseService->list($request, new ActivityBaseCriteria($request));

        return Respond::success($data);
    }


    /**
     * 格式化请求数组
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $activity_rule = $request->input('activity_rule', '{}');
        $data = [
            'activity_name' => $request['activity_title'],  //活动名称
            'activity_title' => $request['activity_title'],  //活动名称
            'activity_type_id' => $request->input('activity_type_id', 0),   //活动类型
            'activity_type' => $request->input('activity_type', 1),      //参与类型(ENUM):1-免费参与;2-积分参与;3-购买参与;4-分享参与
            'activity_starttime' => $request->input('activity_starttime', 0), //活动开始时间
            'activity_endtime' => $request->input('activity_endtime', 0),    //活动结束时间
            'activity_use_level' => $request->input('activity_use_level', ''), //会员等级
            'activity_rule' => json_decode($activity_rule, true) //活动规则
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->activityBaseValidator->with($request->all())->passesOrFail('create');
        $data = $this->activityBaseService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $activity_id = $request['activity_id'];
        $this->activityBaseValidator->setId($activity_id);
        $this->activityBaseValidator->with($request->all())->passesOrFail('update');
        $data = $this->activityBaseService->edit($activity_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $activity_id = $request['activity_id'];
        $data = $this->activityBaseService->remove($activity_id);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $data = $this->activityBaseService->editState($request);

        return Respond::success($data);
    }


}
