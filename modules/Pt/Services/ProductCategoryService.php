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
use Modules\Pt\Repositories\Contracts\ProductAssistItemRepository;
use Modules\Pt\Repositories\Contracts\ProductAssistRepository;
use Modules\Pt\Repositories\Contracts\ProductBrandRepository;
use Modules\Pt\Repositories\Contracts\ProductCategoryRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductTypeRepository;
use Modules\Pt\Repositories\Criteria\ProductCategoryCriteria;

/**
 * Class ProductCategoryService.
 *
 * @package Modules\Pt\Services
 */
class ProductCategoryService extends BaseService
{

    private $productTypeRepository;
    private $productBrandRepository;
    private $productAssistRepository;
    private $productAssistItemRepository;

    public function __construct(
        ProductCategoryRepository   $productCategoryRepository,
        ProductTypeRepository       $productTypeRepository,
        ProductBrandRepository      $productBrandRepository,
        ProductAssistRepository     $productAssistRepository,
        ProductAssistItemRepository $productAssistItemRepository
    )
    {
        $this->repository = $productCategoryRepository;
        $this->productTypeRepository = $productTypeRepository;
        $this->productBrandRepository = $productBrandRepository;
        $this->productAssistRepository = $productAssistRepository;
        $this->productAssistItemRepository = $productAssistItemRepository;
    }


    /**
     * 树形结构列表
     * @param $request
     * @return array
     */
    public function tree($request)
    {
        $request['size'] = 9999;
        $list = $this->list($request, new ProductCategoryCriteria($request));
        $data = $list['data'];
        $data = ArrayToTree($data, 0, 'children', 'category_');

        return $data;
    }


    /**
     * 树形结构分类
     * @param $category_parent_id
     * @param $request
     * @return array|mixed
     */
    public function getTree($category_parent_id = 0, $request = [])
    {
        $request['category_parent_id'] = $category_parent_id;
        $list = $this->list($request, new ProductCategoryCriteria($request));
        $category_rows = $list['data'];

        foreach ($category_rows as $key => $category_row) {
            $category_rows[$key]['children'] = $this->getTree($category_row['category_id'], $request);
        }

        return $category_rows;
    }


    /**
     * 读取子类id
     *
     * @param int $category_parent_id 主键值
     * @param bools $recursive 是否递归查询
     * @return array $rows 返回的查询内容
     * @access public
     */
    public function getCategoryIdByParentId($category_parent_id = 0, $recursive = true)
    {
        if (is_array($category_parent_id)) {
            $cond_row = array(['category_parent_id', 'IN', $category_parent_id], ['category_is_enable', '=', 1]);
        } else {
            $cond_row = array('category_parent_id' => $category_parent_id, 'category_is_enable' => 1);
        }

        $category_rows = $this->repository->find($cond_row);
        $category_id_row = array_column($category_rows, 'category_id');
        if ($recursive && $category_id_row) {
            $rs = $this->getCategoryIdByParentId($category_id_row, $recursive);
            $category_id_row = array_merge($category_id_row, $rs);
        }

        return $category_id_row;
    }


    /**
     * 删除
     * @param $category_id
     * @return bool
     * @throws ErrorException
     */
    public function remove($category_id)
    {
        $count = $this->repository->getNum(['category_parent_id' => $category_id]);
        if ($count) {
            throw new ErrorException(sprintf(__("有 %d 个子级商品分类，不可删除"), $count));
        }

        $type_count = $this->productTypeRepository->getNum(['category_id' => $category_id]);
        if ($type_count) {
            throw new ErrorException(sprintf(__("有 %d 个类型使用，不可删除"), $type_count));
        }

        $brand_count = $this->productBrandRepository->getNum(['category_id' => $category_id]);
        if ($brand_count) {
            throw new ErrorException(sprintf(__("有 %d 个品牌使用，不可删除"), $brand_count));
        }

        $result = $this->repository->remove($category_id);

        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }
    }


    /**
     * 根据ID查询所有父级类别（包含自身）
     *
     * @param $category_id
     * @return array
     */
    public function getParentCategory($category_id)
    {
        $category_list = [];
        $i = 5; // 设置一个最大深度以避免无限循环

        while ($category_id && $i != 0) {
            $category = $this->repository->getOne($category_id);

            if (!empty($category)) {
                $category_list[] = $category;
                $category_id = $category['category_parent_id'];
            } else {
                break;
            }

            $i--;
        }

        return $category_list;
    }


    /**
     * 获取分类绑定类型属性信息
     * @param $category_id
     * @return array
     */
    public function getCategoryFilter($category_id)
    {

        $category_filter_row = [];
        $category_filter_row['contracts'] = [];
        $category_filter_row['markets'] = [];

        // 判断是否固定分类读取数据
        if ($category_id) {
            $product_category = $this->repository->getOne($category_id);
            $category_filter_row['info'] = $product_category;

            if (!empty($product_category)) {
                //上级分类
                $parent_category = $this->getParentCategory($category_id);
                $category_filter_row['parent'] = $parent_category;

                //下级分类
                $child_category = $this->repository->find([
                    'category_parent_id' => $category_id,
                    'category_is_enable' => 1
                ]);
                $category_filter_row['children'] = array_values($child_category);

                //辅助属性
                $product_type = $this->productTypeRepository->getOne($product_category['type_id']);
                $assist_ids = explode(',', $product_type['assist_ids']);
                $assist_rows = $this->productAssistRepository->gets($assist_ids);
                if ($assist_rows) {
                    $assist_item_list = $this->productAssistItemRepository->find([['assist_id', 'IN', $assist_ids]]);
                    $assist_items = arrayMap($assist_item_list, 'assist_id');

                    foreach ($assist_rows as $assist_id => $assist_row) {
                        if (isset($assist_items[$assist_row['assist_id']])) {
                            $assist_rows[$assist_id]['items'] = $assist_items[$assist_row['assist_id']];
                        }
                    }
                }
                $category_filter_row['assists'] = array_values($assist_rows);

                //品牌
                $brand_ids = explode(',', $product_type['brand_ids']);
                $brand_rows = $this->productBrandRepository->gets($brand_ids);
                $category_filter_row['brands'] = array_values($brand_rows);
            }
        }

        return $category_filter_row;
    }

}
