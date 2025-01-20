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


namespace Modules\Trade\Services;

use App\Support\PointsType;
use App\Support\StateCode;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Pay\Services\UserResourceService;
use Modules\Pt\Repositories\Contracts\ProductCommentRepository;
use Modules\Pt\Repositories\Contracts\ProductIndexRepository;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use Modules\Trade\Repositories\Contracts\OrderBaseRepository;
use Modules\Trade\Repositories\Contracts\OrderCommentRepository;
use App\Exceptions\ErrorException;
use Modules\Trade\Repositories\Contracts\OrderInfoRepository;
use Modules\Trade\Repositories\Contracts\OrderItemRepository;

/**
 * Class OrderCommentService.
 *
 * @package Modules\Trade\Services
 */
class OrderCommentService extends BaseService
{
    private $configBaseRepository;
    private $orderBaseRepository;
    private $orderInfoRepository;
    private $orderItemRepository;
    private $productCommentRepository;
    private $productIndexRepository;

    private $userResourceService;

    public function __construct(
        OrderCommentRepository   $orderCommentRepository,
        ConfigBaseRepository     $configBaseRepository,
        OrderBaseRepository      $orderBaseRepository,
        OrderInfoRepository      $orderInfoRepository,
        OrderItemRepository      $orderItemRepository,
        ProductCommentRepository $productCommentRepository,
        ProductIndexRepository   $productIndexRepository,

        UserResourceService      $userResourceService
    )
    {
        $this->repository = $orderCommentRepository;
        $this->configBaseRepository = $configBaseRepository;
        $this->orderBaseRepository = $orderBaseRepository;
        $this->orderInfoRepository = $orderInfoRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->productCommentRepository = $productCommentRepository;
        $this->productIndexRepository = $productIndexRepository;

        $this->userResourceService = $userResourceService;
    }


    /**
     * 订单评价
     * @param $order_id
     * @param $user_id
     * @return array
     */
    public function storeEvaluationWithContent($order_id, $user_id)
    {
        $order_item_evaluation_status = [StateCode::ORDER_ITEM_EVALUATION_NO, StateCode::ORDER_ITEM_EVALUATION_YES];
        $comment_res = $this->getEvaluationItem($order_id, $user_id, $order_item_evaluation_status);

        $comment_rows = $this->productCommentRepository->find([
            'order_id' => $order_id,
            'user_id' => $user_id
        ]);

        //订单商品评论
        $order_items = $comment_res['items'];
        foreach ($order_items as $k => $order_item) {
            $item_id = $order_item['item_id'];
            foreach ($comment_rows as $comment_row) {
                if ($comment_row['item_id'] == $item_id) {
                    $order_items[$k]['comment_content'] = $comment_row['comment_content'];
                    $order_items[$k]['comment_scores'] = $comment_row['comment_scores'];
                    $order_items[$k]['comment_image'] = $comment_row['comment_image'] ? explode(',', $comment_row['comment_image']) : [];
                }
            }
        }
        $comment_res['items'] = $order_items;

        $comment_res['order_evaluation'] = [];
        $order_comment = $this->repository->getOne($order_id);
        if ($order_comment) {
            $order_comment['comment_image'] = $order_comment['comment_image'] ? explode(',', $order_comment['comment_image']) : [];
            $comment_res['order_evaluation'] = $order_comment;
        }

        return $comment_res;
    }


    /**
     * @param $order_id
     * @param $user_id
     * @param $order_item_evaluation_status
     * @return array
     */
    public function getEvaluationItem($order_id = null, $user_id = null, $order_item_evaluation_status)
    {
        $comment_res = [];
        $order_item_cond = [
            'user_id' => $user_id,
            ['order_item_evaluation_status', 'IN', $order_item_evaluation_status]
        ];

        if ($order_id) {
            $order_item_cond['order_id'] = $order_id;
        } else {
            $order_infos = $this->orderInfoRepository->find([
                ['user_id', '=', $user_id],
                ['order_state_id', 'IN', [StateCode::ORDER_STATE_FINISH, StateCode::ORDER_STATE_RECEIVED]]
            ]);

            if (!empty($order_infos)) {
                $order_ids = array_column($order_infos, 'order_id');
                $order_item_cond[] = ['order_id', 'IN', $order_ids];
            }
        }

        $order_items = $this->orderItemRepository->find($order_item_cond);

        $comment_res['items'] = array_values($order_items);
        $comment_res['no'] = 0;
        $comment_res['yes'] = 0;

        return $comment_res;
    }


