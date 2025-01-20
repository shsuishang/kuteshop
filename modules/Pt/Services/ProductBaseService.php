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
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Repositories\Contracts\ProductBaseRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductCategoryRepository;
use Modules\Pt\Repositories\Contracts\ProductImageRepository;
use Modules\Pt\Repositories\Contracts\ProductIndexRepository;
use Modules\Pt\Repositories\Contracts\ProductInfoRepository;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;
use Modules\Pt\Repositories\Contracts\ProductValidPeriodRepository;
use Modules\Sys\Services\ConfigBaseService;
use Modules\Sys\Services\NumberSeqService;

/**
 * Class ProductBaseService.
 *
 * @package Modules\Pt\Services
 */
class ProductBaseService extends BaseService
{

    private $productIndexRepository;
    private $productCategoryRepository;
    private $productItemRepository;
    private $productInfoRepository;
    private $productImageRepository;
    private $productValidPeriodRepository;
    private $numberSeqService;
    private $configBaseService;

    public function __construct(
        ProductBaseRepository        $productBaseRepository,
        ProductIndexRepository       $productIndexRepository,
        ProductCategoryRepository    $productCategoryRepository,
        ProductItemRepository        $productItemRepository,
        ProductInfoRepository        $productInfoRepository,
        ProductImageRepository       $productImageRepository,
        ProductValidPeriodRepository $productValidPeriodRepository,
        NumberSeqService             $numberSeqService,
        ConfigBaseService            $configBaseService)
    {
        $this->repository = $productBaseRepository;
        $this->productIndexRepository = $productIndexRepository;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->productItemRepository = $productItemRepository;
        $this->productInfoRepository = $productInfoRepository;
        $this->productImageRepository = $productImageRepository;
        $this->productValidPeriodRepository = $productValidPeriodRepository;
        $this->numberSeqService = $numberSeqService;
        $this->configBaseService = $configBaseService;
    }


    /**
     * 获取列表
     * @return array
     */
    /*public function list($request)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list(new ProductBaseCriteria($request), $limit);

        return $data;
    }*/


