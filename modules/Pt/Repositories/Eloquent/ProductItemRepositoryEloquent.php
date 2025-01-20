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


namespace Modules\Pt\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;
use Modules\Pt\Repositories\Models\ProductItem;

/**
 * Class ProductItemRepositoryEloquent.
 *
 * @package Modules\Pt\Repositories\Eloquent
 */
class ProductItemRepositoryEloquent extends BaseRepository implements ProductItemRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return ProductItem::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    //根据主键获取数据
    public function getOne($id)
    {
        $res = $this->model->find($id);
        if ($res === NULL) {
            $res = [];
        } else {
            $res = $res->toArray();
            $res['available_quantity'] = $res['item_quantity'] - $res['item_quantity_frozen'];
        }

        $this->resetModel();

        return $res;
    }


    /**
     * 根据主键查询 主键作为key下标 返回
     * @param $ids
     * @param $keyBy
     * @return array|mixed[]
     */
    public function gets($ids, $keyBy = true)
    {
        if (empty($ids)) {
            return [];
        }

        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $res = $this->findWhereIn('item_id', $ids);
        if ($keyBy) {
            $res = $res->keyBy('item_id');
        }

        $rows = $res ? $res->toArray() : [];
        if (!empty($rows)) {
            foreach ($rows as $k => $row) {
                $rows[$k]['available_quantity'] = $row['item_quantity'] - $row['item_quantity_frozen'];
            }
        }

        return $rows;
    }


    /**
     * 批量更新商品库存
     *
     * @param array $data
     * @return int
     */
    public function batchUpdateQuantity(array $data)
    {
        // 提取所有商品的ID
        $item_ids = array_column($data, 'item_id');
        $case_statements = '';
        $item_ids_str = implode(',', $item_ids);

        // 构建 SQL 的 CASE 语句
        foreach ($data as $row) {
            $case_statements .= "WHEN item_id = {$row['item_id']} THEN {$row['item_quantity']} ";
        }

        // 生成批量更新的 SQL 查询
        $query = "UPDATE pt_product_item SET item_quantity = CASE {$case_statements} END WHERE item_id IN ({$item_ids_str})";

        // 执行更新语句
        return DB::update($query);
    }


}
