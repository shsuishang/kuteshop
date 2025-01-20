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

use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductCategoryRepository;
use Modules\Pt\Repositories\Contracts\ProductInfoRepository;
use Modules\Pt\Repositories\Contracts\ProductSpecItemRepository;
use Modules\Pt\Repositories\Contracts\ProductSpecRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductTypeRepository;

/**
 * Class ProductSpecService.
 *
 * @package Modules\Pt\Services
 */
class ProductSpecService extends BaseService
{

    private $productSpecItemRepository;
    private $productCategoryRepository;
    private $productTypeRepository;
    private $productInfoRepository;

    public function __construct(
        ProductSpecRepository     $productSpecRepository,
        ProductSpecItemRepository $productSpecItemRepository,
        ProductCategoryRepository $productCategoryRepository,
        ProductTypeRepository     $productTypeRepository,
        ProductInfoRepository     $productInfoRepository
    )
    {
        $this->repository = $productSpecRepository;
        $this->productSpecItemRepository = $productSpecItemRepository;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->productTypeRepository = $productTypeRepository;
        $this->productInfoRepository = $productInfoRepository;
    }


    /**
     * 规格树形数据
     * @param $request
     * @return array
     */
    public function tree($request)
    {
        $spec_lists = $this->repository->find([]);
        $category_ids = array_column($spec_lists, 'category_id');
        $categories = $this->productCategoryRepository->gets($category_ids);

        $brandList = [];
        foreach ($categories as $category) {
            $brands = [];
            foreach ($spec_lists as $brand) {
                if ($brand['category_id'] == $category['category_id']) {
                    $brands[] = $brand;
                }
            }

            if (count($brands) > 0) {
                $brandList[] = [
                    'children' => empty($brands) ? null : $brands,
                    'spec_id' => $category['category_id'],
                    'spec_name' => $category['category_name']
                ];
            }
        }

        return $brandList;
    }


    /**
     * 删除
     * @param $spec_id
     * @return bool
     * @throws ErrorException
     */
    public function remove($spec_id)
    {
        $spec_row = $this->repository->getOne($spec_id);
        if (empty($spec_row)) {
            throw new ErrorException(__('规格不存在'));
        }

        if ($spec_row['spec_buildin']) {
            throw new ErrorException(__("系统内置，不可删除"));
        }

        $count = $this->productSpecItemRepository->getNum(['spec_id' => $spec_id]);
        if ($count > 0) {
            throw new ErrorException(sprintf(__('有 %d 个规格选项，不可删除'), $count));
        }

        $tmp_rows = $this->productTypeRepository->find([['spec_ids', 'FIND_IN_SET', [$spec_id]]]);
        $count = count($tmp_rows);
        if ($count > 0) {
            throw new ErrorException(sprintf(__("有 %d 条类型使用，不可删除"), $count));
        }

        $product_spec_rows = $this->productInfoRepository->find([['spec_ids', 'FIND_IN_SET', [$spec_id]]]);
        $count = count($product_spec_rows);
        if ($count > 0) {
            throw new ErrorException(sprintf(__("已被 %d 个SPU商品使用，不可删除"), $count));
        }

        $result = $this->repository->remove($spec_id);

        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }
    }


    /**
     * 获取规格选项
     * @param $spec_ids
     * @return array|mixed
     */
    public function getSpecItems($spec_ids = [-1])
    {
        $spec_list = $this->repository->gets($spec_ids);
        if ($spec_list) {
            $spec_item_list = $this->productSpecItemRepository->find([
                ['spec_id', 'IN', $spec_ids],
                ['spec_item_enable', '=', 1]
            ]);
            $spec_items = [];
            foreach ($spec_item_list as $spec_item) {
                if (!array_key_exists($spec_item['spec_id'], $spec_items)) {
                    $spec_items[$spec_item['spec_id']] = [];
                }
                $spec_items[$spec_item['spec_id']][] = $spec_item;
            }

            foreach ($spec_list as $spec_id => $assist_row) {
                if (isset($spec_items[$assist_row['spec_id']])) {
                    $spec_list[$spec_id]['items'] = $spec_items[$assist_row['spec_id']];
                }
            }
        }

        return $spec_list;
    }

}
