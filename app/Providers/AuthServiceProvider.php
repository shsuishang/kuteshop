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


namespace App\Providers;

use Modules\Account\Repositories\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Admin\Repositories\Models\MenuBase;
use Modules\Admin\Repositories\Models\UserRole;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        // 定义一个 Gate 来判断用户是否有访问某个菜单的权限
        Gate::define('access-menu', function (User $user, $menuPath) {

            if ($user->isSuperAdmin()) {
                //return true;
            }

            // 只有管理员才进行菜单权限校验
            if (!$user->isAdmin()) {
                return false;
            }

            $user_role_id = $user->userRoleId();
            $role = UserRole::find($user_role_id);
            if (!$role) {
                return false; // 如果找不到角色信息，返回拒绝访问
            }

            // 获取所有菜单权限ID
            $permissions = $role->getMenuPermissions();

            // 查找菜单表中与路径对应的菜单ID
            $menu = MenuBase::where('menu_permission', '/' . $menuPath)->first();
            if (!$menu) {
                return false; // 如果找不到菜单，返回拒绝访问
            }

            // 检查用户角色的权限中是否包含该菜单ID
            return in_array($menu->menu_id, $permissions);
        });

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->input('api_token')) {
                return User::where('api_token', $request->input('api_token'))->first();
            }
        });
    }
}
