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
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Pt\Repositories\Contracts\ProductBaseRepository;
use Modules\Pt\Repositories\Contracts\ProductCommentReplyRepository;
use Modules\Pt\Repositories\Contracts\ProductCommentRepository;
use App\Exceptions\ErrorException;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;
use Modules\Pt\Repositories\Criteria\ProductCommentCriteria;

/**
 * Class ProductCommentService.
 *
 * @package Modules\Pt\Services
 */
class ProductCommentService extends BaseService
{

    private $userInfoRepository;
    private $productCommentReplyRepository;
    private $productBaseRepository;
    private $productItemRepository;


    public function __construct(
        ProductCommentRepository      $productCommentRepository,
        UserInfoRepository            $userInfoRepository,
        ProductCommentReplyRepository $productCommentReplyRepository,
        ProductBaseRepository         $productBaseRepository,
        ProductItemRepository         $productItemRepository
    )
    {
        $this->repository = $productCommentRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->productCommentReplyRepository = $productCommentReplyRepository;
        $this->productBaseRepository = $productBaseRepository;
        $this->productItemRepository = $productItemRepository;
    }


    /**
     * 获取列表
     * @return array
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list($criteria, $limit);

        if (!empty($data['data'])) {
            $items = $data['data'];
            $product_ids = array_column_unique($items, 'product_id');
            $comment_ids = array_column($items, 'comment_id');

            //评论回复
            $comment_reply_rows = $this->productCommentReplyRepository->find(['comment_reply_enable' => 1, ['comment_id', 'IN', $comment_ids]]);
            $comment_reply_map = arrayMap($comment_reply_rows, 'comment_id');

            //商品信息
            $product_base_rows = $this->productBaseRepository->gets($product_ids);

            foreach ($items as $k => $item) {
                if (isset($product_base_rows[$item['product_id']])) {
                    $items[$k]['product_name'] = $product_base_rows[$item['product_id']]['product_name'];
                }
                $items[$k]['comment_images'] = explode(',', $item['comment_image']);
                $items[$k]['comment_reply_num'] = isset($comment_reply_map[$item['comment_id']]) ? count($comment_reply_map[$item['comment_id']]) : 0;
            }
            $data['data'] = $items;
        }

        return $data;
    }


    /**
     * 删除商品评论
     * @param $comment_id
     * @return int|true
     * @throws ErrorException
     */
    public function removeComment($comment_id)
    {
        $comment_row = $this->repository->getOne($comment_id);
        if (empty($comment_row)) {
            throw new ErrorException(__('评论不存在'));
        }

        $flag = $this->remove($comment_id);
        if ($flag) {
            $reply_ids = $this->productCommentReplyRepository->findKey(['comment_id' => $comment_id]);
            if (!empty($reply_ids)) {
                $flag = $this->productCommentReplyRepository->remove($reply_ids);
            }
        }

        return $flag;
    }


    /**
     * 获取商品最新评论
     * @param $product_id
     * @return array[]
     */
    public function getProductComments($product_id = null)
    {
        $data = [
            'last_comments' => [],
            'last_comment' => []
        ];

        $product_comment_list = $this->repository->find([
            'product_id' => $product_id,
            'comment_enable' => 1
        ], ['comment_id' => 'DESC'], 1, 5);

        if (!empty($product_comment_list)) {
            $user_ids = array_unique(array_column($product_comment_list, 'user_id'));
            $user_infos = $this->userInfoRepository->gets($user_ids);

            foreach ($product_comment_list as $k => $comment) {
                $product_comment_list[$k]['comment_content'] = empty($comment['comment_content']) ? __('无评论') : $comment['comment_content'];
                if (isset($user_infos[$comment['user_id']])) {
                    $product_comment_list[$k]['user_avatar'] = $user_infos[$comment['user_id']]['user_avatar'];
                }

                $comment_image = $comment['comment_image'];
                if (!empty($comment_image)) {
                    $replaceImg = str_replace(['[', ']'], '', $comment_image);
                    $product_comment_list[$k]['comment_images'] = explode(',', $replaceImg);
                }
            }

            $data['last_comments'] = array_values($product_comment_list);
            $data['last_comment'] = reset($product_comment_list);
        }

        return $data;
    }


    public function getComment($request)
    {
        $limit = $request->get('size') ?? 10;
        $product_id = 0;
        if ($item_id = $request->input('item_id', 0)) {
            $product_item = $this->productItemRepository->getOne($item_id);
            if (!empty($product_item)) {
                unset($request['item_id']);
                $product_id = $product_item['product_id'];
                $request->merge(['product_id' => $product_id]);
            }
        }
        $request->merge(['comment_enable' => 1]);

        //好评，中评，差评数量
        $good = $this->repository->getNum(['product_id' => $product_id, 'comment_enable' => 1, ['comment_scores', '>', 3]]);
        $satisfied = $this->repository->getNum(['product_id' => $product_id, 'comment_enable' => 1, ['comment_scores', '=', 3]]);
        $bad = $this->repository->getNum(['product_id' => $product_id, 'comment_enable' => 1, ['comment_scores', '<', 3]]);

        //评价列表
        $data = $this->repository->list(new ProductCommentCriteria($request), $limit);
        if (!empty($data['data'])) {
            $items = $data['data'];

            //获取用户信息
            $user_ids = array_column_unique($items, 'user_id');
            $user_info_rows = $this->userInfoRepository->gets($user_ids);

            //获取评论回复
            $comment_ids = array_column($items, 'comment_id');
            $comment_reply_rows = $this->productCommentReplyRepository->find(['comment_reply_enable' => 1, ['comment_id', 'IN', $comment_ids]]);
            $comment_reply_map = arrayMap($comment_reply_rows, 'comment_id');

            foreach ($items as $k => $item) {
                $items[$k]['comment_images'] = explode(',', $item['comment_image']);
                $items[$k]['comment_reply_num'] = 0;
                $items[$k]['comment_reply_list'] = [];
                if (isset($comment_reply_map[$item['comment_id']])) {
                    $items[$k]['comment_reply_num'] = count($comment_reply_map[$item['comment_id']]);
                    $items[$k]['comment_reply_list'] = $comment_reply_map[$item['comment_id']];
                }

                if (isset($user_info_rows[$item['user_id']])) {
                    $items[$k]['user_avatar'] = $user_info_rows[$item['user_id']]['user_avatar'];
                }
            }

            $data['data'] = $items;
        }

        $data['good'] = $good;
        $data['satisfied'] = $satisfied;
        $data['bad'] = $bad;

        return $data;
    }


}
