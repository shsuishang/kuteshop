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


namespace Modules\Sys\Providers;

use Prettus\Repository\Providers\LumenRepositoryServiceProvider;

class SysRepositoryServiceProvider extends LumenRepositoryServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function register()
    {

        $bindings = [
            \Modules\Sys\Repositories\Contracts\NumberSeqRepository::class =>
                \Modules\Sys\Repositories\Eloquent\NumberSeqRepositoryEloquent::class,

            //页面
            \Modules\Sys\Repositories\Contracts\PageBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\PageBaseRepositoryEloquent::class,
            \Modules\Sys\Repositories\Contracts\PageModuleRepository::class =>
                \Modules\Sys\Repositories\Eloquent\PageModuleRepositoryEloquent::class,
            \Modules\Sys\Repositories\Contracts\PageMobileEntranceRepository::class =>
                \Modules\Sys\Repositories\Eloquent\PageMobileEntranceRepositoryEloquent::class,

            //PC页面导航
            \Modules\Sys\Repositories\Contracts\PagePcNavRepository::class =>
                \Modules\Sys\Repositories\Eloquent\PagePcNavRepositoryEloquent::class,

            //分类导航
            \Modules\Sys\Repositories\Contracts\PageCategoryNavRepository::class =>
                \Modules\Sys\Repositories\Eloquent\PageCategoryNavRepositoryEloquent::class,

            //地区表
            \Modules\Sys\Repositories\Contracts\DistrictBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\DistrictBaseRepositoryEloquent::class,

            //配置表
            \Modules\Sys\Repositories\Contracts\ConfigBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\ConfigBaseRepositoryEloquent::class,

            //配置类型表
            \Modules\Sys\Repositories\Contracts\ConfigTypeRepository::class =>
                \Modules\Sys\Repositories\Eloquent\ConfigTypeRepositoryEloquent::class,

            //素材表
            \Modules\Sys\Repositories\Contracts\MaterialBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\MaterialBaseRepositoryEloquent::class,

            //素材分类表
            \Modules\Sys\Repositories\Contracts\MaterialGalleryRepository::class =>
                \Modules\Sys\Repositories\Eloquent\MaterialGalleryRepositoryEloquent::class,

            //快递公司
            \Modules\Sys\Repositories\Contracts\ExpressBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\ExpressBaseRepositoryEloquent::class,

            //反馈类型
            \Modules\Sys\Repositories\Contracts\FeedbackTypeRepository::class =>
                \Modules\Sys\Repositories\Eloquent\FeedbackTypeRepositoryEloquent::class,

            //反馈分类
            \Modules\Sys\Repositories\Contracts\FeedbackCategoryRepository::class =>
                \Modules\Sys\Repositories\Eloquent\FeedbackCategoryRepositoryEloquent::class,

            //反馈列表
            \Modules\Sys\Repositories\Contracts\FeedbackBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\FeedbackBaseRepositoryEloquent::class,

            //计划任务
            \Modules\Sys\Repositories\Contracts\CrontabBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\CrontabBaseRepositoryEloquent::class,

            //操作日志
            \Modules\Sys\Repositories\Contracts\LogActionRepository::class =>
                \Modules\Sys\Repositories\Eloquent\LogActionRepositoryEloquent::class,

            //错误日志
            \Modules\Sys\Repositories\Contracts\LogErrorRepository::class =>
                \Modules\Sys\Repositories\Eloquent\LogErrorRepositoryEloquent::class,

            //保障服务
            \Modules\Sys\Repositories\Contracts\ContractTypeRepository::class =>
                \Modules\Sys\Repositories\Eloquent\ContractTypeRepositoryEloquent::class,

            //消息模板
            \Modules\Sys\Repositories\Contracts\MessageTemplateRepository::class =>
                \Modules\Sys\Repositories\Eloquent\MessageTemplateRepositoryEloquent::class,

            //字典分类
            \Modules\Sys\Repositories\Contracts\DictBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\DictBaseRepositoryEloquent::class,

            //字典项
            \Modules\Sys\Repositories\Contracts\DictItemRepository::class =>
                \Modules\Sys\Repositories\Eloquent\DictItemRepositoryEloquent::class,

            //货币
            \Modules\Sys\Repositories\Contracts\CurrencyBaseRepository::class =>
                \Modules\Sys\Repositories\Eloquent\CurrencyBaseRepositoryEloquent::class,

            //语言
            \Modules\Sys\Repositories\Contracts\LangStandardRepository::class =>
                \Modules\Sys\Repositories\Eloquent\LangStandardRepositoryEloquent::class,
            \Modules\Sys\Repositories\Contracts\LangMetaRepository::class =>
                \Modules\Sys\Repositories\Eloquent\LangMetaRepositoryEloquent::class
        ];

        foreach ($bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

}
