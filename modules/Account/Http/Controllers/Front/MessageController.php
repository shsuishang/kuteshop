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


namespace Modules\Account\Http\Controllers\Front;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserMessageCriteria;
use Modules\Account\Services\UserMessageService;
use App\Support\Respond;

class MessageController extends BaseController
{
    private $userMessageService;
    private $userId;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserMessageService $userMessageService)
    {
        $this->userMessageService = $userMessageService;
        $this->userId = checkLoginUserId();
    }


    /**
     * 用户消息列表
     */
    public function list(Request $request)
    {
        $request['user_id'] = $this->userId;
        $request['message_kind'] = 2;
        $data = $this->userMessageService->list($request, new UserMessageCriteria($request));

        return Respond::success($data);
    }


    public function getMessageNum(Request $request)
    {
        $user_id = $this->userId;
        $data = $this->userMessageService->getMessageNum($user_id);

        return Respond::success($data);
    }


    /**
     * 消息详情
     */
    public function get(Request $request)
    {
        $message_id = $request->get('message_id', 0);
        $data = $this->userMessageService->getOneMessage($message_id, $this->userId);

        return Respond::success($data);
    }


    public function add(Request $request)
    {
        $data = $this->userMessageService->addMessage($request);

        return Respond::success($data);
    }


    /**
     * 获取最近未读消息数量
     */
    public function getMsgCount(Request $request)
    {
        $recently_flag = $request->input('recently_flag', true);
        $user_id = $this->userId;
        $data = $this->userMessageService->getMsgCount($recently_flag, $user_id);

        return Respond::success($data);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImConfig(Request $request)
    {
        $user_other_id = $request->get('user_other_id', 0);
        $chat_item_id = $request->get('chat_item_id', 0);
        $chat_order_id = $request->get('chat_order_id', '');

        $data = $this->userMessageService->getImConfig($this->userId, $user_other_id, $chat_item_id, $chat_order_id);

        return Respond::success($data);
    }

    public function setRead(Request $request)
    {
        $data = $this->userMessageService->setRead($request);

        return Respond::success($data);
    }

}
