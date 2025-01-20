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
use Modules\Sys\Repositories\Criteria\PageModuleCriteria;
use Modules\Sys\Repositories\Validators\PageModuleValidator;
use Modules\Sys\Services\ConfigBaseService;
use Modules\Sys\Services\PageModuleService;

class PageModuleController extends BaseController
{
    private $pageModuleService;
    private $pageModuleValidator;
    private $configBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        PageModuleService   $pageModuleService,
        PageModuleValidator $pageModuleValidator,
        ConfigBaseService   $configBaseService
    )
    {
        $this->pageModuleService = $pageModuleService;
        $this->pageModuleValidator = $pageModuleValidator;
        $this->configBaseService = $configBaseService;
    }


    /**
     * 获取商城楼层模板库
     */
    public function listTpl()
    {
        $data = $this->configBaseService->getServiceData(['module_type' => 2]);

        return Respond::success($data);
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->pageModuleService->list($request, new PageModuleCriteria($request));

        return Respond::success($data);
    }


    /**
     * 添加
     */
    public function add(Request $request)
    {
        $this->pageModuleValidator->with($request->all())->passesOrFail('create');

        $add_row = [
            'pm_enable' => 0,
            'page_id' => $request->get('page_id'),
            'module_id' => $request->get('module_id')
        ];

        $pm_json = $request->get('pm_json');
        $url = "\"url\": \"//test.shopsuite.cn";
        $url1 = "\"url\":\"//test.shopsuite.cn";
        $url_new = "\"url\":\"" . env('URL_PC'); // 使用.env配置的URL
        $link = "\"link\":\"//test.shopsuite.cn";
        $link_new = "\"link\":\"" . env('URL_PC'); // 使用.env配置的URL

        if (!empty($pm_json)) {
            $pm_json = str_replace([$url, $link, $url1], [$url_new, $link_new, $url_new], $pm_json);
            $add_row['pm_json'] = json_decode($pm_json);
        }

        $result = $this->pageModuleService->add($add_row);
        $pm_id = $result->getKey();
        $data = $this->pageModuleService->get($pm_id);
        $data['pm_json'] = $pm_json;

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $pm_id = $request['pm_id'];
        $this->pageModuleValidator->setId($pm_id);
        $this->pageModuleValidator->with($request->all())->passesOrFail('update');
        $pm_json = json_decode($request->get('pm_json'));
        $edit_row = [
            'pm_json' => $pm_json
        ];
        $data = $this->pageModuleService->edit($pm_id, $edit_row);

        return Respond::success($data);
    }


    /**
     * 修改启用状态
     */
    public function enable(Request $request)
    {
        $pm_id = $request['pm_id'];
        $pm_enable = $request->get('usable') == 'usable' ? 1 : 0;
        $data = $this->pageModuleService->edit($pm_id, ['pm_enable' => $pm_enable]);

        return Respond::success($data);
    }


    /**
     * 排序
     */
    public function sort(Request $request)
    {
        $data = $this->pageModuleService->sort($request);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->pageModuleService->remove($request['pm_id']);

        return Respond::success($data);
    }

}
