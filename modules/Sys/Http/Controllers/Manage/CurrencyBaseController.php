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
use Modules\Sys\Repositories\Criteria\CurrencyBaseCriteria;
use Modules\Sys\Repositories\Validators\CurrencyBaseValidator;
use Modules\Sys\Services\CurrencyBaseService;

class CurrencyBaseController extends BaseController
{
    private $currencyBaseService;
    private $currencyBaseValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(CurrencyBaseService $currencyBaseService, CurrencyBaseValidator $currencyBaseValidator)
    {
        $this->currencyBaseService = $currencyBaseService;
        $this->currencyBaseValidator = $currencyBaseValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->currencyBaseService->list($request, new CurrencyBaseCriteria($request));

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
            'currency_title' => $request->input('currency_title', ''),
            'currency_lang' => $request->input('currency_lang', ''),
            'currency_img' => $request->input('currency_img', ''),
            'currency_symbol_left' => $request->input('currency_symbol_left', ''),
            'currency_symbol_right' => $request->input('currency_symbol_right', ''),
            'currency_decimal_place' => $request->boolean('currency_decimal_place', false),
            'currency_exchange_rate' => $request->input('currency_exchange_rate', 1),
            'currency_status' => $request->boolean('currency_status', false),
            'currency_is_default' => $request->boolean('currency_is_default', false),
            'currency_default_lang' => $request->boolean('currency_default_lang', false),
            'currency_is_standard' => $request->boolean('currency_is_standard', false),
            'currency_sort' => $request->input('currency_sort', 0),
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->currencyBaseValidator->with($request->all())->passesOrFail('create');
        $data = $this->currencyBaseService->addCurrencyBase($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $currency_id = $request->get('currency_id', -1);
        $this->currencyBaseValidator->setId($currency_id);
        $this->currencyBaseValidator->with($request->all())->passesOrFail('update');
        $data = $this->currencyBaseService->editCurrencyBase($currency_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        if ($currency_id = $request->get('currency_id')) {
            $result = $this->currencyBaseService->editState($currency_id, $request);
            return Respond::success($result);
        } else {
            return Respond::error(__('无效的货币ID'));
        }
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $currency_id = $request->input('currency_id', -1);
        $row = $this->currencyBaseService->get($currency_id);
        if ($row['currency_is_default']) {
            throw new ErrorException('默认币种，不可删除！');
        }

        if ($row['currency_default_lang']) {
            throw new ErrorException('默认语言，不可删除！');
        }

        $this->currencyBaseService->remove($currency_id);

        return Respond::success([]);
    }

}
