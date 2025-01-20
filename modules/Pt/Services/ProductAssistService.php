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
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductCategoryRepository;

/**
 * Class ProductAssistService.
 *
 * @package Modules\Pt\Services
 */
class ProductAssistService extends BaseService
{
    private $productAssistItemRepository;
    private $productCategoryRepository;

    public function __construct(
        ProductAssistRepository     $productAssistRepository,
        ProductAssistItemRepository $productAssistItemRepository,
        ProductCategoryRepository   $productCategoryRepository
    )
    {
        $this->repository = $productAssistRepository;
        $this->productAssistItemRepository = $productAssistItemRepository;
        $this->productCategoryRepository = $productCategoryRepository;
    }


    /**
     * 删除
     * @param $assist_id
     * @return bool
     * @throws ErrorException
     */
    public function remove($assist_id)
    {
        $count = $this->productAssistItemRepository->getNum([['assist_id', '=', $assist_id]]);
        if ($count > 0) {
            throw new ErrorException(sprintf(__('有 %d 个属性选项，不可删除！'), $count));
        }

        $result = $this->repository->remove($assist_id);

        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('删除失败'));
        }
    }


    /**
     * 获取属性选项
     * @param $assist_ids
     * @return array|mixed
     */
    public function getAssistItems($assist_ids = [-1])
    {
        $assist_list = $this->repository->gets($assist_ids);
        if ($assist_list) {
            $assist_item_list = $this->productAssistItemRepository->find([['assist_id', 'IN', $assist_ids]]);
            $assist_items = [];
            foreach ($assist_item_list as $assist_item) {
                if (!array_key_exists($assist_item['assist_id'], $assist_items)) {
                    $assist_items[$assist_item['assist_id']] = [];
                }
                $assist_items[$assist_item['assist_id']][] = $assist_item;
            }

            foreach ($assist_list as $assist_id => $assist_row) {
                if (isset($assist_items[$assist_row['assist_id']])) {
                    $assist_list[$assist_id]['items'] = $assist_items[$assist_row['assist_id']];
                }
            }
        }

        return $assist_list;
    }


    /**
     * 获取属性树形数据
     *
     * @return array
     */
    public function getTree()
    {

        $rows = [];

        $assists_rows = $this->repository->find([], ['assist_sort' => 'ASC', 'assist_id' => 'ASC']);
        $category_ids = array_column_unique($assists_rows, 'category_id');
        $category_rows = $this->productCategoryRepository->gets($category_ids);

        // 遍历分类，按分类将属性分组
        foreach ($category_rows as $category_row) {
            $assists = [];

            // 遍历属性数据，并按 category_id 匹配分类
            foreach ($assists_rows as $assists_row) {
                if ($assists_row['category_id'] == $category_row['category_id']) {
                    $assists[] = $assists_row;
                }
            }

            // 如果有对应的属性，则构建树形数据
            if (!empty($assists)) {
                $rows[] = [
                    'assist_id' => $category_row['category_id'],
                    'assist_name' => $category_row['category_name'],
                    'children' => $assists
                ];
            }
        }

        return $rows;
    }


}