    /**
     * 订单商品评价
     * @param $user_id
     * @param $req
     * @return true
     * @throws ErrorException
     */
    public function addOrderComment($user_id, $req)
    {
        $order_id = $req->input('order_id');
        $order_comment = $this->repository->getOne($order_id);
        if (!empty($order_comment)) {
            throw new ErrorException(__('该订单已评论！'));
        }

        $order_base = $this->orderBaseRepository->getOne($order_id);
        if (empty($order_base)) {
            throw new ErrorException(__('订单不存在！'));
        }

        $comment_items = json_decode($req->input('item', []), true);

        $product_item_comments = [];
        $comment_is_anonymous = $req->input('comment_is_anonymous', 0);
        $user_nickname = '';
        if ($comment_is_anonymous) {
            $user_nickname = '匿名用户' . time();
        }

        $order_item_ids = [];
        $product_ids = [];
        foreach ($comment_items as $comment_item) {
            $order_item_ids[] = $comment_item['order_item_id'];
            $product_ids[] = $comment_item['product_id'];

            $product_item_comment = [];
            $product_item_comment['order_id'] = $order_id;
            $product_item_comment['product_id'] = $comment_item['product_id'];
            $product_item_comment['item_id'] = $comment_item['item_id'];
            $product_item_comment['item_name'] = $comment_item['item_name'];
            $product_item_comment['user_id'] = $user_id;
            $product_item_comment['user_name'] = $user_nickname;
            $product_item_comment['comment_is_anonymous'] = $comment_is_anonymous;
            $product_item_comment['comment_enable'] = 1;
            $product_item_comment['store_id'] = $order_base['store_id'];
            $product_item_comment['store_name'] = $order_base['store_name'];
            $product_item_comment['chain_id'] = 0;
            $product_item_comment['comment_scores'] = $comment_item['comment_scores'];
            $product_item_comment['comment_content'] = $comment_item['comment_content'];
            $comment_image = $comment_item['comment_image'];
            $product_item_comment['comment_image'] = implode(',', $comment_image);
            $product_item_comments[] = $product_item_comment;

            // 提交时候，判断没有违禁词才可以通行
            /*$commentContent = $commentItemReq->commentContent;
            if ($this->filterKeywordService->hasKeyword($commentContent)) {
                throw new BusinessException(__('评论中包含非法词汇！'));
            }*/
        }

        DB::beginTransaction();

        //todo 1、添加订单商品评价
        if (!empty($product_item_comments)) {
            $product_comment_res = $this->productCommentRepository->addBatch($product_item_comments);
            if (!$product_comment_res) {
                DB::rollBack();
                throw new ErrorException(__('添加商品评论失败'));
            }
        }

        //todo 2、修改订单商品评价状态
        if (!empty($order_item_ids)) {
            $order_item_res = $this->orderItemRepository->edits($order_item_ids, ['order_item_evaluation_status' => StateCode::ORDER_ITEM_EVALUATION_YES]);
            if (!$order_item_res) {
                DB::rollBack();
                throw new ErrorException(__('修改商品评论状态失败'));
            }
        }

        //todo 3、更新商品评论次数
        if (!empty($product_ids)) {
            $product_index_res = $this->productIndexRepository->incrementFieldByIds($product_ids, 'product_evaluation_num');
            if (!$product_index_res) {
                DB::rollBack();
                throw new ErrorException(__('商品评论次数更新失败'));
            }
        }

        //todo 4、添加订单评价
        $comment_points = $this->configBaseRepository->getConfig("points_evaluate_good", 0);
        $comment_image = json_encode($req->input('comment_image', []));
        $order_comment_row = [
            'order_id' => $order_id,
            'store_id' => $order_base['store_id'],
            'user_id' => $user_id,
            'user_name' => $user_nickname,
            'comment_points' => $comment_points,
            'comment_scores' => $req->input('comment_scores', 0),
            'comment_content' => $req->input('comment_content', ''),
            'comment_image' => $comment_image,
            'comment_is_anonymous' => $comment_is_anonymous,
            'comment_enable' => 1,
            'comment_store_desc_credit' => $req->input('store_desccredit'),
            'comment_store_service_credit' => $req->input('store_servicecredit'),
            'comment_store_delivery_credit' => $req->input('store_deliverycredit')
        ];
        if (!$this->repository->add($order_comment_row)) {
            DB::rollBack();
            throw new ErrorException(__('订单评价信息保存失败！'));
        }

        //todo 5、更新订单评价状态
        $order_info_res = $this->orderInfoRepository->edit($order_id, ['order_buyer_evaluation_status' => StateCode::ORDER_EVALUATION_YES]);
        if (!$order_info_res) {
            DB::rollBack();
            throw new ErrorException(__('订单评价状态修改失败！'));
        }

        //todo 6、更新用户资源
        if ($comment_points) {
            $points_flag = $this->userResourceService->points([
                'user_id' => $user_id,
                'points' => $comment_points,
                'points_type_id' => PointsType::POINTS_TYPE_EVALUATE_STORE,
                'points_log_desc' => __('订单评价') . $order_id
            ]);
            if (!$points_flag) {
                DB::rollBack();
                throw new ErrorException(__('用户积分更新失败！'));
            }
        }
        $exp_evaluate_good = $this->configBaseRepository->getConfig('exp_evaluate_good', 0);
        if ($exp_evaluate_good) {
            $exp_flag = $this->userResourceService->experience([
                'user_id' => $user_id,
                'exp' => $exp_evaluate_good,
                'exp_type_id' => PointsType::POINTS_TYPE_EVALUATE_STORE,
                'desc' => __('订单评价') . $order_id
            ]);
            if (!$exp_flag) {
                DB::rollBack();
                throw new ErrorException(__('用户经验值更新失败！'));
            }
        }

        DB::commit();

        return true;
    }


}
