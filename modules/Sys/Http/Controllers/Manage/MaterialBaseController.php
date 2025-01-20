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
use Modules\Sys\Repositories\Criteria\MaterialBaseCriteria;
use Modules\Sys\Services\MaterialBaseService;

class MaterialBaseController extends BaseController
{
    private $materialBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(MaterialBaseService $materialBaseService)
    {
        $this->materialBaseService = $materialBaseService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->materialBaseService->list($request, new MaterialBaseCriteria($request));

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
            'store_id' => 0,
            'gallery_id' => $request->input('gallery_id', 0),
            'material_type' => $request->input('feedback_type_genus', 'image'),
            'material_name' => $request->input('material_name', ''),
            'material_desc' => $request->input('material_desc', ''),
            'material_alt' => $request->input('material_alt', ''),
            'material_url' => $request->input('material_url', ''),
            'material_path' => $request->input('material_path', ''),
            'material_size' => $request->input('material_size', 0),
            'material_mime_type' => $request->input('material_mime_type', 'image/png')
        ];

        return $data;
    }

    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $material_id = $request['material_id'];
        $data = $this->materialBaseService->edit($material_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->materialBaseService->remove($request['material_id']);

        return Respond::success($data);
    }

}
