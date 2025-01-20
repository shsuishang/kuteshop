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

use App\Exceptions\ErrorException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class AnalyticsReturn.
 *
 * @package Modules\Analytics\Services\Models
 */
class AnalyticsReturn extends Model
{

    /**
     * 统计退单数量
     * @param $stime
     * @param $etime
     * @param $return_state_ids
     * @return int
     * @throws ErrorException
     */
    public function getReturnNum($stime, $etime, $return_state_ids = [])
    {
        // 初始化where条件
        $where_sql = "";
        $bindings = [];

        if (!empty($stime) && !empty($etime)) {
            $where_sql .= " AND return_add_time BETWEEN :stime AND :etime";
            $bindings['stime'] = $stime;
            $bindings['etime'] = $etime;
        }

        if (!empty($return_state_ids)) {
            $where_sql .= " AND return_state_id IN (" . implode(',', $return_state_ids) . ")";
        }

        // SQL查询语句
        $sql = "
            SELECT
                COUNT(*) AS num
            FROM
                trade_order_return
            WHERE
                1 {$where_sql}";

        try {
            // 执行查询
            $result = DB::select($sql, $bindings);

            // 返回查询结果
            if (!empty($result) && isset($result[0]->num)) {
                $out = $result[0]->num;
            } else {
                $out = 0;
            }

            return $out;

        } catch (\Exception $e) {
            // 返回错误信息
            throw new ErrorException($e->getMessage());
        }
    }


    /**
     * 时间段内退单金额
     * @param $stime
     * @param $etime
     * @param $return_state_ids
     * @return int
     * @throws ErrorException
     */
    public function getReturnAmount($stime, $etime, $return_state_ids = [])
    {
        // 初始化 where 条件
        $where_sql = "";
        $bindings = [];

        if (!empty($stime) && !empty($etime)) {
            $where_sql .= " AND return_add_time BETWEEN :stime AND :etime";
            $bindings['stime'] = $stime;
            $bindings['etime'] = $etime;
        }

        if (!empty($return_state_ids)) {
            $where_sql .= " AND return_state_id IN (" . implode(',', $return_state_ids) . ")";
        }

        // SQL 查询语句
        $sql = "
            SELECT
                SUM(trade_order_return.return_refund_amount) AS num
            FROM
                trade_order_return
            WHERE
                1 {$where_sql}";

        try {
            // 执行查询
            $result = DB::select($sql, $bindings);

            // 返回查询结果
            if (!empty($result) && isset($result[0]->num)) {
                $out = $result[0]->num;
            } else {
                $out = 0;
            }

            return $out;

        } catch (\Exception $e) {
            // 返回错误信息
            throw new ErrorException($e->getMessage());
        }
    }


    /**
     * 时间段退款金额
     * @param $stime
     * @param $etime
     * @param $return_state_ids
     * @return array
     * @throws ErrorException
     */
    public function getReturnAmountTimeline($stime, $etime, $return_state_ids = [])
    {
        // 初始化 where 条件
        $where_sql = "";
        $bindings = [];

        if ($stime && $etime) {
            $where_sql .= " AND trade_order_return.return_add_time BETWEEN :stime AND :etime";
            $bindings['stime'] = $stime;
            $bindings['etime'] = $etime;
        }

        if (!empty($return_state_ids)) {
            $where_sql .= " AND trade_order_return.return_state_id IN (" . implode(',', $return_state_ids) . ")";
        }

        // SQL 查询语句
        $sql = "
            SELECT
                FROM_UNIXTIME(trade_order_return.return_add_time / 1000, '%m-%d') AS time,
                SUM(trade_order_return.return_refund_amount) AS num
            FROM
                trade_order_return
            LEFT JOIN
                trade_order_base ON trade_order_return.order_id = trade_order_base.order_id
            WHERE
                1 {$where_sql}
            GROUP BY time
            ORDER BY time";

        try {
            // 执行查询
            $result = DB::select($sql, $bindings);

            // 返回查询结果
            return $result;

        } catch (\Exception $e) {
            // 返回错误信息
            throw new ErrorException($e->getMessage());
        }
    }


    /**
     * @param $stime
     * @param $etime
     * @param $return_state_ids
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReturnTimeLine($stime, $etime, $return_state_ids = [])
    {
        // 构建查询条件
        $query = DB::table('trade_order_return')
            ->select(DB::raw("FROM_UNIXTIME(return_add_time / 1000, '%m-%d') AS time"), DB::raw('COUNT(*) AS num'))
            ->whereRaw('1 = 1');

        if ($stime && $etime) {
            $query->whereBetween('return_add_time', [$stime, $etime]);
        }

        if (!empty($return_state_ids)) {
            $query->whereIn('return_state_id', $return_state_ids);
        }

        // 执行查询并获取结果
        $results = $query->groupBy('time')
            ->orderBy('time')
            ->get();

        return $results;
    }

}
