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
use Modules\Pt\Repositories\Contracts\ProductBrandRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductCategoryRepository;
use Modules\Pt\Repositories\Contracts\ProductTypeRepository;

/**
 * Class ProductBrandService.
 *
 * @package Modules\Pt\Services
 */
class ProductBrandService extends BaseService
{
    private $productCategoryRepository;
    private $productTypeRepository;

    public function __construct(
        ProductBrandRepository    $productBrandRepository,
        ProductCategoryRepository $productCategoryRepository,
        ProductTypeRepository     $productTypeRepository
    )
    {
        $this->repository = $productBrandRepository;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->productTypeRepository = $productTypeRepository;
    }


    /**
     * 品牌树形数据
     * @param $request
     * @return array
     */
    public function tree($request)
    {
        $conditions = [];
        if ($request->has('front')) {
            $conditions['brand_recommend'] = 1;
            $conditions['brand_enable'] = 1;
            if ($brand_name = $request->get('keywords')) {
                $conditions[] = ['brand_name', 'LIKE', '%' . $brand_name . '%'];
            }
        }

        $brand_lists = $this->repository->find($conditions);
        $category_ids = array_column($brand_lists, 'category_id');
        $categories = $this->productCategoryRepository->gets($category_ids);

        $brandList = [];
        foreach ($categories as $category) {
            $brands = [];
            foreach ($brand_lists as $brand) {
                if ($brand['category_id'] == $category['category_id']) {
                    $brands[] = $brand;
                }
            }

            if (count($brands) > 0) {
                $brandList[] = [
                    'children' => empty($brands) ? null : $brands,
                    'brand_id' => $category['category_id'],
                    'brand_name' => $category['category_name']
                ];
            }
        }

        return $brandList;
    }


    /**
     * 删除
     * @param $brand_id
     * @return bool
     * @throws ErrorException
     */
    public function remove($brand_id)
    {
        $tmp_rows = $this->productTypeRepository->find([['brand_ids', 'FIND_IN_SET', [$brand_id]]]);
        $count = count($tmp_rows);
        if ($count > 0) {
            throw new ErrorException(sprintf('有 %d 条类型使用，不可删除', $count));
        }

        $result = $this->repository->remove($brand_id);
        if ($result) {
            return true;
        } else {
            throw new ErrorException('删除失败');
        }
    }

}
