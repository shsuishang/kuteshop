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


namespace Modules\Trade\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class TradeRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        $bindings = [
            //订单基础表
            \Modules\Trade\Repositories\Contracts\OrderBaseRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderBaseRepositoryEloquent::class,

            //订单信息表
            \Modules\Trade\Repositories\Contracts\OrderInfoRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderInfoRepositoryEloquent::class,

            //订单商品表
            \Modules\Trade\Repositories\Contracts\OrderItemRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderItemRepositoryEloquent::class,

            //订单数据表
            \Modules\Trade\Repositories\Contracts\OrderDataRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderDataRepositoryEloquent::class,

            //订单状态日志表
            \Modules\Trade\Repositories\Contracts\OrderStateLogRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderStateLogRepositoryEloquent::class,

            //订单收货地址表
            \Modules\Trade\Repositories\Contracts\OrderDeliveryAddressRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderDeliveryAddressRepositoryEloquent::class,

            //订单物流表
            \Modules\Trade\Repositories\Contracts\OrderLogisticsRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderLogisticsRepositoryEloquent::class,

            //退单表
            \Modules\Trade\Repositories\Contracts\OrderReturnRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderReturnRepositoryEloquent::class,

            //退单商品表
            \Modules\Trade\Repositories\Contracts\OrderReturnItemRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderReturnItemRepositoryEloquent::class,

            //退款原因
            \Modules\Trade\Repositories\Contracts\OrderReturnReasonRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderReturnReasonRepositoryEloquent::class,

            //订单发票
            \Modules\Trade\Repositories\Contracts\OrderInvoiceRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderInvoiceRepositoryEloquent::class,

            //购物车
            \Modules\Trade\Repositories\Contracts\UserCartRepository::class =>
                \Modules\Trade\Repositories\Eloquent\UserCartRepositoryEloquent::class,

            //订单评论
            \Modules\Trade\Repositories\Contracts\OrderCommentRepository::class =>
                \Modules\Trade\Repositories\Eloquent\OrderCommentRepositoryEloquent::class,

            //推广订单
            \Modules\Trade\Repositories\Contracts\DistributionOrderRepository::class =>
                \Modules\Trade\Repositories\Eloquent\DistributionOrderRepositoryEloquent::class,
        ];

        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

}