    /**
     * 新增或修改
     * @param $request
     * @param $product_id
     * @return bool
     * @throws ErrorException
     */
    public function saveProduct($request, $product_id = null)
    {
        $store_id = 0;
        if (!$product_id) {
            $product_id = $request->input('product_id', 0);
            if (!$product_id) {
                $product_id = $this->numberSeqService->getNextSeq("product_id");
            }
        }
        if (!$product_id) {
            throw new ErrorException(__('商品编号有误'));
        }

        $product_items = $request->get('product_items') ?? '{}';
        $product_items = json_decode($product_items, true);

        //设置默认Item
        if (!empty($product_items)) {
            $item_is_defaults = array_column($product_items, 'item_is_default');
            if (!in_array(1, $item_is_defaults)) {
                $product_items[0]['item_is_default'] = 1;
            }
        }

        //todo 处理商品主图
        $product_image = '';
        $product_images = $request->get('product_images') ?? '{}';
        $product_images = json_decode($product_images, true);

        //todo 根据默认商品获取默认主图
        foreach ($product_items as $product_item) {
            if ($product_item['item_is_default']) {
                foreach ($product_images as $image) {
                    if ($image['color_id'] == $product_item['color_id']) {
                        $product_image = $image['item_image_default'];
                        if (!$product_image) {
                            $product_image = $this->configBaseService->getConfig('default_image');
                        }
                        break;
                    }
                }

                break;
            }
        }

        DB::beginTransaction();

        //todo 1、保存商品基础数据
        $product_base_row = [
            'product_id' => $product_id,
            'product_number' => $request->input('product_number', ''),//SPU货号:货号
            'product_name' => $request['product_name'],
            'product_tips' => $request->input('product_tips', ''), //商品卖点:商品广告词
            'store_id' => $store_id,
            'product_image' => $product_image, //商品主图
            'product_video' => $request->input('product_video', ''), //产品视频 URL
            'transport_type_id' => $request->input('transport_type_id', 0), //选择售卖区域:完成售卖区域及运费设置
            'product_buy_limit' => $request->input('product_buy_limit', 0)//每人限购
        ];
        $result = $this->repository->save(['product_id' => $product_id], $product_base_row);
        if (!$result) {
            throw new ErrorException('商品基数数据错误');
        }

        //todo 商品属性
        $product_assist_str = $request->get('product_assist') ?? '{}';
        $product_assist_rows = json_decode($product_assist_str, true);
        $product_assist_data = [];
        foreach ($product_assist_rows as $assist_id => $product_assist) {
            $product_assist_data = array_merge($product_assist_data, $product_assist);
        }

        //todo 2、保存商品Index数据
        $request['product_id'] = $product_id;
        $request['product_assist_data'] = implode(',', $product_assist_data);
        $request['product_is_video'] = $product_base_row['product_video'] ? 1 : 0;
        $request['product_unit_price_min'] = min(array_column($product_items, 'item_unit_price'));
        $request['product_unit_price_max'] = max(array_column($product_items, 'item_unit_price'));
        $category = $this->productCategoryRepository->getOne($request['category_id']);
        $request['type_id'] = $category['type_id'];
        $product_index_row = $this->formatIndexData($request);
        $product_index_result = $this->productIndexRepository->save(['product_id' => $product_id], $product_index_row);
        if (!$product_index_result) {
            throw new ErrorException(__('商品索引数据错误'));
        }

        $product_uniqid = [];
        //todo 3、商品Item数据
        $product_item_exist = $this->productItemRepository->find(array('product_id' => $product_id));
        if ($product_item_exist) {
            //查找当前商品下信息，删除废弃的， 取差集
            $product_item_id_row = array_column($product_item_exist, 'item_id');
            $del_item_id_row = array_diff($product_item_id_row, array_column($product_items, 'item_id'));
            if ($del_item_id_row) {
                $this->productItemRepository->remove($del_item_id_row);
            }
        }
        foreach ($product_items as $k => $product_item) {
            $spec_item_ids = [];
            $specs = json_decode($product_item['item_spec'], true);
            $item_name = [];
            foreach ($specs as $spec) {
                $item = $spec["item"];
                $spec_item_ids[] = intval($item["id"]);
                if (isset($product_item[$spec['id']])) {
                    $item_name[] = $product_item[$spec['id']];
                }
            }
            sort($spec_item_ids);

            $item_row = [
                'product_id' => $product_id,
                'category_id' => $request['category_id'],
                'item_name' => implode(',', $item_name),
                'color_id' => $product_item['color_id'],
                'item_is_default' => $product_item['item_is_default'],
                'item_enable' => $product_item['item_enable'],
                'item_market_price' => $product_item['item_market_price'],
                'item_unit_price' => $product_item['item_unit_price'],
                'item_quantity' => $product_item['item_quantity'],
                'item_spec' => $product_item['item_spec'],
                'spec_item_ids' => implode(',', $spec_item_ids),
                'store_id' => $store_id
            ];
            if (!isset($product_item['item_id'])) {
                $item_result = $this->productItemRepository->add($item_row);
                $item_id = $item_result->getKey('item_id');
            } else {
                $item_id = $product_item['item_id'];
                $item_result = $this->productItemRepository->edit($item_id, $item_row);
            }
            if (!$item_result) {
                throw new ErrorException(__('商品SKU数据保存有误'));
            }

            $color_image = "";
            foreach ($product_images as $image) {
                if ($image['color_id'] == $product_item['color_id']) {
                    $color_image = $image['item_image_default'];
                    break;
                }
            }

            $product_uniqid[implode("-", $spec_item_ids)] = [
                $item_id,
                $product_item['item_unit_price'],
                $product_item['item_quantity'],
                $product_item['item_enable'],
                $product_item['color_id'],
                $color_image
            ];
        }

        //todo 4、保存商品Info
        $spec_list = json_decode($request['product_spec']);
        $spec_ids = array_column($spec_list, 'id');
        $product_info_result = $this->productInfoRepository->save(['product_id' => $product_id], [
            'product_id' => $product_id,
            'product_assist' => $product_assist_str,
            'product_spec' => $request['product_spec'],
            'product_uniqid' => json_encode($product_uniqid),
            'product_detail' => $request['product_detail'],
            'product_meta_title' => $request->input('product_meta_title', $request['product_name']),
            'product_meta_description' => $request->input('product_meta_description', $request['product_name']),
            'product_meta_keyword' => $request->input('product_meta_keyword', $request['product_name']),
            'spec_ids' => implode(',', $spec_ids)
        ]);
        if (!$product_info_result) {
            throw new ErrorException(__("商品信息数据错误"));
        }

        //todo 5、商品图片数据
        $product_images_exist = $this->productImageRepository->find(['product_id' => $product_id]);
        if ($product_images_exist) {
            //查找当前商品下信息，删除废弃的， 取差集
            $product_image_id_row = array_column($product_images_exist, 'product_image_id');
            $del_image_id_row = array_diff($product_image_id_row, array_column($product_images, 'product_image_id'));
            if ($del_image_id_row) {
                $this->productImageRepository->remove($del_image_id_row);
            }
        }
        foreach ($product_images as $k => $product_image) {
            $product_image['product_id'] = $product_id;
            if (isset($product_image['product_image_id'])) {
                $product_image_id = $product_image['product_image_id'];
                unset($product_image['product_image_id']);
                $result = $this->productImageRepository->edit($product_image_id, $product_image);
            } else {
                $result = $this->productImageRepository->add($product_image);
            }
        }

        if ($request['kind_id'] == StateCode::PRODUCT_KIND_FUWU) {
            //todo 6、保存虚拟商品数据
            $valid_data = $this->formatValidData($request);
            $valid_data['product_id'] = $product_id;
            $result = $this->productValidPeriodRepository->add($valid_data);
        }

        if ($result) {
            DB::commit();
            return ['product_id' => $product_id];
        } else {
            DB::rollBack();
            throw new ErrorException('操作失败');
        }
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatIndexData($request)
    {
        $data['product_id'] = $request['product_id'];
        $data['product_name'] = $request['product_name']; //商品名称
        $data['product_number'] = $request->input('product_number', ''); //货号
        $data['kind_id'] = $request['kind_id']; //商品种类:1201-实物;1202-虚拟
        $data['store_id'] = 0; //店铺编号
        $data['category_id'] = $request['category_id']; //商品分类ID
        $data['type_id'] = $request['type_id']; //类型编号
        $data['brand_id'] = $request['brand_id']; //品牌编号
        $data['product_assist_data'] = $request['product_assist_data'];
        $data['product_unit_price_min'] = $request['product_unit_price_min']; //最低单价
        $data['product_unit_price_max'] = $request['product_unit_price_max']; //最高单价
        $data['product_tags'] = $request['product_tags']; //商品标签(DOT)
        $data['product_sp_enable'] = 0; //允许分销(BOOL):1-启用分销;0-禁用分销
        $data['product_dist_enable'] = 0; //三级分销允许分销(BOOL):1-启用分销;0-禁用分销
        $data['product_add_time'] = getTime(); //添加时间
        $data['product_order'] = $request->input('product_order', 0); //排序:越小越靠前
        $data['product_is_video'] = $request['product_is_video']; //是否视频(BOOL):1-有视频;0-无视频
        $data['product_transport_id'] = $request->input('product_transport_id', 1001); //配送服务(ENUM):1001-快递发货;1002-到店自提;1003-上门服务
        $data['subsite_id'] = 0; //所属分站:0-总站
        $data['product_inventory_lock'] = $request['product_inventory_lock'];//库存锁定(ENUM):1001-下单锁定;1002-支付锁定;
        $data['product_from'] = 1000; //商品来源(ENUM):1000-发布;1001-天猫;1002-淘宝;1003-阿里巴巴;1004-京东;
        $data['product_verify_id'] = StateCode::PRODUCT_VERIFY_PASSED;

        $data['product_state_id'] = $request['product_state_id'];
        if ($data['product_state_id'] == StateCode::PRODUCT_STATE_OFF_THE_SHELF) {
            $data['product_sale_time'] = $request['product_sale_time']; //销售时间
        } else {
            $data['product_sale_time'] = $data['product_add_time']; //上架时间:预设上架时间,可以动态修正状态
        }

        return $data;
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatValidData($request)
    {
        $data = [
            'product_valid_period' => $request['product_valid_period'], //有效期:1001-长期有效;1002-自定义有效期;1003-购买起有效时长年单位
            'product_validity_start' => $request['product_validity_start'], //开始时间
            'product_validity_end' => $request['product_validity_end'], //失效时间
            'product_validity_duration' => $request->input('product_validity_duration', 0), //有效时长单位为天
            'product_valid_type' => $request['product_valid_type'], //服务类型(ENUM):1001-到店服务;1002-上门服务
            'product_service_date_flag' => $request->boolean('product_service_date_flag'), //填写预约日期(BOOL):0-否;1-是
            'product_service_contactor_flag' => $request->boolean('product_service_contactor_flag'), //填写联系人(BOOL):0-否;1-是
            'product_valid_refund_flag' => $request->boolean('product_valid_refund_flag') //支持过期退款(BOOL):0-否;1-是
        ];

        return $data;
    }


    /**
     * @param $product_id
     * @return array
     * @throws ErrorException
     */
    public function getProduct($product_id = null)
    {
        $product_data = [];

        // 基础表
        $product_base = $this->repository->getOne($product_id);
        if (empty($product_base)) {
            throw new ErrorException(__("未找到该商品！"));
        }
        $product_data['product_base'] = $product_base;

        // 索引表
        $product_index = $this->productIndexRepository->getOne($product_id);
        if (empty($product_index)) {
            throw new ErrorException(__("商品索引数据有误！"));
        }
        $product_data['product_index'] = $product_index;

        // 信息表
        $product_info = $this->productInfoRepository->getOne($product_id);
        if (empty($product_info)) {
            throw new ErrorException(__("商品信息数据有误！"));
        }
        $product_data['product_info'] = $product_info;

        // SKU表
        $product_items = $this->productItemRepository->find(['product_id' => $product_id]);
        if (empty($product_items)) {
            throw new ErrorException(__("商品SKU数据有误！"));
        }
        $product_data['product_item'] = array_values($product_items);

        // 图片表
        $product_images = $this->productImageRepository->find(['product_id' => $product_id]);
        if (empty($product_images)) {
            throw new ErrorException(__("商品图片数据有误！"));
        }
        $product_data['product_image'] = array_values($product_images);

        //虚拟商品信息
        if ($product_index['kind_id'] == StateCode::PRODUCT_KIND_FUWU) {
            $product_valid_period = $this->productValidPeriodRepository->getOne($product_id);
            $product_data['product_valid_period'] = $product_valid_period;
        }

        return $product_data;
    }


    /**
     * 修改商品状态
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function editState($request)
    {
        $product_id = $request->get('product_id');
        $state_data = [];

        // 提取可选状态字段，并转换为 bool 值
        if ($request->has('product_state_id')) {
            $state_data['product_state_id'] = $request['product_state_id'];
        }

        // 三级分销状态
        if ($request->has('product_dist_enable')) {
            $state_data['product_dist_enable'] = $request->boolean('product_dist_enable');
        }

        // 更新状态
        if ($product_id && !empty($state_data)) {
            $result = $this->productIndexRepository->edit($product_id, $state_data);
            if ($result) {
                return true;
            } else {
                throw new ErrorException(__('更新失败'));
            }
        }

        return true;
    }


    /**
     * 删除商品
     * @param $product_id
     * @return bool
     * @throws ErrorException
     */
    public function removeProduct($product_id = null)
    {
        if (!$product_id) {
            throw new ErrorException(__('商品编号有误'));
        }

        DB::beginTransaction();
        $flag_row = [];

        $flag_row[] = $this->productInfoRepository->remove($product_id);  //删除商品信息表数据
        $flag_row[] = $this->productIndexRepository->remove($product_id); //删除商品索引表数据

        //删除商品SKU
        $product_items = $this->productItemRepository->find(['product_id' => $product_id]);
        if ($product_items) {
            $item_ids = array_column($product_items, 'item_id');
            $flag_row[] = $this->productItemRepository->remove($item_ids);
        }

        //删除商品图片
        $product_images = $this->productImageRepository->find(['product_id' => $product_id]);
        if ($product_images) {
            $product_image_ids = array_column($product_images, 'product_image_id');
            $flag_row[] = $this->productImageRepository->remove($product_image_ids);
        }

        //删除虚拟商品信息
        $product_valid_period = $this->productValidPeriodRepository->find(['product_id' => $product_id]);
        if (!empty($product_valid_period)) {
            $flag_row[] = $this->productValidPeriodRepository->remove($product_id);
        }

        //删除商品基础数据
        $flag_row[] = $this->repository->remove($product_id);

        if (is_ok($flag_row)) {
            DB::commit();
            return true;
        } else {
            DB::rollBack();
            throw new ErrorException(__('删除失败'));
        }

    }


    /**
     * 批量更新商品状态
     *
     * @param array $product_ids
     * @param int $product_state_id
     * @return bool
     * @throws ErrorException
     */
    public function batchEditState(array $product_ids, int $product_state_id)
    {
        $product_index_rows = $this->productIndexRepository->gets($product_ids);
        if (empty($product_index_rows)) {
            throw new ErrorException(__("更新商品不存在！"));
        }
        $all_product_items = $this->productItemRepository->find([['product_id', 'IN', $product_ids]]);
        $product_items_map = arrayMap($all_product_items, 'product_id');

        $product_sale_time = 0;
        if ($product_state_id == StateCode::PRODUCT_STATE_NORMAL) {
            $product_sale_time = getTime();
        }
        $update_product_ids = [];

        foreach ($product_ids as $product_id) {

            if (isset($product_index_rows[$product_id])) {
                $product_index = $product_index_rows[$product_id];

                if ($product_state_id == StateCode::PRODUCT_STATE_NORMAL) {
                    //上架商品操作

                    $product_verify_id = $product_index['product_verify_id']; // 校验审核状态
                    if (in_array($product_verify_id, [StateCode::PRODUCT_VERIFY_WAITING, StateCode::PRODUCT_VERIFY_REFUSED])) {
                        continue;
                        //throw new ErrorException("商品编号: {$product_index['product_id']} 尚未审核通过，无法上架！");
                    }

                    // 检查 SKU 是否可以上架
                    if (isset($product_items_map[$product_id])) {
                        $product_items = $product_items_map[$product_id];
                        $item_enable_rows = array_column($product_items, 'item_enable');
                        $enable_count_rows = array_count_values($item_enable_rows);
                        if ($enable_count_rows[StateCode::PRODUCT_STATE_NORMAL] <= 0) {
                            continue;
                            //throw new ErrorException("SPU编号: {$product_id}，由于SKU商品都处于下架仓库中，无法上架！");
                        }
                    }

                    $update_product_ids[] = $product_id;
                } elseif ($product_state_id === StateCode::PRODUCT_STATE_OFF_THE_SHELF) {
                    $update_product_ids[] = $product_id;
                } elseif ($product_state_id === StateCode::PRODUCT_STATE_ILLEGAL) {
                    $update_product_ids[] = $product_id;
                }
            }
        }

        if (!empty($update_product_ids)) {
            $affected_rows = $this->productIndexRepository->edits($update_product_ids, [
                'product_state_id' => $product_state_id,
                'product_sale_time' => $product_sale_time
            ]);

            return $affected_rows;
        } else {
            throw new ErrorException(__("无可更新商品！"));
        }
    }


}
