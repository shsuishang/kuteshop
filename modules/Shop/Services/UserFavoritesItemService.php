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


namespace Modules\Shop\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductBaseRepository;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;
use Modules\Shop\Repositories\Contracts\UserFavoritesItemRepository;
use App\Exceptions\ErrorException;

/**
 * Class UserFavoritesItemService.
 *
 * @package Modules\Shop\Services
 */
class UserFavoritesItemService extends BaseService
{
    private $productItemRepository;
    private $productBaseRepository;

    public function __construct(
        UserFavoritesItemRepository $userFavoritesItemRepository,
        ProductItemRepository       $productItemRepository,
        ProductBaseRepository       $productBaseRepository
    )
    {
        $this->repository = $userFavoritesItemRepository;
        $this->productItemRepository = $productItemRepository;
        $this->productBaseRepository = $productBaseRepository;
    }


    /**
     * 获取列表
     * @return array
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list($criteria, $limit);
        if (!empty($data['data'])) {
            $items = $data['data'];
            $item_ids = array_column($items, 'item_id');
            $product_items = $this->productItemRepository->gets($item_ids);
            $product_ids = array_column($product_items, 'product_id');
            $product_rows = $this->productBaseRepository->gets($product_ids);
            foreach ($items as $k => $item) {
                if (isset($product_items[$item['item_id']])) {
                    $product_item = $product_items[$item['item_id']];
                    $items[$k]['item_unit_price'] = $product_item['item_unit_price'];
                    $product_id = $product_item['product_id'];
                    if (isset($product_rows[$product_id])) {
                        $items[$k]['product_image'] = $product_rows[$product_id]['product_image'];
                        $items[$k]['product_item_name'] = $product_rows[$product_id]['product_name'] . $product_item['item_name'];
                    }
                }
            }

            $data['data'] = $items;
        }

        return $data;
    }


    /**
     * 根据商品SKU删除
     * @param $user_id
     * @param $item_id
     * @return bool
     * @throws ErrorException
     */
    public function removeByItemId($user_id = 0, $item_id = 0)
    {
        if (!$user_id || !$item_id) {
            throw new ErrorException(__('数据有误！'));
        }

        $favorites_item_ids = $this->repository->findKey([
            'item_id' => $item_id,
            'user_id' => $user_id
        ]);

        if ($favorites_item_ids) {
            $this->repository->remove($favorites_item_ids);

            return true;
        }

        throw new ErrorException(__('删除失败'));
    }

}
