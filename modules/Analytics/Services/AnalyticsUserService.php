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

use Modules\Analytics\Repositories\Models\AnalyticsUser;

/**
 * Class AnalyticsUserService.
 *
 * @package Modules\Analytics\Services
 */
class AnalyticsUserService
{
    private $analyticsUser;

    public function __construct(AnalyticsUser $analyticsUser)
    {
        $this->analyticsUser = $analyticsUser;
    }


    /**
     * getRegUser
     * @return array
     */
    public function getRegUser()
    {
        $today = getToday();
        $data['today'] = $this->analyticsUser->getRegUserNum($today['start'], $today['end']);

        $yesterday = getYesterday();
        $data['yestoday'] = $this->analyticsUser->getRegUserNum($yesterday['start'], $yesterday['end']);

        // 计算日环比 日环比 = (当日数据 - 前一日数据) / 前一日数据
        $daym2m = 0;
        if ($data['yestoday']) {
            $daym2m = ($data['today'] - $data['yestoday']) / $data['yestoday'];
        }
        $data['daym2m'] = $daym2m;

        $month = getMonth();
        $data['month'] = $this->analyticsUser->getRegUserNum($month['start'], $month['end']);

        return $data;
    }


    public function getUserTimeLine($stime, $etime)
    {
        $data = $this->analyticsUser->getUserTimeLine($stime, $etime);

        return $data;
    }


    public function getUserNum($request)
    {
        $data = [];
        $stime = $request->input('stime', '');
        $etime = $request->input('etime', '');

        // 获取当前周期内数据
        $current = $this->analyticsUser->getRegUserNum($stime, $etime);
        $data['current'] = $current;
        $data['pre'] = 0;
        $data['daym2m'] = 0;

        // 获取上个周期的数据
        if ($stime && $etime) {
            // 计算上个周期的时间范围
            $pre_stime = $stime - ($etime - $stime);
            $pre_etime = $stime;
            $pre_reg_num = $this->analyticsUser->getRegUserNum($pre_stime, $pre_etime);
            if ($pre_reg_num) {
                $data['pre'] = $pre_reg_num;
                $daym2m = (($current - $pre_reg_num) / $pre_reg_num);
                $data['daym2m'] = $daym2m;
            }
        }

        return $data;
    }

}
