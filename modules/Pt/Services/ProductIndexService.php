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

use App\Support\StateCode;
use Illuminate\Support\Facades\Log;
use Kuteshop\Core\Service\BaseService;
use Modules\Marketing\Services\ActivityItemService;
use Modules\Pt\Repositories\Contracts\ProductBaseRepository;
use Modules\Pt\Repositories\Contracts\ProductImageRepository;
use Modules\Pt\Repositories\Contracts\ProductIndexRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductInfoRepository;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;
use Modules\Pt\Repositories\Criteria\ProductItemCriteria;
use Modules\Shop\Services\StoreTransportTypeService;
use Modules\Sys\Repositories\Contracts\ContractTypeRepository;
use Modules\Sys\Repositories\Contracts\DistrictBaseRepository;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Contracts\UserLevelRepository;

/**
 * Class ProductIndexService.
 *
 * @package Modules\Pt\Services
 */
class ProductIndexService extends BaseService
{
    private $productIndexRepository;
    private $productBaseRepository;
    private $productInfoRepository;
    private $productItemRepository;
    private $productImageRepository;
    private $districtBaseRepository;
    private $contractTypeRepository;

    private $storeTransportTypeService;
    private $activityItemService;
    private $userInfoRepository;
    private $userLevelRepository;

    public function __construct(
        ProductIndexRepository    $productIndexRepository,
        ProductBaseRepository     $productBaseRepository,
        ProductInfoRepository     $productInfoRepository,
        ProductItemRepository     $productItemRepository,
        ProductImageRepository    $productImageRepository,
        DistrictBaseRepository    $districtBaseRepository,
        ContractTypeRepository    $contractTypeRepository,
        UserInfoRepository        $userInfoRepository,
        UserLevelRepository       $userLevelRepository,

        StoreTransportTypeService $storeTransportTypeService,
        ActivityItemService       $activityItemService
    )
    {
        $this->productIndexRepository = $productIndexRepository;
        $this->productBaseRepository = $productBaseRepository;
        $this->productInfoRepository = $productInfoRepository;
        $this->productItemRepository = $productItemRepository;
        $this->productImageRepository = $productImageRepository;
        $this->districtBaseRepository = $districtBaseRepository;
        $this->contractTypeRepository = $contractTypeRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->userLevelRepository = $userLevelRepository;

        $this->storeTransportTypeService = $storeTransportTypeService;
        $this->activityItemService = $activityItemService;
    }


