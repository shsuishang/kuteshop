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


namespace Modules\Account\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class AccountRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        $bindings = [
            \Modules\Account\Repositories\Contracts\UserRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserInfoRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserInfoRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserLevelRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserLevelRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserTagGroupRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserTagGroupRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserTagBaseRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserTagBaseRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserMessageRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserMessageRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserDeliveryAddressRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserDeliveryAddressRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserInvoiceRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserInvoiceRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserBindConnectRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserBindConnectRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserFriendRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserFriendRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserGroupRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserGroupRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserGroupRelRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserGroupRelRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserZoneRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserZoneRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserZoneRelRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserZoneRelRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserDistributionRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserDistributionRepositoryEloquent::class,

            \Modules\Account\Repositories\Contracts\UserLoginRepository::class =>
                \Modules\Account\Repositories\Eloquent\UserLoginRepositoryEloquent::class,
        ];

        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }
}
