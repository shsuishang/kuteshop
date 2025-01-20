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
use Modules\Pt\Repositories\Contracts\ProductCategoryRepository;
use Modules\Pt\Repositories\Contracts\ProductInfoRepository;
use Modules\Pt\Repositories\Contracts\ProductTypeRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Criteria\ProductTypeCriteria;

/**
 * Class ProductTypeService.
 *
 * @package Modules\Pt\Services
 */
class ProductTypeService extends BaseService
{

    private $productCategoryRepository;
    private $productInfoRepository;
    private $productAssistService;
    private $productBrandRepository;
    private $productSpecService;

    public function __construct(
        ProductTypeRepository     $productTypeRepository,
        ProductCategoryRepository $productCategoryRepository,
        ProductInfoRepository     $productInfoRepository,
        ProductAssistService      $productAssistService,
        ProductBrandRepository    $productBrandRepository,
        ProductSpecService        $productSpecService
    )
    {
        $this->repository = $productTypeRepository;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->productInfoRepository = $productInfoRepository;
        $this->productAssistService = $productAssistService;
        $this->productBrandRepository = $productBrandRepository;
        $this->productSpecService = $productSpecService;
    }


    /**
     * 修改
     * @param $type_id
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function edit($type_id, $request)
    {
        $spec_ids = $request->get('spec_ids');

        if ($type_id) {
            $product_type_row = $this->repository->getOne($type_id);
            if ($product_type_row) {
                $spec_ids_old = $product_type_row['spec_ids'];
                $spec_ids_old_row = explode(',', $spec_ids_old);
                $spec_ids_row = explode(',', $spec_ids);

                foreach ($spec_ids_old_row as $type_spec_id_old) {
                    if (!in_array($type_spec_id_old, $spec_ids_row)) {
                        $product_spec_rows = $this->productInfoRepository->find([['spec_ids', 'FIND_IN_SET', [$type_spec_id_old]]]);
                        $count = count($product_spec_rows);
                        if ($count > 0) {
                            throw new ErrorException(sprintf(__("规格 %d 已经被 %d 个SPU商品使用，不可取消关联"), $type_spec_id_old, $count));
                        }
                    }
                }
            }
        }

        try {
            return $this->edit($type_id, $request);
        } catch (\Exception $e) {
            throw new ErrorException(__('修改失败: ') . $e->getMessage());
        }
    }


    /**
     * 删除
     * @param $type_id
     * @return bool
     * @throws ErrorException
     */
    public function remove($type_id)
    {
        $type_row = $this->repository->getOne($type_id);
        if (empty($type_row)) {
            throw new ErrorException(__('类型不存在'));
        }

        if ($type_row['type_buildin']) {
            throw new ErrorException(__("系统内置，不可删除"));
        }

        $count = $this->productCategoryRepository->getNum(['type_id' => $type_id]);
        if ($count > 0) {
            throw new ErrorException(sprintf(__('有 %d 条分类使用，不可删除'), $count));
        }

        $result = $this->repository->remove($type_id);

        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }
    }


    /**
     * 获取类型和类型绑定的属性、规格、品牌
     * @param $type_id
     * @return mixed
     */
    public function getInfo($type_id = null)
    {
        $product_type = $this->repository->getOne($type_id);

        //todo 获取该类型下的品牌
        $brand_ids = explode(',', $product_type['brand_ids']);
        $brand_rows = $this->productBrandRepository->gets($brand_ids);
        $product_type['brands'] = array_values($brand_rows);

        //todo 获取该类型下的属性
        $assist_ids = explode(',', $product_type['assist_ids']);
        $assist_rows = $this->productAssistService->getAssistItems($assist_ids);
        $product_type['assists'] = array_values($assist_rows);

        //todo 获取该类型下的规格
        $spec_ids = explode(',', $product_type['spec_ids']);
        $spec_rows = $this->productSpecService->getSpecItems($spec_ids);
        $product_type['specs'] = array_values($spec_rows);

        return $product_type;
    }

}
