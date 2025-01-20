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


namespace Modules\Sys\Repositories\Eloquent;

use App\Support\StateCode;
use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Sys\Repositories\Contracts\ConfigRepository;
use Modules\Sys\Repositories\Models\Config;

/**
 * Class ConfigRepositoryEloquent.
 *
 * @package Modules\Sys\Repositories\Eloquent
 */
class ConfigRepositoryEloquent extends BaseRepository implements ConfigRepository
{

    // 定义全局变量 $state_id_row
    protected $state_id_row = [];

    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Config::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    public function getConfig($key, $default = null)
    {
        $config_row = $this->find($key);
        if ($config_row) {
            if ('json' == $config_row['config_datatype']) {
                $config_row['config_value'] = decode_json($config_row['config_value']);
            } else if ('dot' == $config_row['config_datatype']) {

                $config_row['config_value'] = explode(',', $config_row['config_value']);
            }

            $val = is_array($config_row['config_value']) ? $config_row['config_value'] : trim($config_row['config_value']);

            //self::$registry[$key] = $config_row['config_value'];
        } else {
            $val = $default;
        }

        return $val;
    }


    /**
     * 获取订单下一个状态
     * @param $order_state_id
     * @return array|int|mixed|string
     */
    public function getNextOrderStateId($order_state_id)
    {
        if (count($this->state_id_row) > 0) {
        } else {
            $this->initOrderProcess();
        }

        $index = array_search($order_state_id, $this->state_id_row);
        if ($index === false) {
            return [0, new Error("订单当前状态配置数据有误！")];
        } else {
            // 最后一个
            if (count($this->state_id_row) === $index + 1) {
                $next_order_state_id = StateCode::ORDER_STATE_FINISH;
            } else {
                $next_order_state_id = $this->state_id_row[$index + 1];
            }
        }

        return $next_order_state_id;
    }


    /**
     * 读取配置，获得初始化订单状态
     * @return string[]
     */
    public function initOrderProcess()
    {

        $this->state_id_row = [];

        $sc_order_process = $this->getConfig('sc_order_process');
        $state_id_list = explode(',', $sc_order_process);

        // 从小到大排序
        sort($state_id_list);

        $this->state_id_row = $state_id_list;

        return $this->state_id_row;
    }


}
