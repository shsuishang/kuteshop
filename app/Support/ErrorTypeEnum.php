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


namespace App\Support;


/**
 * 状态常量
 */
class ErrorTypeEnum
{
    const ERR_NOT_DEFINITION = array("label" => "未分类异常", "value" => 0);
    const ERR_WX_JSPI = array("label" => "微信JSPI异常", "value" => 2001);
    const ERR_WX_XCX = array("label" => "微信小程序异常", "value" => 2002);
    const ERR_WX_MP = array("label" => "微信公众号异常", "value" => 2003);
    const ERR_ALI_PAY = array("label" => "支付宝支付异常", "value" => 3001);
    const ERR_PSUH_MSG = array("label" => "消息推送异常", "value" => 4001);
    const ERR_ALI_SERVICE = array("label" => "阿里云服务异常", "value" => 5001);
    const ERR_TENCENT_SERVICE = array("label" => "腾讯云服务异常", "value" => 5002);
    const ERR_HUAWEI_SERVICE = array("label" => "华为云服务异常", "value" => 5003);
    const ERR_ORDER_SERVICE = array("label" => "订单处理异常", "value" => 6001);

    public function getAllValues()
    {
        $errorTypes = [];
        $refClass = new \ReflectionClass($this);
        $constants = $refClass->getConstants();

        foreach ($constants as $constant) {
            $errorTypes[] = $constant;
        }

        return $errorTypes;
    }
}
