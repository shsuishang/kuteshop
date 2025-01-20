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
use Modules\Sys\Repositories\Criteria\PagePcNavCriteria;
use Modules\Sys\Repositories\Validators\PagePcNavValidator;
use Modules\Sys\Services\PagePcNavService;

class PagePcNavController extends BaseController
{
    private $pagePcNavService;
    private $pagePcNavValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PagePcNavService $pagePcNavService, PagePcNavValidator $pagePcNavValidator)
    {
        $this->pagePcNavService = $pagePcNavService;
        $this->pagePcNavValidator = $pagePcNavValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->pagePcNavService->list($request, new PagePcNavCriteria($request));

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
            'nav_title' => $request['nav_title'],
            'nav_url' => $request->input('nav_url', ''),
            'nav_position' => $request['nav_position'],
            'nav_target_blank' => $request->boolean('nav_target_blank'),
            'nav_image' => $request->input('nav_image', ''),
            'nav_dropdown_menu' => $request->input('nav_dropdown_menu', ''),
            'nav_order' => $request->input('nav_order', 0),
            'nav_enable' => $request->boolean('nav_enable'),
            'nav_buildin' => $request->input('nav_buildin', 0)
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->pagePcNavValidator->with($request->all())->passesOrFail('create');
        $data = $this->pagePcNavService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $nav_id = $request['nav_id'];

        $this->pagePcNavValidator->setId($nav_id);
        $this->pagePcNavValidator->with($request->all())->passesOrFail('update');
        $data = $this->pagePcNavService->edit($nav_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->pagePcNavService->remove($request['nav_id']);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $nav_id = $request['nav_id'];
        $state_data = [];
        $state_data['nav_enable'] = $request->boolean('nav_enable', 0);

        // 更新状态
        if ($nav_id && !empty($state_data)) {
            $this->pagePcNavService->edit($nav_id, $state_data);
        } else {
            throw new ErrorException(__('数据有误'));
        }

        return Respond::success($state_data);
    }

}
