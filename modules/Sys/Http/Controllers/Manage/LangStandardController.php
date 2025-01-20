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
use Modules\Sys\Repositories\Criteria\LangStandardCriteria;
use Modules\Sys\Repositories\Validators\LangStandardValidator;
use Modules\Sys\Services\LangStandardService;

class LangStandardController extends BaseController
{
    private $langStandardService;
    private $langStandardValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(LangStandardService $langStandardService, LangStandardValidator $langStandardValidator)
    {
        $this->langStandardService = $langStandardService;
        $this->langStandardValidator = $langStandardValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->langStandardService->getList($request, new LangStandardCriteria($request));

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
            'is_used' => $request->boolean('is_used', false),
            'frontend' => $request->boolean('frontend', false),
            'backend' => $request->boolean('backend', false),
            'java' => $request->boolean('java', false),
            'time' => $request->input('time', getTime())
        ];

        $lang_fields = ['zh_CN', 'zh_TW', 'en_GB', 'th_TH', 'es_MX', 'ar_SA', 'vi_VN', 'tr_TR', 'ja_JP', 'id_ID', 'de_DE', 'fr_FR', 'pt_PT', 'it_IT',
            'ru_RU', 'ro_RO', 'az_AZ', 'el_GR', 'fi_FI', 'lv_LV', 'nl_NL', 'da_DK', 'sr_RS', 'pl_PL', 'uk_UA', 'kk_KZ', 'my_MM', 'ko_KR', 'ms_MY'];
        foreach ($lang_fields as $field) {
            $key = strtolower($field);
            if ($request->has($key)) {
                $data[$field] = $request->input($key);
            }
        }

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->langStandardValidator->with($request->all())->passesOrFail('create');
        $data = $this->langStandardService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $zh_CN = $request->get('zh_cn', -1);
        $this->langStandardValidator->setId($zh_CN);
        $this->langStandardValidator->with($request->all())->passesOrFail('update');
        $data = $this->langStandardService->edit($zh_CN, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $zh_CN = $request->input('zh_CN', -1);
        $this->langStandardService->remove($zh_CN);

        return Respond::success([]);
    }

}
