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


namespace Modules\Analytics\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class AnalyticsProduct.
 *
 * @package Modules\Analytics\Services\Models
 */
class AnalyticsProduct extends Model
{

    //获取产品时间线数据
    public function getProductTimeLine($stime, $etime)
    {
        $query = DB::table('pt_product_index')
            ->select(DB::raw('COUNT(*) as num'), DB::raw("FROM_UNIXTIME(ROUND(product_add_time / 1000), '%m-%d') AS time"))
            ->whereRaw('1 = 1');

        if ($stime && $etime) {
            $query->whereBetween('product_add_time', [$stime, $etime]);
        }

        $results = $query->groupBy('time')
            ->orderBy('time')
            ->get();

        return $results;
    }


    /*
     * 获取产品数量
     * @param $stime
     * @param $etime
     * @param $product_state_id
     * @param $category_id
     */
    public function getProductNum($stime, $etime, $product_state_id = 0, $category_id = 0)
    {
        $query = DB::table('pt_product_index')
            ->select(DB::raw('COUNT(*) as num'))
            ->whereRaw('1 = 1'); // 保证基本条件存在

        if ($stime && $etime) {
            $query->whereBetween('product_add_time', [$stime, $etime]);
        }

        if ($product_state_id) {
            $query->where('product_state_id', $product_state_id);
        }

        if ($category_id) {
            $query->where('category_id', $category_id);
        }

        $result = $query->first();
        $num = $result->num ?? 0;

        return $num;
    }

}
