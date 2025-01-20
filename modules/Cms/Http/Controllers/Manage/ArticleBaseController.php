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


namespace Modules\Cms\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Cms\Services\ArticleBaseService;

class ArticleBaseController extends BaseController
{
    private $articleBaseService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ArticleBaseService $articleBaseService)
    {
        $this->articleBaseService = $articleBaseService;
    }

    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->articleBaseService->getLists($request);

        return Respond::success($data);
    }

    /**
     * 格式化请求数组
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $data = [
            'article_title' => $request['article_title'],       //文章标题
            'article_name' => $request->input('article_name', ''),  //别名
            'article_excerpt' => $request->input('article_excerpt', ''),  //摘要
            'article_content' => $request['article_content'],       //文章内容
            'category_id' => $request->input('category_id', 0),       //所属分类
            'article_template' => $request->input('article_template', ''),       //模板
            'article_seo_title' => $request->input('article_seo_title', ''),       //SEO标题
            'article_seo_keywords' => $request->input('article_seo_keywords', ''),       //SEO关键字
            'article_seo_description' => $request->input('article_seo_description', ''),       //SEO描述

            'article_lang' => $request->input('article_lang', 'cn'),    //语言
            'article_type' => $request->input('article_type', 1),       //文章类型(ENUM):1-文章;2-公告
            'article_sort' => $request->input('article_sort', 0),       //排序

            'article_image' => $request->input('article_image', ''),       //文章图片
            'user_id' => User::getUserId(),       //发布用户ID
            'article_tags' => $request->input('article_tags', ''),        //文章标签(DOT):文章标签

            'article_reply_flag' => $request->boolean('article_reply_flag'),   //是否启用问答留言(BOOL):0-否;1-是
            'article_status' => $request->boolean('article_status'),       //状态(BOOL):0-关闭;1-启用
            'article_is_popular' => $request->boolean('article_is_popular'),   //是否热门
        ];

        return $data;
    }

    /**
     * 新增
     */
    public function add(Request $request)
    {
        $data = $this->articleBaseService->addArticleBase($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $article_id = $request->input('article_id', -1);
        $data = $this->articleBaseService->editArticleBase($article_id, $this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改 布尔值字段
     * @param Request $request
     * @return void
     */
    public function editState(Request $request)
    {
        $article_id = $request->input('article_id', -1);
        $state_data = [];
        if ($request->has('article_status')) {
            $state_data['article_status'] = $request->boolean('article_status');
        }
        if ($request->has('article_reply_flag')) {
            $state_data['article_reply_flag'] = $request->boolean('article_reply_flag');
        }
        if ($request->has('article_is_popular')) {
            $state_data['article_is_popular'] = $request->boolean('article_is_popular');
        }

        //todo 变更相关状态
        $data = $this->articleBaseService->edit($article_id, $state_data);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $article_id = $request->input('article_id', -1);
        $data = $this->articleBaseService->removeArticleBase($article_id);

        return Respond::success($data);
    }


    /**
     * 批量删除
     */
    public function removeBatch(Request $request)
    {
        $data = $this->articleBaseService->removeBatch($request);

        return Respond::success($data);
    }
}
