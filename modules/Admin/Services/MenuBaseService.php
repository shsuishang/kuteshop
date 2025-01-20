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


namespace Modules\Admin\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Admin\Repositories\Contracts\MenuBaseRepository;
use Modules\Admin\Repositories\Contracts\UserAdminRepository;
use Modules\Admin\Repositories\Contracts\UserRoleRepository;

class MenuBaseService extends BaseService
{
    private $userAdminRepository;
    private $userRoleRepository;

    public function __construct(
        MenuBaseRepository  $menuBaseRepository,
        UserAdminRepository $userAdminRepository,
        UserRoleRepository  $userRoleRepository
    )
    {
        $this->repository = $menuBaseRepository;
        $this->userAdminRepository = $userAdminRepository;
        $this->userRoleRepository = $userRoleRepository;
    }

    public function treeMenus($condition = [])
    {
        if (isset($condition['menu_type'])) {
            $user_id = auth()->payload()->get('user_id');
            $admin_user = $this->userAdminRepository->getOne($user_id);
            if (!empty($admin_user)) {
                $menu_ids = [-1];
                //todo 获取用户的角色权限菜单
                $user_role_id = $admin_user['user_role_id'];
                $user_role_menu = $this->userRoleRepository->getOne($user_role_id);
                if (!empty($user_role_menu)) {
                    $menu_ids = explode(',', $user_role_menu['menu_ids']);
                }

                $condition[] = ['menu_id', 'IN', $menu_ids];
            }
        }

        $data = $this->repository->find($condition, ['menu_sort' => 'ASC']);
        $data = ArrayToTree($data, 0, 'children', 'menu_');

        return $data;
    }

}
