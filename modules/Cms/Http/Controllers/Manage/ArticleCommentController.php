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
use Modules\Cms\Repositories\Criteria\ArticleCommentCriteria;
use Modules\Cms\Services\ArticleCommentService;

class ArticleCommentController extends BaseController
{
    private $articleCommentService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ArticleCommentService $articleCommentService)
    {
        $this->articleCommentService = $articleCommentService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->articleCommentService->list($request, new ArticleCommentCriteria($request));

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
            'article_id' => $request->input('article_id', 0),  //文章编号
            'comment_content' => $request->input('comment_content', ''),  //评论内容
            'comment_is_show' => $request->boolean('comment_is_show', false), //是显示(BOOL):0-否;1-是
            'user_id' => User::getUserId()       //发布用户ID
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $formatted_request = $this->formatRequest($request);
        $data = $this->articleCommentService->add($formatted_request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $comment_id = $request['comment_id'];
        $formatted_request = $this->formatRequest($request);

        $data = $this->articleCommentService->edit($comment_id, $formatted_request);

        return Respond::success($data);

    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $comment_id = $request->get('comment_id');
        $data = $this->articleCommentService->remove($comment_id);

        return Respond::success($data);
    }


    /**
     * 批量删除
     */
    public function removeBatch(Request $request)
    {
        $comment_id_str = $request->get('comment_id');
        $comment_ids = explode(',', $comment_id_str);
        $data = $this->articleCommentService->remove($comment_ids);

        return Respond::success($data);
    }


    /**
     * 修改状态值
     */
    public function editState(Request $request)
    {
        $comment_id = $request->input('comment_id', 0);
        $state_data = [
            'comment_is_show' => $request->boolean('comment_is_show')
        ];

        $data = $this->articleCommentService->edit($comment_id, $state_data);

        return Respond::success($data);
    }

}
