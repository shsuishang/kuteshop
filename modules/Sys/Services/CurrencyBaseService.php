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


namespace Modules\Sys\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\CurrencyBaseRepository;

/**
 * Class CurrencyBaseService.
 *
 * @package Modules\Sys\Services
 */
class CurrencyBaseService extends BaseService
{

    public function __construct(CurrencyBaseRepository $currencyBaseRepository)
    {
        $this->repository = $currencyBaseRepository;
    }


    /**
     * 添加货币语言
     * @param $request
     * @return true
     * @throws ErrorException
     */
    public function addCurrencyBase($request)
    {
        DB::beginTransaction();

        try {
            if (isset($request['currency_is_default']) && $request['currency_is_default']) {
                $this->repository->editWhere(['currency_is_default' => true], ['currency_is_default' => false]);
            }
            if (isset($request['currency_default_lang']) && $request['currency_default_lang']) {
                $this->repository->editWhere(['currency_default_lang' => true], ['currency_default_lang' => false]);
            }

            $this->repository->add($request);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('操作失败: ') . $e->getMessage());
        }

        return true;
    }


    /**
     * 修改货币语言
     * @param $currency_id
     * @param $request
     * @return true
     * @throws ErrorException
     */
    public function editCurrencyBase($currency_id, $request)
    {
        DB::beginTransaction();

        try {

            $row = $this->repository->getOne($currency_id);
            if (isset($request['currency_is_default']) && $request['currency_is_default'] && $request['currency_is_default'] != $row['currency_is_default']) {
                $this->repository->editWhere(['currency_is_default' => true], ['currency_is_default' => false]);
            }
            if (isset($request['currency_default_lang']) && $request['currency_default_lang'] && $request['currency_default_lang'] != $row['currency_default_lang']) {
                $this->repository->editWhere(['currency_default_lang' => true], ['currency_default_lang' => false]);
            }

            $this->repository->edit($currency_id, $request);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('操作失败: ') . $e->getMessage());
        }

        return true;
    }


    /**
     * 修改状态
     * @param $request
     * @param $currency_id
     * @return bool
     * @throws ErrorException
     */
    public function editState($currency_id, $request)
    {
        DB::beginTransaction();

        try {
            $state_fields = [
                'currency_status',
                'currency_is_default',
                'currency_default_lang',
                'currency_is_standard',
                'currency_decimal_place',
            ];

            $state_data = [];
            $currency_row = $this->repository->getOne($currency_id);

            foreach ($state_fields as $field) {
                if ($request->has($field)) {
                    $new_value = $request->boolean($field, false);
                    $state_data[$field] = $new_value;

                    if ($new_value != $currency_row[$field] && in_array($field, ['currency_is_default', 'currency_default_lang'])) {
                        $this->repository->editWhere([$field => true], [$field => false]);
                    }
                }
            }

            if (!empty($state_data)) {
                $this->repository->edit($currency_id, $state_data);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('操作失败: ') . $e->getMessage());
        }

        return true;
    }

}
