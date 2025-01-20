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


namespace Modules\Admin\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Admin\Services\MenuBaseService;

class MenuBaseController extends BaseController
{
    private $menuBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(MenuBaseService $menuBaseService)
    {
        $this->menuBaseService = $menuBaseService;
    }

    /*
     * 获取树形菜单
     */
    public function tree(Request $request)
    {
        $condition = ['menu_role' => 1];
        if ($request['type'] != 2) {
            $condition['menu_type'] = 1;
            $condition['menu_enable'] = 1;
            $condition['menu_hidden'] = 0;
        }
        if ($menu_title = $request->input('menu_title', '')) {
            $condition['menu_title'] = $menu_title;
        }
        $data = $this->menuBaseService->treeMenus($condition);

        return Respond::success($data);
    }


    public function editState(Request $request)
    {
        $menu_id = $request->get('menu_id');
        $state_data = [];

        if ($request->has('menu_close')) {
            $state_data['menu_close'] = $request->boolean('menu_close');
        }

        if ($request->has('menu_hidden')) {
            $state_data['menu_hidden'] = $request->boolean('menu_hidden');
        }

        if ($request->has('menu_enable')) {
            $state_data['menu_enable'] = $request->boolean('menu_enable');
        }

        if ($request->has('menu_dot')) {
            $state_data['menu_dot'] = $request->boolean('menu_dot');
        }

        if ($request->has('menu_buildin')) {
            $state_data['menu_buildin'] = $request->boolean('menu_buildin');
        }

        // 更新状态
        if ($menu_id && !empty($state_data)) {
            $result = $this->menuBaseService->edit($menu_id, $state_data);
            if ($result) {
                return true;
            } else {
                throw new ErrorException(__('更新失败'));
            }
        }

        return Respond::success($state_data);
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        return [
            'menu_parent_id' => $request->input('menu_parent_id', 0),   // 项目标题
            'menu_title' => $request->input('menu_title', ''),
            'menu_url' => $request->input('menu_url', ''),
            'menu_name' => $request->input('menu_name', ''),
            'menu_path' => $request->input('menu_path', ''),
            'menu_component' => $request->input('menu_component', ''),
            'menu_redirect' => $request->input('menu_redirect', ''),
            'menu_class' => $request->input('menu_class', ''),
            'menu_icon' => $request->input('menu_icon', ''),
            'menu_bubble' => $request->input('menu_bubble', ''),
            'menu_sort' => $request->boolean('menu_sort', false),
            'menu_type' => $request->input('menu_type', ''),
            'menu_note' => $request->input('menu_note', ''),
            'menu_func' => $request->input('menu_func', ''),
            'menu_role' => $request->input('menu_role', ''),
            'menu_param' => $request->input('menu_param', ''),
            'menu_permission' => $request->input('menu_permission', ''),

            'menu_close' => $request->boolean('menu_close', false),
            'menu_hidden' => $request->boolean('menu_hidden', false),
            'menu_enable' => $request->boolean('menu_enable', false),
            'menu_dot' => $request->boolean('menu_dot', false),
            'menu_buildin' => $request->boolean('menu_buildin', false)
        ];
    }

    public function edit(Request $request)
    {
        $menu_id = $request->input('menu_id', -1);
        $formatted_request = $this->formatRequest($request);
        $data = $this->menuBaseService->edit($menu_id, $formatted_request);

        return Respond::success($data);
    }

}
