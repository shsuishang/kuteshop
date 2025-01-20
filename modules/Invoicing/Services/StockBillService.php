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


namespace Modules\Invoicing\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Models\User;
use Modules\Invoicing\Repositories\Contracts\StockBillItemRepository;
use Modules\Invoicing\Repositories\Contracts\StockBillRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;
use Modules\Sys\Services\NumberSeqService;

/**
 * Class StockBillService.
 *
 * @package Modules\Invoicing\Services
 */
class StockBillService extends BaseService
{

    private $stockBillItemRepository;
    private $productItemRepository;
    private $numberSeqService;

    public function __construct(
        StockBillRepository     $stockBillRepository,
        StockBillItemRepository $stockBillItemRepository,
        ProductItemRepository   $productItemRepository,
        NumberSeqService        $numberSeqService
    )
    {
        $this->repository = $stockBillRepository;
        $this->stockBillItemRepository = $stockBillItemRepository;
        $this->productItemRepository = $productItemRepository;
        $this->numberSeqService = $numberSeqService;
    }


    /**
     * 获取出库单数据
     * @param $order_ids
     * @return array
     */
    public function findDetail($order_ids = [-1])
    {
        $stock_bills = $this->repository->find([['order_id', 'IN', $order_ids]]);
        if ($stock_bills) {
            $stock_bill_ids = array_column($stock_bills, 'stock_bill_id');
            $stock_bill_items = $this->stockBillItemRepository->find([['stock_bill_id', 'IN', $stock_bill_ids]]);

            if (!empty($stock_bill_items)) {
                //todo 订单商品数据 处理为map
                $bill_items = [];
                foreach ($stock_bill_items as $bill_item) {
                    if (!array_key_exists($bill_item['stock_bill_id'], $bill_items)) {
                        $bill_items[$bill_item['stock_bill_id']] = [];
                    }
                    $bill_items[$bill_item['stock_bill_id']][] = $bill_item;
                }

                foreach ($stock_bills as $k => $stock_bill) {
                    if (isset($bill_items[$stock_bill['stock_bill_id']])) {
                        $stock_bills[$k]['items'] = $bill_items[$stock_bill['stock_bill_id']];
                    }
                }
            }
        }

        return $stock_bills;
    }


    /**
     * todo 根据订单ID 获取该订单已经出库的商品
     * @param $order_id
     * @return mixed
     */
    public function getOutItems($order_id = null)
    {
        //todo 获取已出库商品
        if (!is_array($order_id)) {
            $order_id = [$order_id];
        }
        $data = $this->stockBillItemRepository->find([['order_id', 'IN', $order_id]]);

        return $data;
    }


    public function addStockBill($data = [])
    {
        $order_id = $data['order_id'];
        $stock_bill_id = $this->numberSeqService->createNextSeq('OUT');
        $data['stock_transport_type_id'] = $data['stock_transport_type_id'] ?? 2751;
        $data['bill_type_id'] = $data['bill_type_id'] ?? 2700;

        $user = User::getUser();
        $stock_bill = [
            'stock_bill_id' => $stock_bill_id,
            'stock_bill_checked' => 1,
            'stock_bill_date' => getCurDate(),
            'stock_bill_modify_time' => getDateTime(),
            'stock_bill_time' => getTime(),
            'bill_type_id' => $data['bill_type_id'],
            'stock_transport_type_id' => $data['stock_transport_type_id'],
            'store_id' => 0,
            'warehouse_id' => 0,
            'order_id' => $order_id,
            'stock_bill_remark' => "",
            'employee_id' => $user->user_id,
            'admin_id' => $user->user_id,
            'stock_bill_other_money' => 0,
            'stock_bill_amount' => 0, // 订单金额
            'stock_bill_enable' => 1, // 是否有效(BOOL):1-有效; 0-无效
            'stock_bill_src_id' => "", // 关联编号
        ];

        $stock_bill_amount = 0; //单据金额
        $bill_item_waiting = $data['bill_item_waiting'];
        $bill_item_quantity_all = $data['bill_item_quantity_all'];

        foreach ($bill_item_waiting as $order_item_id => $picking_item) {
            //单据商品小计
            $bill_item_subtotal = $picking_item['bill_item_price'] * $picking_item['bill_item_quantity'];

            $stock_bill_item = [
                'stock_bill_id' => $stock_bill_id,
                'order_id' => $order_id,
                'order_item_id' => $order_item_id,
                'item_id' => $picking_item['item_id'],
                'product_name' => $picking_item['product_name'],
                'item_name' => $picking_item['item_name'],
                'bill_item_quantity' => $picking_item['bill_item_quantity'],
                'bill_item_unit_price' => $picking_item['bill_item_price'],
                'bill_item_subtotal' => $bill_item_subtotal,
                'product_id' => $picking_item['product_id'],
                'warehouse_id' => $stock_bill['warehouse_id'],
                'stock_transport_type_id' => $stock_bill['stock_transport_type_id'],
                'bill_type_id' => $stock_bill['bill_type_id'],
            ];

            $stock_bill_item_result = $this->stockBillItemRepository->add($stock_bill_item);
            if (!$stock_bill_item_result) {
                throw new ErrorException('订单出库失败！');
            }

            //todo 更新商品库存
            if ($stock_bill_item['bill_item_quantity'] > 0) {
                $product_item_update = $this->productItemRepository->decrementFieldByIds([$stock_bill_item['item_id']], 'item_quantity_frozen', 1);
                if (!$product_item_update) {
                    throw new ErrorException('商品冻结库存更新失败！');
                }
                $product_item_update = $this->productItemRepository->decrementFieldByIds([$stock_bill_item['item_id']], 'item_quantity', 1);
                if (!$product_item_update) {
                    throw new ErrorException('商品库存更新失败！');
                }
            }

            $bill_item_quantity_all[$order_item_id]['bill_item_quantity'] -= $picking_item['bill_item_quantity'];

            $stock_bill_amount += $bill_item_subtotal;
        }

        $stock_bill['stock_bill_amount'] = $stock_bill_amount;

        $stock_bill_result = $this->repository->add($stock_bill);
        if (!$stock_bill_result) {
            throw new ErrorException('订单出库失败！');
        }

        return $bill_item_quantity_all;
    }


    /**
     * 出入库商品信息
     * @param $items
     * @return false
     */
    public function addBillItems($items)
    {
        if (empty($items)) {
            return false;
        }

        return $this->stockBillItemRepository->addBatch($items);
    }

}
