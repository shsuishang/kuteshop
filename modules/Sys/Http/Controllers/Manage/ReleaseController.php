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


namespace Modules\Sys\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Services\LogErrorService;

class ReleaseController extends BaseController
{
    private $logErrorService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(LogErrorService $logErrorService)
    {
        $this->logErrorService = $logErrorService;
    }


    public function getLicence()
    {
        try {
            $licence = $this->configBaseService->getLk();
            $licencePublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA5Fa6zAE5sJ9y1qzjhGAFWggKbMAS82xNG8wKglG0k6XojKBTw/7evSwC0aEgYU/BkIPzxNb7j6Oap5iZ43YgFgLI1dGalZJnvmLTmRK4+MqkKI6LlQQyCTZtSDkPNr62jYzSya+pPt0hgBHgk2x6YAns83SYmZf+7NT3qB+uxIgVIJTcO6m+SX3MyQU6cRKlt46+A9GwYiPx6davGxiX4TeeS5/sWiV1+yBb1xovNPjGK9d6N/3ObvSPtNXLnFn5jtwT1UanZPdZMR+oYIlltR9QGE3jnaTxlYTUhkY63GMek94dWbJTBQqpaA6t221iCwh8uawX4sbm4ZoRTy8SRwIDAQAB";

            // 解密
            $decryptedLicence = RSAUtil::decryptByPublicKey($licence, $licencePublicKey);

            $res = [
                'licence_str' => $decryptedLicence,
                'is_authorized' => true,
            ];

            return response()->json($res, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
