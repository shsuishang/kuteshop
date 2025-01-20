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
 * Class AnalyticsOrder.
 *
 * @package Modules\Analytics\Services\Models
 */
class AnalyticsSys extends Model
{

    /**
     * @param $stime
     * @param $etime
     * @return false|int
     */
    public function getVisitor($stime, $etime)
    {
        $whereSet = "";

        if (!empty($stime) && !empty($etime)) {
            $whereSet .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        $sql = sprintf(
            "SELECT
                COUNT(*) AS num
            FROM sys_access_history
            WHERE 1 %s",
            $whereSet
        );

        try {
            $result = DB::selectOne($sql);

            if ($result && isset($result->num)) {
                return $result->num;
            } else {
                return 0;
            }
        } catch (QueryException $e) {
            // 记录日志或处理错误
            return false;
        }
    }

    public function getAccessNum($stime, $etime)
    {
        $whereSet = "";

        if (!empty($stime) && !empty($etime)) {
            $whereSet .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        $sql = sprintf(
            "SELECT COUNT(*) AS num
             FROM sys_access_history
             WHERE 1 %s", $whereSet
        );

        try {
            $result = DB::selectOne($sql);
            return $result ? $result->num : 0;
        } catch (QueryException $e) {
            return false;
        }
    }


    /**
     * 获取访问数量
     * @param $stime
     * @param $etime
     * @return int
     */
    public function getVisitorNum($stime, $etime)
    {
        $where_sql = "";
        if ($stime && $etime) {
            $where_sql .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        $sql = sprintf(
            "SELECT COUNT(DISTINCT access_client_id) AS num
             FROM sys_access_history
             WHERE 1 %s", $where_sql
        );

        $result = DB::selectOne($sql);
        return $result ? $result->num : 0;
    }

    public function getAccessItemTimeLine($stime, $etime, $item_id)
    {
        $whereSet = "";

        if ($stime && $etime) {
            $whereSet .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        if ($item_id) {
            $whereSet .= sprintf(" AND item_id = %d", $item_id);
        }

        $sql = sprintf(
            "SELECT COUNT(*) AS num,
                    FROM_UNIXTIME(ROUND(access_time / 1000), '%%m-%%d') AS time
             FROM sys_access_history
             WHERE 1 %s
             GROUP BY time
             ORDER BY time", $whereSet
        );

        try {
            return DB::select($sql);
        } catch (QueryException $e) {
            return false;
        }
    }

    public function getAccessItemNum($stime, $etime, $item_id)
    {
        $where_sql = "";

        if ($stime && $etime) {
            $where_sql .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        if ($item_id) {
            $where_sql .= sprintf(" AND item_id = %d", $item_id);
        }

        $sql = sprintf(
            "SELECT COUNT(*) AS num
             FROM sys_access_history
             WHERE 1 %s", $where_sql
        );

        try {
            $result = DB::selectOne($sql);
            return $result ? $result->num : 0;
        } catch (QueryException $e) {
            return false;
        }
    }

    public function getAccessItemUserTimeLine($stime, $etime, $item_id)
    {
        $whereSet = "";

        if ($stime && $etime) {
            $whereSet .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        if ($item_id) {
            $whereSet .= sprintf(" AND item_id = %d", $item_id);
        }

        $sql = sprintf(
            "SELECT COUNT(DISTINCT access_client_id) AS num,
                    FROM_UNIXTIME(ROUND(access_time / 1000), '%%m-%%d') AS time
             FROM sys_access_history
             WHERE 1 %s
             GROUP BY time
             ORDER BY time", $whereSet
        );

        try {
            return DB::select($sql);
        } catch (QueryException $e) {
            return false;
        }
    }

    public function getAccessItemUserNum($stime, $etime, $item_id)
    {
        $where_sql = "";

        if ($stime && $etime) {
            $where_sql .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        if ($item_id) {
            $where_sql .= sprintf(" AND item_id = %d", $item_id);
        }

        $sql = sprintf(
            "SELECT COUNT(DISTINCT access_client_id) AS num
             FROM sys_access_history
             WHERE 1 %s", $where_sql
        );

        try {
            $result = DB::selectOne($sql);
            return $result ? $result->num : 0;
        } catch (QueryException $e) {
            return false;
        }
    }

    public function getAccessVisitorTimeLine($stime, $etime)
    {
        $where_sql = "";
        if ($stime && $etime) {
            $where_sql .= sprintf(" AND access_time BETWEEN %d AND %d", $stime, $etime);
        }

        $sql = sprintf(
            "SELECT COUNT(*) AS num,
                    FROM_UNIXTIME(ROUND(access_time / 1000), '%%m-%%d') AS time
             FROM sys_access_history
             WHERE 1 %s
             GROUP BY time
             ORDER BY time", $where_sql
        );

        try {
            $result = DB::select($sql);
            return $result;
        } catch (QueryException $e) {
            return false;
        }
    }

    public function listAccessItem($stime, $etime, $item_id)
    {
        $where_sql = "";

        if ($stime && $etime) {
            $where_sql .= sprintf(" AND ash.access_time BETWEEN %d AND %d", $stime, $etime);
        }

        if ($item_id) {
            $where_sql .= sprintf(" AND item_id = %d", $item_id);
        }

        $sql = sprintf(
            "SELECT ash.item_id, ppi.item_name, ppi.product_id, ppi.item_unit_price, ppb.product_name,
                    COUNT(*) AS num
             FROM sys_access_history ash
             INNER JOIN pt_product_item ppi ON ash.item_id = ppi.item_id
             INNER JOIN pt_product_base ppb ON ppi.product_id = ppb.product_id
             WHERE 1 %s
             GROUP BY ash.item_id,ppi.item_name,ppi.product_id,ppi.item_unit_price,ppb.product_name
             ORDER BY num DESC
             LIMIT 0, 100", $where_sql
        );

        try {
            return DB::select($sql);
        } catch (QueryException $e) {
            return false;
        }
    }

}
