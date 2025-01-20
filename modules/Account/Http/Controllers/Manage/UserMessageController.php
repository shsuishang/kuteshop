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


namespace Modules\Account\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserMessageCriteria;
use Modules\Account\Repositories\Models\User;
use Modules\Account\Services\UserMessageService;

class UserMessageController extends BaseController
{

    private $userMessageService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserMessageService $userMessageService)
    {
        $this->userMessageService = $userMessageService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userMessageService->list($request, new UserMessageCriteria($request));

        return Respond::success($data);
    }


    public function editState(Request $request)
    {
        $message_id = $request->get('message_id', -1);
        $state_data = [];

        if ($request->has('message_is_read')) {
            $state_data['message_is_read'] = $request->boolean('message_is_read');
        }

        if ($request->has('message_is_delete')) {
            $state_data['message_is_delete'] = $request->boolean('message_is_delete');
        }

        // 更新状态
        if ($message_id && !empty($state_data)) {
            $result = $this->userMessageService->edit($message_id, $state_data);
        } else {
            throw new ErrorException(__('数据有误'));
        }

        return Respond::success($result);
    }


    public function getNotice(Request $request)
    {
        $user_id = User::getUserId();
        $data = $this->userMessageService->getNotice($user_id);

        return Respond::success($data);
    }

}
