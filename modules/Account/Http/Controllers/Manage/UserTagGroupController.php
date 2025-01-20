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


namespace Modules\Account\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserTagGroupCriteria;
use Modules\Account\Repositories\Validators\UserTagGroupValidator;
use Modules\Account\Services\UserTagGroupService;

class UserTagGroupController extends BaseController
{

    private $userTagGroupService;
    private $userTagGroupValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserTagGroupService $userTagGroupService, UserTagGroupValidator $userTagGroupValidator)
    {
        $this->userTagGroupService = $userTagGroupService;
        $this->userTagGroupValidator = $userTagGroupValidator;
    }


    /**
     * tree
     */
    public function tree(Request $request)
    {
        $data = $this->userTagGroupService->tree($request);

        return Respond::success($data);
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userTagGroupService->list($request, new UserTagGroupCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->userTagGroupValidator->with($request->all())->passesOrFail('create');
        $data = $this->userTagGroupService->add([
            'tag_group_name' => $request['tag_group_name'], //名称
            'tag_group_sort' => $request->input('tag_group_sort', 0), //排序
            'tag_group_enable' => $request->boolean('tag_group_enable'), //是否启用
        ]);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $tag_group_id = $request->get('tag_group_id');
        $this->userTagGroupValidator->setId($tag_group_id);
        $this->userTagGroupValidator->with($request->all())->passesOrFail('update');
        $data = $this->userTagGroupService->edit($tag_group_id, [
            'tag_group_name' => $request['tag_group_name'], //名称
            'tag_group_sort' => $request->input('tag_group_sort', 0), //排序
            'tag_group_enable' => $request->boolean('tag_group_enable'), //是否启用
        ]);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $tag_group_id = $request->get('tag_group_id');
        $data = $this->userTagGroupService->remove($tag_group_id);

        return Respond::success($data);
    }
}
