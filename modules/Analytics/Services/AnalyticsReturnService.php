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


namespace Modules\Analytics\Services;

use App\Support\StateCode;
use Modules\Analytics\Repositories\Models\AnalyticsReturn;

/**
 * Class AnalyticsReturnService.
 *
 * @package Modules\Analytics\Services
 */
class AnalyticsReturnService
{
    private $analyticsReturn;
    private $return_state_ids = [];

    public function __construct(AnalyticsReturn $analyticsReturn)
    {
        $this->analyticsReturn = $analyticsReturn;
        $this->return_state_ids = [
            StateCode::RETURN_PROCESS_FINISH,
            StateCode::RETURN_PROCESS_CHECK,
            StateCode::RETURN_PROCESS_RECEIVED,
            StateCode::RETURN_PROCESS_REFUND,
            StateCode::RETURN_PROCESS_RECEIPT_CONFIRMATION,
            StateCode::RETURN_PROCESS_REFUSED,
            StateCode::RETURN_PROCESS_SUBMIT
        ];
    }


    /**
     * @param $request
     * @return array
     * @throws \App\Exceptions\ErrorException
     */
    public function getReturnNum($request)
    {
        $data = [];
        $data['pre'] = 0;
        $data['daym2m'] = 0;

        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');

        // 获取当前周期内数据
        $current = $this->analyticsReturn->getReturnNum($stime, $etime, $this->return_state_ids);
        $data['current'] = $current;

        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $pre_etime = $stime;
            $pre_reg_num = $this->analyticsReturn->getReturnNum($pre_stime, $pre_etime, $this->return_state_ids);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }


    /**
     * @param $request
     * @return array
     * @throws \App\Exceptions\ErrorException
     */
    public function getReturnAmount($request)
    {
        $data = [];
        $data['pre'] = 0;
        $data['daym2m'] = 0;

        $stime = $request->input('stime');
        $etime = $request->input('etime');

        // 获取当前周期内数据
        $current = $this->analyticsReturn->getReturnAmount($stime, $etime, $this->return_state_ids);
        $data['current'] = $current;

        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $pre_etime = $stime;
            $pre_reg_num = $this->analyticsReturn->getReturnAmount($pre_stime, $pre_etime, $this->return_state_ids);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }


    public function getReturnAmountTimeline($request)
    {
        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $data = $this->analyticsReturn->getReturnAmountTimeline($stime, $etime, $this->return_state_ids);

        return $data;
    }


    public function getReturnNumTimeline($request)
    {
        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');
        $data = $this->analyticsReturn->getReturnTimeLine($stime, $etime, $this->return_state_ids);

        return $data;
    }


}
