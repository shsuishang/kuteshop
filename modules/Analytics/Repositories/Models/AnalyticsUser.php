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
class AnalyticsUser extends Model
{

    /**
     * 统计新增用户数量
     * @param $start_time
     * @param $end_time
     * @return int|mixed
     */
    public function getRegUserNum($start_time, $end_time)
    {
        $query = DB::table('account_user_login')
            ->select(DB::raw('COUNT(*) AS num'));
        if ($start_time) {
            $query->where('user_reg_time', '>=', $start_time);
        }
        if ($end_time) {
            $query->where('user_reg_time', '<=', $end_time);
        }

        $result = $query->first();

        return $result ? $result->num : 0;
    }


    /**
     * 获取用户时间线数据
     *
     * @param int $start_time 开始时间
     * @param int $end_time 结束时间
     * @return array 用户时间线数据
     */
    public function getUserTimeLine($start_time, $end_time): array
    {
        // 构建查询条件
        $where_sql = '';
        if ($start_time && $end_time) {
            $where_sql = sprintf(" AND user_reg_time BETWEEN %d AND %d", $start_time, $end_time);
        }

        // 构建 SQL 查询
        $sql = sprintf("
            SELECT FROM_UNIXTIME(ROUND(user_reg_time / 1000), '%%m-%%d') AS time,
                   COUNT(*) AS num
            FROM account_user_login
            WHERE 1 %s
            GROUP BY time
            ORDER BY time
        ", $where_sql);

        // 执行查询并获取结果
        $result = DB::select($sql);
        return $result;
    }

}