    /**
     * 获取列表
     * @return array
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;

        if ($request->get('sidx', '')) {
            $sort_key = $request->get('sidx', '');
            $sort = $request->get('sort', 'ASC');
            $this->productIndexRepository->orderBy($sort_key, $sort);
        }

        $data = $this->productIndexRepository->list($criteria, $limit);
        if ($data['data']) {

            //获取商品基础信息
            $product_ids = array_column($data['data'], 'product_id');
            $product_base_rows = $this->productBaseRepository->gets($product_ids);

            //获取商品SKU信息
            $item_condition = [['product_id', 'IN', $product_ids]];
            if ($request->has('product_state_id')) {
                //商品状态
                $item_condition[] = ['item_enable', '=', $request->get('product_state_id')];
            }
            $orderBy = [
                'item_is_default' => 'DESC',
                'item_id' => 'ASC'
            ];
            $product_items = $this->productItemRepository->find($item_condition, $orderBy);
            $product_items_map = arrayMap($product_items, 'product_id');

            foreach ($data['data'] as $k => $v) {
                $product_id = $v['product_id'];
                $data['data'][$k]['product_image'] = $product_base_rows[$product_id]['product_image'];
                $data['data'][$k]['product_commission_rate'] = $product_base_rows[$product_id]['product_commission_rate'];

                if (isset($product_items_map[$product_id])) {
                    $data['data'][$k]['item_id'] = $product_items_map[$product_id][0]['item_id'];
                    $data['data'][$k]['items'] = $product_items_map[$product_id];
                }

            }
        }

        $data['limit'] = $limit;

        return $data;
    }


    /**
     * 商品详情
     * @param $item_id
     * @param $district_id
     * @param $gb_id
     * @param $user_id
     * @return array
     * @throws ErrorException
     */
    public function detail($item_id = 0, $district_id = 0, $gb_id = 0, $user_id = 0)
    {

        $product_item = $this->productItemRepository->getOne($item_id);
        if (empty($product_item)) {
            throw new ErrorException(__('商品SKU不存在！'));
        }

        // todo 获取商品其他信息
        $product_id = $product_item['product_id'];
        $product_base = $this->productBaseRepository->getOne($product_id);
        $product_info = $this->productInfoRepository->getOne($product_id);
        $product_index = $this->productIndexRepository->getOne($product_id);
        $detail = array_merge($product_base, $product_info, $product_index);

        $product_item['item_sale_price'] = $product_item['item_unit_price'];

        //todo 读取活动信息
        $activity_info_rows = $this->activityItemService->getActivityInfo([$product_item['item_id']], $user_id);
        if (!empty($activity_info_rows)) {
            $activity_info_row = current($activity_info_rows);
            $product_item['activity_id'] = $activity_info_row['activity_id'];
            $product_item['activity_info'] = $activity_info_row;

            if (!empty($activity_info_row) && isset($activity_info_row['activity_item_price'])) {
                $product_item['item_sale_price'] = $activity_info_row['activity_item_price'];
            }
        }

        $detail['item_id'] = $product_item['item_id'];
        $detail['item_row'] = $product_item;

        // SKU图片
        $image_rows = $this->productImageRepository->find([
            'product_id' => $product_id,
            'color_id' => $product_item['color_id']
        ]);
        $detail['image'] = !empty($image_rows) ? current($image_rows) : [];
        if (!empty($detail['image']) && $detail['image']['item_image_default']) {
            $detail['product_image'] = $detail['image']['item_image_default'];
        }

        //商品名称+SKU名称
        $item_spec_name = str_replace(',', ' ', $product_item['item_name']);
        $detail['product_item_name'] = $detail['product_name'] . " " . $item_spec_name;

        // 是否可销售
        $product_item['available_quantity'] = $product_item['item_quantity'] - $product_item['item_quantity_frozen'];
        if ($product_item['available_quantity'] > 0) {
            $detail['if_store'] = true;

            // 可售区域
            if ($district_id) {

                // 读取上级分类信息
                $district_base = $this->districtBaseRepository->getOne($district_id);
                if (!empty($district_base)) {
                    $district_id = $district_base['district_parent_id'];
                }

                $store_transport_item_row = $this->storeTransportTypeService->getFreight($product_base['transport_type_id'], $district_id);
                if (empty($store_transport_item_row)) {
                    $detail['if_store'] = false;
                } else {
                    if (!$store_transport_item_row['transport_type_free'] && empty($store_transport_item_row['item'])) {
                        $detail['if_store'] = false;
                    }

                    $transport_item = $store_transport_item_row['item'];
                    if (!empty($transport_item)) {
                        $detail['freight'] = $transport_item['transport_item_default_price'];
                    }
                }
            }
        } else {
            $detail['if_store'] = false;
        }

        // 服务
        $contract_types = $this->contractTypeRepository->find(['contract_type_enable' => true]);
        if (!empty($contract_types)) {
            $detail['contracts'] = array_values($contract_types);
        }

        return $detail;

    }


