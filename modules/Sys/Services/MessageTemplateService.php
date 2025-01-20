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


namespace Modules\Sys\Services;

use Kuteshop\Core\Service\BaseService;
use Modules\Sys\Repositories\Contracts\MessageTemplateRepository;
use App\Exceptions\ErrorException;

/**
 * Class MessageTemplateService.
 *
 * @package Modules\Sys\Services
 */
class MessageTemplateService extends BaseService
{

    public function __construct(MessageTemplateRepository $messageTemplateRepository)
    {
        $this->repository = $messageTemplateRepository;
    }

    public function editState($request)
    {
        $message_id = $request->get('message_id');
        $state_data = [];

        if ($request->has('message_enable')) {
            $state_data['message_enable'] = $request->boolean('message_enable');
        }

        if ($request->has('message_sms_enable')) {
            $state_data['message_sms_enable'] = $request->boolean('message_sms_enable');
        }

        if ($request->has('message_email_enable')) {
            $state_data['message_email_enable'] = $request->boolean('message_email_enable');
        }

        if ($request->has('message_wechat_enable')) {
            $state_data['message_wechat_enable'] = $request->boolean('message_wechat_enable');
        }

        if ($request->has('message_xcx_enable')) {
            $state_data['message_xcx_enable'] = $request->boolean('message_xcx_enable');
        }

        if ($request->has('message_app_enable')) {
            $state_data['message_app_enable'] = $request->boolean('message_app_enable');
        }

        if ($request->has('message_sms_force')) {
            $state_data['message_sms_force'] = $request->boolean('message_sms_force');
        }

        if ($request->has('message_email_force')) {
            $state_data['message_email_force'] = $request->boolean('message_email_force');
        }

        if ($request->has('message_app_force')) {
            $state_data['message_app_force'] = $request->boolean('message_app_force');
        }

        if ($request->has('message_force')) {
            $state_data['message_force'] = $request->boolean('message_force');
        }

        // 更新状态
        if ($message_id && !empty($state_data)) {
            $result = $this->repository->edit($message_id, $state_data);
            if ($result) {
                return true;
            } else {
                throw new ErrorException(__('更新失败'));
            }
        }

        return true;
    }

}
