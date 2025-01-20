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


namespace Modules\Pt\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Models\User;
use Modules\Pt\Repositories\Contracts\ProductCommentReplyRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductCommentRepository;

/**
 * Class ProductCommentService.
 *
 * @package Modules\Pt\Services
 */
class ProductCommentReplyService extends BaseService
{
    private $productCommentRepository;

    public function __construct(ProductCommentReplyRepository $productCommentReplyRepository, ProductCommentRepository $productCommentRepository)
    {
        $this->repository = $productCommentReplyRepository;
        $this->productCommentRepository = $productCommentRepository;
    }


    public function addCommentReply($request)
    {
        $comment_id = $request->get('comment_id', -1);
        $comment_row = $this->productCommentRepository->getOne($comment_id);
        if (empty($comment_row)) {
            throw new ErrorException(__('评论不存在'));
        }

        $user_row = User::getUser();
        $reply_row = [
            'comment_id' => $comment_id,
            'user_id' => $user_row['user_id'],
            'user_name' => $user_row['user_nickname'],
            'user_id_to' => $comment_row['user_id'],
            'user_name_to' => $comment_row['user_name'],
            'comment_reply_content' => $request->input('comment_reply_content', ''),
            'comment_reply_time' => getDateTime(),
            'comment_reply_enable' => true,
            'comment_reply_isadmin' => true
        ];

        return $this->repository->add($reply_row);

    }

}
