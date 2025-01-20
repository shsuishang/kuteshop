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


namespace Modules\Trade\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Trade\Repositories\Contracts\DistributionOrderRepository;
use App\Exceptions\ErrorException;
use Illuminate\Http\Request;
use Modules\Account\Repositories\Contracts\UserInfoRepository;

/**
 * Class DistributionOrderService.
 *
 * @package Modules\Trade\Services
 */
class DistributionOrderService extends BaseService
{
    private $userInfoRepository;


    public function __construct(DistributionOrderRepository $distributionOrderRepository, UserInfoRepository $userInfoRepository)
    {
        $this->repository = $distributionOrderRepository;
        $this->userInfoRepository = $userInfoRepository;
    }


    /**
     * 获取列表
     * @return array
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list($criteria, $limit);

        //加入用户昵称
        $data['data'] = $this->userInfoRepository->fixUserInfo($data['data'], [
            'buyer_user_name' => 'user_nickname',
            'buyer_user_avatar' => 'user_avatar'
        ], 'buyer_user_id');

        return $data;
    }


    /**
     * 用户基础信息-用户来源关系记录，此记录不可以改变。列表数据
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function listsOrder(Request $request)
    {
        $time_flag = $request->input('time_flag');
        if ($uo_level = $request->input('uo_level')) {
            if ($uo_level == 1) {
                $request['uo_levels'] = array(
                    1,
                    2,
                    3,
                    11,
                    12,
                    13
                );

            } else if ($uo_level == 81) {
                $request['uo_levels'] = array(
                    14,
                    15,
                    16,
                    4,
                    5,
                    6
                );
            } else {
                $request['uo_level'] = $uo_level;
            }
        }

        if (1 == $time_flag) {
            $time_section = getToday();

            $request['uo_time_start'] = $time_section['start'];
            $request['uo_time_end'] = $time_section['end'];
            $time = $time_section['start'];
        } elseif (2 == $time_flag) {
            $time_section = getSubDaysRange(30);
            $request['uo_time_start'] = $time_section['start'];
            $request['uo_time_end'] = $time_section['end'];
            $time = $time_section['start'];
        } elseif (3 == $time_flag) {
            $time_section = getSubDaysRange(90);
            $request['uo_time_start'] = $time_section['start'];
            $request['uo_time_end'] = $time_section['end'];
            $time = $time_section['start'];
        } else {
            $time = null;
        }

        $request['uo_is_paid'] = 1;

        $lists = $this->list($request);

        $data = [];
        $uo_buy_commission_total = 0.00;

        if ($lists['data']) {
            $uo_buy_commission_row = $this->repository->calCommissionByTime($request['user_id'], $uo_level, $time);

            if ($uo_buy_commission_row) {
                $uo_buy_commission_row = (array)$uo_buy_commission_row[0];
                //计算总金额
                $uo_buy_commission_total = sprintf('%.2f', $uo_buy_commission_row['uo_buy_commission']);
            }
        }

        $data['items']['records'] = $lists['data'];
        $data['items']['current'] = $lists['current_page'];
        $data['items']['pages'] = $lists['last_page'];
        $data['items']['size'] = $lists['limit'];
        $data['items']['total'] = $lists['total'];

        $data['uo_buy_commission_total'] = $uo_buy_commission_total;

        return $data;
    }
}
