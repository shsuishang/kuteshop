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


namespace Modules\Pt\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class PtRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        $bindings = [
            //商品分类
            \Modules\Pt\Repositories\Contracts\ProductCategoryRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductCategoryRepositoryEloquent::class,

            //商品类型
            \Modules\Pt\Repositories\Contracts\ProductTypeRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductTypeRepositoryEloquent::class,

            //商品属性
            \Modules\Pt\Repositories\Contracts\ProductAssistRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductAssistRepositoryEloquent::class,
            \Modules\Pt\Repositories\Contracts\ProductAssistItemRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductAssistItemRepositoryEloquent::class,

            //商品品牌
            \Modules\Pt\Repositories\Contracts\ProductBrandRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductBrandRepositoryEloquent::class,

            //商品规格
            \Modules\Pt\Repositories\Contracts\ProductSpecRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductSpecRepositoryEloquent::class,
            \Modules\Pt\Repositories\Contracts\ProductSpecItemRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductSpecItemRepositoryEloquent::class,

            //商品标签
            \Modules\Pt\Repositories\Contracts\ProductTagRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductTagRepositoryEloquent::class,

            //商品Info
            \Modules\Pt\Repositories\Contracts\ProductInfoRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductInfoRepositoryEloquent::class,

            //商品Base
            \Modules\Pt\Repositories\Contracts\ProductBaseRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductBaseRepositoryEloquent::class,

            //商品Index
            \Modules\Pt\Repositories\Contracts\ProductIndexRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductIndexRepositoryEloquent::class,

            //商品AssistIndex
            \Modules\Pt\Repositories\Contracts\ProductAssistIndexRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductAssistIndexRepositoryEloquent::class,

            //商品Image
            \Modules\Pt\Repositories\Contracts\ProductImageRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductImageRepositoryEloquent::class,

            //商品ValidPeriod
            \Modules\Pt\Repositories\Contracts\ProductValidPeriodRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductValidPeriodRepositoryEloquent::class,

            //商品Item
            \Modules\Pt\Repositories\Contracts\ProductItemRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductItemRepositoryEloquent::class,

            //商品评论
            \Modules\Pt\Repositories\Contracts\ProductCommentRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductCommentRepositoryEloquent::class,
            //评论回复
            \Modules\Pt\Repositories\Contracts\ProductCommentReplyRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductCommentReplyRepositoryEloquent::class,
            //评论点赞
            \Modules\Pt\Repositories\Contracts\ProductCommentHelpfulRepository::class =>
                \Modules\Pt\Repositories\Eloquent\ProductCommentHelpfulRepositoryEloquent::class

        ];

        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

}
