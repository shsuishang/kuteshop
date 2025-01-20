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


namespace Modules\Pt\Services;

use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductAssistItemRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductAssistRepository;

/**
 * Class ProductAssistItemService.
 *
 * @package Modules\Pt\Services
 */
class ProductAssistItemService extends BaseService
{

    private $productAssistRepository;

    public function __construct(ProductAssistItemRepository $productAssistItemRepository, ProductAssistRepository $productAssistRepository)
    {
        $this->repository = $productAssistItemRepository;
        $this->productAssistRepository = $productAssistRepository;
    }


    /**
     * 新增
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function add($request)
    {
        DB::beginTransaction();

        try {
            $assist_item = [
                'assist_id' => $request['assist_id'],     //属性ID
                'assist_item_name' => $request['assist_item_name'], //选项名称
                'assist_item_sort' => $request->input('assist_item_sort', 0)   //排序
            ];
            $result = $this->repository->add($assist_item);
            $assist_item_id = $result->getKey();

            $assist_id = $this->getAssistId($assist_item_id);
            $this->updateAssistItem($assist_id);

            DB::commit();
            return true;
        } catch (\Exception $e) {

            DB::rollBack();
            throw new ErrorException(__('添加失败: ') . $e->getMessage());
        }
    }


    /**
     * 删除
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function remove($assist_item_id)
    {
        DB::beginTransaction();

        $assist_id = $this->getAssistId($assist_item_id);
        $this->repository->remove($assist_item_id);
        $result = $this->updateAssistItem($assist_id);

        if ($result) {
            DB::commit();
            return true;
        } else {
            DB::rollBack();
            throw new ErrorException(__('删除失败'));
        }
    }


    /**
     * 获取assist_id
     * @param $assist_item_id
     * @return int|mixed
     */
    public function getAssistId($assist_item_id = null)
    {
        $assist_id = 0;
        if ($assist_item_id) {
            $assist_row = $this->repository->getOne($assist_item_id);
            $assist_id = $assist_row['assist_id'];
        }

        return $assist_id;
    }


    /**
     * 更新属性中的assist_item
     * @param $assist_id
     * @return bool|mixed
     */
    public function updateAssistItem($assist_id = null)
    {
        if ($assist_id) {
            $assist_items = $this->repository->find(['assist_id' => $assist_id]);
            $assist_item_names = array_column($assist_items, 'assist_item_name');
            $assist_item = implode(',', $assist_item_names);
            return $this->productAssistRepository->edit($assist_id, ['assist_item' => $assist_item]);
        }

        return true;
    }

}
