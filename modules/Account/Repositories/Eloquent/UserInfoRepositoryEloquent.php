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


namespace Modules\Account\Repositories\Eloquent;

use Kuteshop\Core\Repository\BaseRepository;
use Kuteshop\Core\Repository\Criteria\RequestCriteria;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Models\UserInfo;

/**
 * Class UserInfoRepositoryEloquent.
 *
 * @package Modules\Account\Repositories\Eloquent
 */
class UserInfoRepositoryEloquent extends BaseRepository implements UserInfoRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return UserInfo::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }


    /**
     * 绑定用户数据
     *
     * @param array $data 数据列表
     * @param array $map 字段映射 [目标字段 => 用户数据字段]
     * @param string $key_name 用户标识字段名称（默认为 "user_id"）
     * @return array 处理后的数据
     */
    public function fixUserInfo(array $data, array $map = ["user_nickname" => "user_nickname"], string $key_name = "user_id"): array
    {
        if (empty($data)) {
            return $data;
        }

        // 获取用户信息
        $user_ids = array_unique(array_column($data, $key_name));
        $exist_user_ids = array_column_unique($data, 'user_id');
        if ($key_name != 'user_id' && !empty($exist_user_ids)) {
            $user_ids = array_merge($exist_user_ids, $user_ids);
        }
        $user_info_rows = $this->gets($user_ids);

        // 遍历数据并绑定用户信息
        foreach ($data as $key => $item) {
            if (!empty($item[$key_name]) && isset($user_info_rows[$item[$key_name]])) {
                foreach ($map as $target_field => $source_field) {
                    $data[$key][$target_field] = $user_info_rows[$item[$key_name]][$source_field] ?? null;
                }
            }
            if ($key_name != 'user_id' && isset($item['user_id']) && isset($user_info_rows[$item['user_id']])) {
                $data[$key]['user_nickname'] = $user_info_rows[$item['user_id']]['user_nickname'];
            }
        }

        return $data;
    }

}
