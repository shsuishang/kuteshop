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


namespace Modules\Account\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserDistributionRepository;
use Modules\Account\Repositories\Criteria\UserDistributionCriteria;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Pay\Repositories\Contracts\DistributionCommissionRepository;


/**
 * Class UserDistributionService.
 *
 * @package Modules\Account\Services
 */
class UserDistributionService extends BaseService
{
    private $userInfoRepository;
    private $distributionCommissionRepository;

    public function __construct(
        UserDistributionRepository       $userDistributionRepository,
        UserInfoRepository               $userInfoRepository,
        DistributionCommissionRepository $distributionCommissionRepository)
    {
        $this->repository = $userDistributionRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->distributionCommissionRepository = $distributionCommissionRepository;
    }

    /**
     * 获取列表
     * @return array
     */
    public function getLists($request)
    {
        $size = $request->input('size');
        $data = $this->repository->list(new UserDistributionCriteria($request), $size);
        if (!empty($data)) {
            $user_ids = array_column_unique($data['data'], 'user_id');
            $info_rows = $this->userInfoRepository->gets($user_ids);
            foreach ($data['data'] as $key => $item) {
                if (isset($info_rows[$item['user_id']]) && $info_rows[$item['user_id']]) {
                    $data['data'][$key]['user_nickname'] = $info_rows[$item['user_id']]['user_nickname'];
                }
            }
        }

        return $data;
    }


    /**
     * 添加推广员
     */
    public function addDistribution($request)
    {
        //查询当前要添加的用户是否为推广员，如果不是，则添加
        $user_id = $request->input('user_id', 0);
        $user_distribution_row = $this->repository->getOne($user_id);
        if (!empty($user_distribution_row)) {
            throw new ErrorException(__('该推广员已存在'));
        }

        $user_info = $this->userInfoRepository->getOne($user_id);
        if (empty($user_info)) {
            throw new ErrorException(__('用户不存在'));
        }

        DB::beginTransaction();

        try {
            $this->repository->add([
                'user_id' => $user_id,   //用户ID
                'user_parent_id' => $request->input('user_parent_id', 0), //用户父级ID
                'user_partner_id' => $request->input('user_partner_id', 0),   //合伙人ID
                'user_team_count' => $request->input('user_team_count', 0),   //团队数量
                'user_province_team_id' => $request->input('user_province_team_id', 0),   //所属省公司
                'user_city_team_id' => $request->input('user_city_team_id', 0),   //所属市公司
                'user_county_team_id' => $request->input('user_county_team_id', 0),   //所属区公司
                'role_level_id' => $request->input('role_level_id', 1001),   //角色等级
                'ucc_id' => $request->input('ucc_id', 0),   //渠道编号
                'activity_id' => $request->input('activity_id', 0),   //活动编号
                'user_time' => getTime(),   //注册(推广员)时间
                'user_fans_num' => $request->input('user_fans_num', 0),   //粉丝数量 （冗余）
                'user_is_sp' => $request->boolean('user_is_sp', false),   //是否服务商
                'user_is_da' => $request->input('user_is_da', 0),   //区代理 0-否 其他-区ID
                'user_is_ca' => $request->input('user_is_ca', 0),   //市代理 0-否 其他-市ID
                'user_is_pa' => $request->input('user_is_pa', 0),   //省代理 0-否 其他-省ID
                'user_is_pt' => $request->boolean('user_is_pt', 0),   //城市合伙人 0-否 1-是
                'user_active' => $request->boolean('user_active', false),   //生效 未生效 0-否 1-是
            ]);

            //增加推广员佣金表数据
            $distribution_commission_row = $this->distributionCommissionRepository->getOne($user_id);
            if (empty($distribution_commission_row)) {
                $this->distributionCommissionRepository->add(['user_id' => $user_id]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('添加推广员失败: ') . $e->getMessage());
        }
    }

}
