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


use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

//格式化菜单
function ArrayToTree($tree, $root_id = 0, $subname = 'sub', $prefix = '')
{
    $return = array();
    foreach ($tree as $leaf) {

        $returnLeaf = $leaf;

        if ($prefix == 'menu_') {
            $returnLeaf['name'] = $leaf['menu_name'];
            $returnLeaf['path'] = $leaf['menu_path'];

            $returnLeaf['component'] = $leaf['menu_component'];
            $returnLeaf['redirect'] = $leaf['menu_redirect'];
            $returnLeaf['meta'] = [
                'title' => $leaf['menu_title'],
                'hidden' => $leaf['menu_hidden'],
                'noClosable' => !$leaf['menu_close'],
                'icon' => $leaf['menu_icon'],
                'dot' => $leaf['menu_dot'],
                'badge' => $leaf['menu_bubble']
            ];
        }

        if ($leaf[$prefix . 'parent_id'] == $root_id) {
            foreach ($tree as $subleaf) {
                if ($subleaf[$prefix . 'parent_id'] == $leaf[$prefix . 'id']) {
                    $returnLeaf[$subname] = ArrayToTree($tree, $leaf[$prefix . 'id'], $subname, $prefix);
                    break;
                }
            }

            $return[] = $returnLeaf;
        }
    }

    return $return;
}


//验证码验证
function checkVerifyCode($request)
{
    $verify_key = $request->get('verify_key');
    $verify_code = $request->get('verify_code');

    $code = Cache::get($verify_key);
    if (!$code || !$verify_code || strtolower($verify_code) != strtolower($code)) {
        throw new ErrorException('验证码有误');
    }
    Cache::forget($verify_key);

    return true;
}

/**
 * 取得执行结果
 *
 * @return  array   $rs_row             是否成功
 */
function is_ok(&$rs_row = array())
{
    return ok($rs_row);
}

/**
 * 取得执行结果
 *
 * @return  array   $rs_row             是否成功
 */
function ok(&$rs_row = array())
{
    $rs = true;

    if (in_array(false, $rs_row, true)) {
        $rs = false;
    }

    return $rs;
}

/**
 * @param $input
 * @param $column_key
 * @param $index_key
 * @return array
 */
function array_column_unique($input, $column_key, $index_key = null)
{
    return array_unique(array_filter(array_column($input, $column_key, $index_key)));
}

/**
 * 获取当前日期时间
 * @param $time
 * @return string
 */
function getDateTime($time = null)
{
    if ($time) {
        return Carbon::createFromTimestamp($time)->format('Y-m-d H:i:s');
    } else {
        return Carbon::now()->format('Y-m-d H:i:s');
    }
}

/**
 * 获取当天日期
 * @return string
 */
function getCurDate()
{
    return Carbon::today()->toDateString(); // 直接返回 'Y-m-d' 格式
}

/**
 * 获取时间戳 13位 (毫秒级)
 * @return float
 */
function getTime()
{
    return round(microtime(true) * 1000);
}

/**
 * 获取获取当天时间区间
 * @return array
 */
function getToday()
{
    $startOfDay = Carbon::now()->startOfDay();
    $start_time = $startOfDay->timestamp * 1000;
    $end_time = getTime();

    return [
        'start' => $start_time,
        'end' => $end_time
    ];
}

/**
 * 获取昨天的开始时间和结束时间（毫秒级）
 * @return float[]|int[]
 */
function getYesterday()
{
    // 获取昨天的开始时间（00:00:00）
    $yesterday_start = Carbon::yesterday()->startOfDay();

    // 获取昨天的结束时间（23:59:59）
    $yesterday_end = Carbon::yesterday()->endOfDay();

    return [
        'start' => $yesterday_start->timestamp * 1000,
        'end' => $yesterday_end->timestamp * 1000
    ];
}

/**
 * 获取当前月的开始时间和结束时间
 * @return array 返回包含 'start' 和 'end' 的时间戳，单位为毫秒
 */
function getMonth()
{
    // 获取当前日期
    $currentDate = Carbon::now();

    // 获取本月开始时间戳
    $start_month_timestamp = $currentDate->startOfMonth()->startOfDay()->timestamp;

    // 获取本月结束时间戳
    $end_month_timestamp = $currentDate->endOfMonth()->endOfDay()->timestamp;

    // 将时间戳转换为13位整数（以毫秒为单位）
    return [
        'start' => $start_month_timestamp * 1000,
        'end' => $end_month_timestamp * 1000
    ];
}

/**
 * 获取过去N天的开始时间和结束时间
 * @param int $days 需要回溯的天数，默认为30天
 * @return array 返回包含 'start' 和 'end' 的时间戳，单位为毫秒
 */
function getSubDaysRange($days = 30)
{
    // 获取当前日期
    $currentDate = Carbon::now();

    // 获取N天前的日期
    $startDate = $currentDate->copy()->subDays($days)->startOfDay();
    $endDate = $currentDate->copy()->endOfDay();

    // 获取时间戳
    $start_timestamp = $startDate->timestamp;
    $end_timestamp = $endDate->timestamp;

    // 将时间戳转换为13位整数（以毫秒为单位）
    return [
        'start' => $start_timestamp * 1000,
        'end' => $end_timestamp * 1000
    ];
}

/**
 * 判断获取登录用户ID
 * @return int|string
 * @throws ErrorException
 */
function checkLoginUserId()
{
    $user_id = auth()->id();
    if (!$user_id) {
        throw new ErrorException('请先登录');
    }

    return $user_id;
}


/**
 * 用户权限检验
 * @param $user_id
 * @param $row
 * @param $key
 * @return bool
 * @throws ErrorException
 */
function checkDataRights($user_id, $row = [], $key = 'user_id')
{
    if (!isset($row[$key]) || $user_id != $row[$key]) {
        throw new ErrorException('无权限操作');
    }

    return true;
}


/**
 * 重构数组
 * @param $list
 * @param $key
 * @return array
 */
function arrayMap($list = [], $key = 'order_id')
{
    $map_data = [];
    foreach ($list as $item) {
        if (!array_key_exists($item[$key], $map_data)) {
            $map_data[$item[$key]] = [];
        }
        $map_data[$item[$key]][] = $item;
    }

    return $map_data;
}


/**
 * 验证码
 * @param $length
 * @return string
 */
function getVerifyCode($length)
{
    $numbers = '0123456789';
    return substr(str_shuffle(str_repeat($numbers, $length)), 0, $length);
}
