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

class PointsType
{
    public const POINTS_ADD = 1;
    public const POINTS_MINUS = 2;

    public const POINTS_TYPE_REG = 1;  // 会员注册
    public const POINTS_TYPE_LOGIN = 2;  // 会员登录
    public const POINTS_TYPE_EVALUATE_PRODUCT = 3; // 商品评论
    public const POINTS_TYPE_EVALUATE_STORE = 6; // 店铺评论
    public const POINTS_TYPE_CONSUME = 4; // 购买商品
    public const POINTS_TYPE_OTHER = 5; // 管理员操作
    public const POINTS_TYPE_EXCHANGE_PRODUCT = 7; // 积分换购商品
    public const POINTS_TYPE_EXCHANGE_VOUCHER = 8; // 积分兑换优惠券
    public const POINTS_TYPE_EXCHANGE_SP = 9; // 积分兑换
    public const POINTS_TYPE_TRANSFER_MINUS = 10; // 积分转出
    public const POINTS_TYPE_TRANSFER_ADD = 11; // 积分转入
    public const POINTS_TYPE_CONSUME_RETRUN = 12; // 积分退还
    public const POINTS_TYPE_UP_SP = 13; // 升级服务商
    public const POINTS_TYPE_UP_SELLER = 14; // 升级商家
    public const POINTS_TYPE_FX_FANS = 15; // 发展粉丝赠送积分
    public const POINTS_TYPE_DEDUCTION = 21; // 购买商品抵扣积分

    public const POINTS_TYPE_EXCHANGE_MONEY_ADD = 16; // 余额换积分
    public const POINTS_TYPE_EXCHANGE_MONEY_MINUS = 17; // 积分换余额
}
