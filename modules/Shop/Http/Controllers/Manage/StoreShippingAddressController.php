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


namespace Modules\Shop\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Modules\Shop\Http\Controllers\ShopController;
use Modules\Shop\Repositories\Criteria\StoreShippingAddressCriteria;
use Modules\Shop\Services\StoreShippingAddressService;
use Modules\Shop\Repositories\Validators\StoreShippingAddressValidator;

class StoreShippingAddressController extends ShopController
{
    private $storeShippingAddressService;
    private $storeShippingAddressValidator;

    /**
     * Create a new controller instance.
     *
     * @param StoreShippingAddressService $storeShippingAddressService
     * @param StoreShippingAddressValidator $storeShippingAddressValidator
     */
    public function __construct(
        StoreShippingAddressService   $storeShippingAddressService,
        StoreShippingAddressValidator $storeShippingAddressValidator,
    )
    {
        $this->storeShippingAddressService = $storeShippingAddressService;
        $this->storeShippingAddressValidator = $storeShippingAddressValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->storeShippingAddressService->list($request, new StoreShippingAddressCriteria($request));

        return Respond::success($data);
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        return [
            'ss_name' => $request->input('ss_name', ''),   // 联系人
            'ss_intl' => $request->input('ss_intl', '+86'), // 国家编码
            'ss_mobile' => $request->input('ss_mobile', ''),   // 手机号码
            'ss_postalcode' => $request->input('ss_postalcode', ''), // 邮编
            'ss_province' => $request->input('ss_province', ''), // 省份
            'ss_city' => $request->input('ss_city', ''), // 市
            'ss_county' => $request->input('ss_county', ''), // 县区
            'ss_address' => $request->input('ss_address', ''), // 详细地址
            'ss_province_id' => $request->input('ss_province_id', 0), // 省编号
            'ss_city_id' => $request->input('ss_city_id', 0), // 市编号
            'ss_county_id' => $request->input('ss_county_id', 0), // 县区编号
            'ss_is_default' => $request->boolean('ss_is_default', false), // 默认地址(ENUM):0-否;1-是
        ];
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->validateRequest($request, 'create');
        $formatted_request = $this->formatRequest($request);
        $data = $this->storeShippingAddressService->addShippingAddress($formatted_request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $ss_id = $request['ss_id'];
        $this->validateRequest($request, 'update');
        $formatted_request = $this->formatRequest($request);
        $data = $this->storeShippingAddressService->editShippingAddress($ss_id, $formatted_request);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $ss_id = $request->input('ss_id', 0);
        $data = $this->storeShippingAddressService->remove($ss_id);

        return Respond::success($data);
    }


    /**
     * 验证请求
     */
    private function validateRequest(Request $request, string $action)
    {
        $this->storeShippingAddressValidator->with($request->all())->passesOrFail($action);
    }

}