    /**
     * 获取商品SKU列表
     * @param $request
     * @return array
     */
    public function listItem($request)
    {
        $limit = $request->get('size') ?? 10;

        //商品名称筛选
        $index_cond = [];
        if ($product_name = $request->input('product_name')) {
            $index_cond[] = ['product_name', 'LIKE', '%' . $product_name . '%'];
        }
        if ($category_id = $request->input('category_id', 0)) {
            $index_cond['category_id'] = $category_id;
        }
        if ($product_id = $request->input('product_id', 0)) {
            $index_cond['product_id'] = $product_id;
        }
        if (!empty($index_cond)) {
            $product_ids = $this->productIndexRepository->findKey($index_cond);
            $request['product_ids'] = $product_ids;
        }

        $data = $this->productItemRepository->list(new ProductItemCriteria($request), $limit);

        if (!empty($data['data'])) {
            $product_ids = array_column_unique($data['data'], 'product_id');
            $product_base_rows = $this->productBaseRepository->gets($product_ids);
            $product_index_rows = $this->productIndexRepository->gets($product_ids);

            foreach ($data['data'] as $k => $row) {
                $product_id = $row['product_id'];
                if (isset($product_base_rows[$product_id])) {
                    $data['data'][$k]['product_image'] = $product_base_rows[$product_id]['product_image'];
                    $data['data'][$k]['product_tips'] = $product_base_rows[$product_id]['product_tips'];
                }

                if (isset($product_index_rows[$product_id])) {
                    $data['data'][$k]['product_name'] = $product_index_rows[$product_id]['product_name'];
                    $data['data'][$k]['product_state_id'] = $product_index_rows[$product_id]['product_state_id'];
                    $data['data'][$k]['product_item_name'] = $product_index_rows[$product_id]['product_name'] . $row['item_name'];
                    $data['data'][$k]['item_spec_name'] = $data['data'][$k]['product_item_name'];
                }
            }
        }

        return $data;
    }


    /**
     * 根据item_id 获取商品信息
     * @param array $item_ids
     * @param int $user_id
     * @return array
     * @throws ErrorException
     */
    public function getItems(array $item_ids = [], int $user_id = 0)
    {
        $return_product_items = [];

        if (!empty($item_ids)) {
            $product_items = $this->productItemRepository->gets($item_ids);
            if (!empty($product_items)) {
                $product_ids = array_column_unique($product_items, 'product_id');
                $product_base_rows = $this->productBaseRepository->gets($product_ids);
                $product_index_rows = $this->productIndexRepository->gets($product_ids);
                $product_images = $this->productImageRepository->find([['product_id', 'IN', $product_ids]]);

                //todo 获取限时折扣商品信息
                $activity_item_rows = $this->activityItemService->getActivityInfo($item_ids, $user_id);

                foreach ($product_items as $product_item) {
                    $item_id = $product_item['item_id'];
                    $product_id = $product_item['product_id'];
                    $product_item['item_sale_price'] = $product_item['item_unit_price'];

                    //todo 绑定商品活动价格
                    if (!empty($activity_item_rows) && isset($activity_item_rows[$item_id])) {
                        $activity_ite_row = $activity_item_rows[$item_id];
                        $product_item['activity_id'] = $activity_ite_row['activity_id'];
                        $product_item['activity_type_id'] = $activity_ite_row['activity_type_id'];
                        $product_item['activity_info'] = $activity_ite_row;
                        if (isset($activity_ite_row['activity_item_price'])) {
                            $product_item['activity_item_price'] = $activity_ite_row['activity_item_price'];
                            $product_item['item_sale_price'] = $activity_ite_row['activity_item_price'];
                        }
                    }

                    $product_base = $product_base_rows[$product_id];
                    $product_index = $product_index_rows[$product_id];
                    if (empty($product_base) || empty($product_index)) {
                        throw new ErrorException(__('商品信息不存在'));
                    }

                    $product_name = $product_base['product_name'];
                    $product_item['product_name'] = $product_name;
                    $product_item['product_tips'] = $product_base['product_tips'];
                    $item_spec_name = str_replace(',', ' ', $product_item['item_name']);
                    $product_item['product_item_name'] = $product_name . " " . $item_spec_name;
                    $product_item['product_commission_rate'] = $product_base['product_commission_rate'] ?? 0;
                    $product_item['product_buy_limit'] = $product_base['product_buy_limit'] ?? 0;

                    $product_item['transport_type_id'] = $product_base['transport_type_id'] ?? 0;
                    $product_item['product_tags'] = $product_index['product_tags'];
                    $product_item['product_dist_enable'] = $product_index['product_dist_enable'];
                    $product_item['product_inventory_lock'] = $product_index['product_inventory_lock'];
                    $product_item['kind_id'] = $product_index['kind_id'];
                    $product_item['activity_type_ids'] = $product_index['activity_type_ids'];
                    $product_item['available_quantity'] = $product_item['item_quantity'] - $product_item['item_quantity_frozen'];

                    //商品状态
                    $product_state_id = $product_index['product_state_id'];
                    if ($product_item['item_enable'] == StateCode::PRODUCT_STATE_NORMAL) {
                        if ($product_item['available_quantity'] > 0) {
                            $product_item['is_on_sale'] = 1;
                        } else {
                            $product_state_id = StateCode::PRODUCT_STATE_OFF_THE_SHELF;
                        }
                    } else {
                        $product_state_id = StateCode::PRODUCT_STATE_OFF_THE_SHELF;
                    }
                    $product_item['product_state_id'] = $product_state_id;


                    //商品图片
                    $product_item['product_image'] = $product_base['product_image'];
                    if ($product_item['color_id']) {
                        $product_images_map = arrayMap($product_images, 'product_id');
                        if (isset($product_images_map[$product_id])) {
                            foreach ($product_images_map[$product_id] as $image) {
                                if ($image['color_id'] == $product_item['color_id']) {
                                    $product_item['product_image'] = $image['item_image_default'];
                                }
                            }
                        }
                    }

                    $return_product_items[$item_id] = $product_item;
                }
            }
        }

        return $return_product_items;
    }


