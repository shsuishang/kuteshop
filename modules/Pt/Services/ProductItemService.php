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
use Kuteshop\Core\Service\BaseService;
use Modules\Invoicing\Repositories\Contracts\StockBillItemRepository;
use Modules\Pt\Repositories\Contracts\ProductBaseRepository;
use Modules\Pt\Repositories\Contracts\ProductIndexRepository;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;

use Illuminate\Support\Facades\DB;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Criteria\ProductItemCriteria;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;


/**
 * Class ProductItemService.
 *
 * @package Modules\Pt\Services
 */
class ProductItemService extends BaseService
{
    private $stockBillItemRepository;
    private $configBaseRepository;
    private $productBaseRepository;
    private $productIndexRepository;


    public function __construct(
        ProductItemRepository   $productItemRepository,
        StockBillItemRepository $stockBillItemRepository,
        ConfigBaseRepository    $configBaseRepository,
        ProductBaseRepository   $productBaseRepository,
        ProductIndexRepository  $productIndexRepository
    )
    {
        $this->repository = $productItemRepository;
        $this->stockBillItemRepository = $stockBillItemRepository;
        $this->configBaseRepository = $configBaseRepository;
        $this->productBaseRepository = $productBaseRepository;
        $this->productIndexRepository = $productIndexRepository;
    }


    /**
     * 获取列表
     * @return array
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;

        if ($product_name = $request->input('product_name', '')) {
            $product_ids = $this->productIndexRepository->findKey([['product_name', 'LIKE', '%' . $product_name . '%']]);
            $request['product_ids'] = $product_ids;
        }

        $data = $this->repository->list($criteria, $limit);

        return $data;
    }


    /**
     * 更改库存
     * @param $inputs
     * @return true
     * @throws ErrorException
     */
    public function batchEditStock($inputs)
    {
        $item_ids = array_column($inputs, 'item_id');

        // 将 inputs 按 item_id 转换为关联数组
        $edit_stock_input_map = [];
        foreach ($inputs as $input) {
            $edit_stock_input_map[$input['item_id']] = $input;
        }

        // 获取商品 SKU 信息
        $product_items = $this->repository->gets($item_ids);
        if (empty($product_items)) {
            throw new ErrorException(__('商品 SKU 信息不存在'));
        }

        $stock_bill_items = [];

        // 开始数据库事务
        DB::beginTransaction();
        try {
            foreach ($product_items as $item_id => $product_item) {
                $input = $edit_stock_input_map[$product_item['item_id']] ?? null;

                if ($input) {
                    $stock_bill_item = [];
                    $stock_bill_item['product_id'] = $product_item['product_id'];
                    $stock_bill_item['item_id'] = $product_item['item_id'];
                    $stock_bill_item['item_name'] = $product_item['item_name'];
                    $stock_bill_item['bill_item_quantity'] = $input['item_quantity'];
                    $stock_bill_item['warehouse_item_quantity'] = $product_item['item_quantity'];

                    // 判断出入库类型
                    if ($input['bill_type_id'] == StateCode::BILL_TYPE_IN) {
                        $stock_bill_item['bill_type_id'] = StateCode::BILL_TYPE_IN;
                        $stock_bill_item['stock_transport_type_id'] = StateCode::STOCK_IN_OTHER;

                        // 增加库存
                        $product_items[$item_id]['item_quantity'] += $input['item_quantity'];
                    } else {
                        $stock_bill_item['bill_type_id'] = StateCode::BILL_TYPE_OUT;
                        $stock_bill_item['stock_transport_type_id'] = StateCode::STOCK_OUT_OTHER;

                        // 检查是否有足够的库存
                        if ($product_item['available_quantity'] >= $input['item_quantity']) {
                            // 减少库存
                            $product_items[$item_id]['item_quantity'] -= $input['item_quantity'];
                        } else {
                            throw new ErrorException(__('出库数量不能大于总库存！'));
                        }
                    }

                    $stock_bill_item['bill_item_unit_price'] = $product_item['item_unit_price'];
                    $stock_bill_item['bill_item_subtotal'] = $product_item['item_unit_price'] * $stock_bill_item['bill_item_quantity'];

                    $stock_bill_items[] = $stock_bill_item;
                }
            }

            if (!empty($stock_bill_items)) {
                // 保存库存单据
                if (!$this->stockBillItemRepository->addBatch($stock_bill_items)) {
                    throw new ErrorException(__('保存出入库单据失败！'));
                }

                if (!$this->repository->batchUpdateQuantity($product_items)) {
                    throw new ErrorException(__('修改商品 SKU 信息失败！'));
                }

                // 提交事务
                DB::commit();
            }

            return true;
        } catch (\Exception $e) {
            // 回滚事务
            DB::rollBack();
            throw new ErrorException($e->getMessage());
        }
    }


    public function getStockWarningItems($request)
    {
        // 从配置服务中获取库存警告阈值，默认为 5
        $stockWarning = $this->configBaseRepository->getConfig('stock_warning', 5);
        $request['stock_warning'] = $stockWarning;

        $data = $this->list($request, new ProductItemCriteria($request));
        if (!empty($data['data'])) {
            $product_ids = array_column_unique($data['data'], 'product_id');
            $product_base_rows = $this->productBaseRepository->gets($product_ids);

            foreach ($data['data'] as $k => $row) {
                $product_id = $row['product_id'];
                if (isset($product_base_rows[$product_id])) {
                    $data['data'][$k]['product_name'] = $product_base_rows[$product_id]['product_name'];
                    $data['data'][$k]['product_image'] = $product_base_rows[$product_id]['product_image'];
                }
            }
        }

        return $data;
    }


}