    /**
     * 定时上架商品
     * @return void
     */
    public function autoSaleProduct()
    {
        $time = getTime();

        // 查询待上架商品
        $column_row = [
            ['product_verify_id', '=', StateCode::PRODUCT_VERIFY_PASSED],
            ['product_state_id', '=', StateCode::PRODUCT_STATE_OFF_THE_SHELF],
            ['product_sale_time', '<=', $time],
        ];
        $product_index_rows = $this->productIndexRepository->find($column_row, ['product_id']);

        if (empty($product_index_rows)) {
            Log::info(__("没有待上架的商品."));
            return;
        }

        // 查询商品 SKU
        $product_ids = array_column($product_index_rows, 'product_id');
        $product_items = $this->productItemRepository->find([
            ['product_id', 'IN', $product_ids]
        ]);
        if (empty($product_items)) {
            Log::info(__("商品SKU集合为空！"));
            return;
        }

        // 按 product_id 分组 SKU
        $product_item_rows = arrayMap($product_items, 'product_id');
        $edit_product_ids = [];
        foreach ($product_index_rows as $product_index_row) {
            $product_id = $product_index_row['product_id'];
            if (!isset($product_item_rows[$product_id])) {
                continue;
            }

            $item_enable = array_reduce($product_item_rows[$product_id], function ($carry, $item) {
                return $carry || ($item['item_enable'] === StateCode::PRODUCT_STATE_NORMAL);
            }, false);

            if ($item_enable) {
                $edit_product_ids[] = $product_id;
            }
        }

        if (!empty($edit_product_ids)) {
            try {
                $this->productIndexRepository->edits($edit_product_ids, [
                    'product_state_id' => StateCode::PRODUCT_STATE_NORMAL,
                    'product_sale_time' => $time
                ]);
                Log::info(__("商品定时上架成功"), ['product_ids' => $edit_product_ids]);
            } catch (\Exception $e) {
                Log::error(__("商品定时上架失败:") . $e->getMessage());
            }
        }

    }


}
