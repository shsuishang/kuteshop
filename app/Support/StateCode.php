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

use Symfony\Component\HttpFoundation\Response;

/**
 * 状态常量
 */
class StateCode
{
    const DELIVERY_TYPE_EXPRESS = 1;
    const DELIVERY_TYPE_EMS = 2;
    const DELIVERY_TYPE_MAIL = 3;
    const DELIVERY_TYPE_AIR_FREIGHT = 4;
    const DELIVERY_TYPE_SELF_PICK_UP = 5;
    const DELIVERY_TYPE_EXP = 10;

    const DELIVERY_TIME_NO_LIMIT = 1;
    const DELIVERY_TIME_WORKING_DAY = 2;
    const DELIVERY_TIME_WEEKEND = 3;

    const USER_STATE_LOCKING = 0;
    const USER_STATE_NOTACTIVE = 1;
    const USER_STATE_ACTIVATION = 2;

    const PRODUCT_STATE_ILLEGAL = 1000;
    const PRODUCT_STATE_NORMAL = 1001;
    const PRODUCT_STATE_OFF_THE_SHELF = 1002;

    const DEMAND_STATE_CONDUCT = 1000;
    const DEMAND_STATE_REJECT = 1030;
    const DEMAND_STATE_EXAMINE = 1040;

    const PRODUCT_TAG_NEW = 1401;
    const PRODUCT_TAG_REC = 1402;
    const PRODUCT_TAG_BARGAIN = 1403;
    const PRODUCT_TAG_BARGAIN1 = 1404;
    const PRODUCT_TAG_CROSSBORDS = 1405;

    const PRODUCT_KIND_ENTITY = 1201;
    const PRODUCT_KIND_FUWU = 1202;
    const PRODUCT_KIND_CARD = 1203;
    const PRODUCT_KIND_WAIMAI = 1204;
    const PRODUCT_KIND_EDU = 1205;

    const PRODUCT_VERIFY_REFUSED = 3000;
    const PRODUCT_VERIFY_PASSED = 3001;
    const PRODUCT_VERIFY_WAITING = 3002;

    const ORDER_STATE_WAIT_PAY = 2010;
    const ORDER_STATE_WAIT_PAID = 2016;
    const ORDER_STATE_WAIT_REVIEW = 2011;
    const ORDER_STATE_WAIT_FINANCE_REVIEW = 2013;
    const ORDER_STATE_PICKING = 2020;
    const ORDER_STATE_WAIT_SHIPPING = 2030;
    const ORDER_STATE_SHIPPED = 2040;
    const ORDER_STATE_RECEIVED = 2050;
    const ORDER_STATE_FINISH = 2060;
    const ORDER_STATE_CANCEL = 2070;
    const ORDER_STATE_SELF_PICKUP = 2080;
    const ORDER_STATE_ERROR = 2090;
    const ORDER_STATE_RETURN = 2091;

    const ORDER_STATE_PICKUP = 2045;
    const ORDER_STATE_RIDER_RECEIVED = 2046;

    const ORDER_PAID_STATE_NO = 3010;
    const ORDER_PAID_STATE_FINANCE_REVIEW = 3011;
    const ORDER_PAID_STATE_PART = 3012;
    const ORDER_PAID_STATE_YES = 3013;

    const ORDER_PICKING_STATE_NO = 3020;
    const ORDER_PICKING_STATE_PART = 3021;
    const ORDER_PICKING_STATE_YES = 3022;

    const ORDER_CARDKIND_STATE_CARD = 1001;
    const ORDER_CARDKIND_STATE_VOUCHER = 1002;
    const ORDER_CARDKIND_STATE_COUPON = 1003;

    const ORDER_SHIPPED_STATE_NO = 3030;
    const ORDER_SHIPPED_STATE_PART = 3031;
    const ORDER_SHIPPED_STATE_YES = 3032;

    const VIRTUAL_ORDER_USED = 2101;
    const VIRTUAL_ORDER_UNUSE = 2100;
    const VIRTUAL_ORDER_TIMEOUT = 2103;

    const ORDER_CANCEL_BY_BUYER = 2201;
    const ORDER_CANCEL_BY_SELLER = 2202;
    const ORDER_CANCEL_BY_ADMIN = 2203;

    const SOURCE_TYPE_OTHER = 2310;
    const SOURCE_TYPE_PC = 2311;
    const SOURCE_TYPE_H5 = 2312;
    const SOURCE_TYPE_APP = 2313;
    const SOURCE_TYPE_MP = 2314;

    const SOURCE_FROM_OTHER = 2320;
    const SOURCE_FROM_WECHAT = 2321;
    const SOURCE_FROM_BAIDU = 2322;
    const SOURCE_FROM_ALIPAY = 2323;
    const SOURCE_FROM_TOUTIAO = 2324;

    const ORDER_FROM_PC = 2301;
    const ORDER_FROM_WAP = 2302;
    const ORDER_FROM_WEBPOS = 2303;

    const SETTLEMENT_STATE_WAIT_OPERATE = 2401;
    const SETTLEMENT_STATE_SELLER_COMFIRMED = 2402;
    const SETTLEMENT_STATE_PLATFORM_COMFIRMED = 2403;
    const SETTLEMENT_STATE_FINISH = 2404;

    const ORDER_RETURN_NO = 2500;
    const ORDER_RETURN_ING = 2501;
    const ORDER_RETURN_END = 2502;

    const ORDER_REFUND_STATE_NO = 2600;
    const ORDER_REFUND_STATE_ING = 2601;
    const ORDER_REFUND_STATE_END = 2602;

    const ORDER_TYPE_DD = 3061;
    const ORDER_TYPE_DC = 3063;
    const ORDER_TYPE_FX = 3062;
    const ORDER_TYPE_TH = 3066;
    const ORDER_TYPE_MD = 3068;
    const ORDER_TYPE_PT = 3069;

    const ORDER_TYPE_XQ = 4034;
    const ORDER_TYPE_FW = 4035;
    const ORDER_TYPE_XX = 5000;

    const ACTIVITY_STATE_WAITING = 0;
    const ACTIVITY_STATE_NORMAL = 1;
    const ACTIVITY_STATE_FINISHED = 2;
    const ACTIVITY_STATE_CLOSED = 3;

    const GET_VOUCHER_FREE = 1;
    const GET_VOUCHER_BY_POINT = 2;
    const GET_VOUCHER_BY_PURCHASE = 3;
    const GET_VOUCHER_BY_SHARE = 4;

    const ACTIVITY_GROUP_BOOKING_FAIL = 0;
    const ACTIVITY_GROUP_BOOKING_SUCCESS = 1;
    const ACTIVITY_GROUP_BOOKING_UNDERWAY = 2;

    const CART_GET_TYPE_BUY = 1;
    const CART_GET_TYPE_POINT = 2;
    const CART_GET_TYPE_GIFT = 3;
    const CART_GET_TYPE_BARGAIN = 4;

    const STOCK_IN_PURCHASE = 2701;     // 采购入库
    const STOCK_IN_RETURN = 2702;       // 退货入库
    const STOCK_IN_ALLOCATE = 2703;     // 调库入库
    const STOCK_IN_INVENTORY_P = 2704;  // 盘盈入库
    const STOCK_IN_INIT = 2705;         // 期初入库
    const STOCK_IN_OTHER = 2706;        // 手工入库
    const STOCK_OUT_SALE = 2751;        // 销售出库
    const STOCK_OUT_DAMAGED = 2752;     // 损坏出库
    const STOCK_OUT_ALLOCATE = 2753;    // 调库出库
    const STOCK_OUT_LOSSES = 2754;      // 盘亏出库
    const STOCK_OUT_OTHER = 2755;       // 手工出库
    const STOCK_OUT_PO_RETURN = 2756;   // 损坏出库

    const STOCK_OUT_ALL = 2700;   // 出库单
    const STOCK_IN_ALL = 2750;    // 入库单

    const BILL_TYPE_OUT = 2700;   //出库单
    const BILL_TYPE_IN = 2750;   //入库单

    const BILL_TYPE_SO = 2800;    // 销售订单
    const BILL_TYPE_PO = 2850;    // 采购订单

    const ORDER_PROCESS_SUBMIT = 3070; // 【客户】提交订单1OrderOrder
    const ORDER_PROCESS_PAY = 2010;            // 待支付Order
    const ORDER_PROCESS_CHECK = 2011;          // 订单审核1OrderOrder
    const ORDER_PROCESS_FINANCE_REVIEW = 2013; // 财务审核0OrderOrder
    const ORDER_PROCESS_OUT = 2020;            // 出库审核商品库存在“出库审核”节点完成后扣减，如需进行库存管理或核算销售成本毛利，需开启此节点。0OrderOrder
    const ORDER_PROCESS_SHIPPED = 2030;        // 发货确认如需跟踪订单物流信息，需开启此节点0OrderOrder
    const ORDER_PROCESS_RECEIVED = 2040;       // 【客户】收货确认0OrderOrder

    const ORDER_PROCESS_FINISH = 3098; // 完成1OrderOrder

    const RETURN_PROCESS_SUBMIT = 3100;               // 【客户】提交退单1ReturnReturn
    const RETURN_PROCESS_CHECK = 3105;                // 退单审核1ReturnReturn
    const RETURN_PROCESS_RECEIVED = 3110;             // 收货确认0ReturnReturn
    const RETURN_PROCESS_REFUND = 3115;               // 退款确认0ReturnReturn
    const RETURN_PROCESS_RECEIPT_CONFIRMATION = 3120; // 客户】收款确认0ReturnReturn
    const RETURN_PROCESS_FINISH = 3125;               // 完成1ReturnReturn3130-商家拒绝退货
    const RETURN_PROCESS_REFUSED = 3130;              // -商家拒绝退货
    const RETURN_PROCESS_CANCEL = 3135;               // -买家取消

    const PLANTFORM_RETURN_STATE_WAITING = 3180;  // 申请状态平台(ENUM):3180-处理中
    const PLANTFORM_RETURN_STATE_AGREE = 3181;    // 为待管理员处理卖家同意或者收货后
    const PLANTFORM_RETURN_PROCESS_FINISH = 3182; // -为已完成

    const STORE_STATE_WAIT_PROFILE = 3210; // 待完善资料
    const STORE_STATE_WAIT_VERIFY = 3220;  // 等待审核
    const STORE_STATE_NO = 3230;          // 审核资料没有通过
    const STORE_STATE_YES = 3240;         // 审核资料通过,待付款

    const TRADE_TYPE_SHOPPING = 1201;            // 购物
    const TRADE_TYPE_TRANSFER = 1202;            // 转账
    const TRADE_TYPE_DEPOSIT = 1203;             // 充值
    const TRADE_TYPE_WITHDRAW = 1204;            // 提现
    const TRADE_TYPE_SALES = 1205;               // 销售
    const TRADE_TYPE_COMMISSION = 1206;          // 佣金
    const TRADE_TYPE_REFUND_PAY = 1207;          // 退货付款
    const TRADE_TYPE_REFUND_GATHERING = 1208;    // 退货收款
    const TRADE_TYPE_TRANSFER_GATHERING = 1209;  // 转账收款
    const TRADE_TYPE_COMMISSION_TRANSFER = 1210; // 佣金付款
    const TRADE_TYPE_BONUS = 1211;               // 分红
    const TRADE_TYPE_BUY_SP = 1212;              // 购买SP
    const TRADE_TYPE_SALE_SP = 1213;             // 销售SP
    const TRADE_TYPE_FAVORABLE = 1214;           // 线下买单
    const TRADE_TYPE_OTHER = 1215;               // 线下买单
    const TRADE_TYPE_BUY_SELLER = 1216;          // 升级为商家
    const TRADE_TYPE_SALE_SELLER = 1217;         // 销售升级为商家
    const TRADE_TYPE_WITHDRAW_CANCEL = 1218;     // 提现驳回
    const TRADE_TYPE_RETURN_GROUPBOOKING = 1219; // 拼团失败退款
    const TRADE_TYPE_TRANSFER_COMMISSION = 1220; // 转单分佣
    const TRADE_TYPE_PAOTUI_FEE = 1221;          // 跑腿运费
    const TRADE_TYPE_REBATE = 1222;              // 购物返利

    const TRADE_TYPE_HALL_FEE = 1221; // 需求订单

    const TRADE_TYPE_BUY_POINTS = 1223; // 购买积分
    const TRADE_TYPE_SALE_POINTS = 1224; // 销售积分
    const TRADE_TYPE_STORE_RENEW = 1225; // 店铺续费

    const TRADE_TYPE_BUY_POSTER = 1226; // 购买广告
    const TRADE_TYPE_SHOPPING_CARD = 1227; // 充值卡消费
    const TRADE_TYPE_DEPOSIT_CARD = 1228; // 充值卡充值

    const TRADE_TYPE_OFFLINE_INCREASE = 1501; // 线下记账收入
    const TRADE_TYPE_OFFLINE_DECREASE = 1502; // 线下记账支出

    const TRADE_TYPE_DOCTOR_BUY = 1503; // 购物
    const TRADE_TYPE_DOCTOR_SERVICE = 1504; // 销售

    const TRADE_TYPE_XQ_BUY = 1505; // 购物
    const TRADE_TYPE_XQ_SERVICE = 1506; // 销售

    const PAYMENT_TYPE_DELIVER = 1301; // 货到付款
    const PAYMENT_TYPE_ONLINE = 1302; // 在线支付
    const PAYMENT_TYPE_OFFLINE = 1305; // 线下支付

    const ORDER_ITEM_EVALUATION_NO = 0; // 未评价
    const ORDER_ITEM_EVALUATION_YES = 1; // 已评价
    const ORDER_ITEM_EVALUATION_TIMEOUT = 2; // 失效评价

    const ORDER_PICKUP_CODE_UNUSED = 0; // 未评价
    const ORDER_PICKUP_CODE_USED = 1; // 已评价
    const ORDER_PICKUP_CODE_TIMEOUT = 2; // 失效评价

    const ORDER_EVALUATION_NO = 0; // 未评价
    const ORDER_EVALUATION_YES = 1; // 已评价
    const ORDER_EVALUATION_TIMEOUT = 2; // 失效评价

    const ORDER_NOT_NEED_RETURN_GOODS = 0; // 不用退货
    const ORDER_NEED_RETURN_GOODS = 1; // 需要退货

    const ORDER_REFUND = 1; // 1-退款申请 2-退货申请 3-虚拟退款
    const ORDER_RETURN = 2; // 需要退货
    const ORDER_VIRTUAL_REFUND = 3; // 需要退货


    const USER_CERTIFICATION_NO = 0; //0-未认证;
    const USER_CERTIFICATION_VERIFY = 1; //待审核
    const USER_CERTIFICATION_YES = 2; //认证通过
    const USER_CERTIFICATION_FAILED = 3; //认证失败


    const TO_STORE_SERVICE = 1001; // 到店取货
    const DOOR_TO_DOOR_SERVICE = 1002; // 上门服务

    const ORDER_AUTO_TRANSFER_HALL_TIME = 0.5; // 用户付款后多长时间没被接单就转到抢单大厅
    const ORDER_AUTO_REFUND_TIME = 1; // 用户付款后多长时间没被接单就自动退款

    const SUPPLY_TASK_STATE_BIDDING = 2000; // 竞标中
    const SUPPLY_TASK_STATE_OVER = 2010; // 已结束
    const SUPPLY_TASK_STATE_ACCEPTANCE = 2020; // 验收完成

    const LIVEANCHOR_COMMIT = 1; // 提交
    const LIVEANCHOR_PASS = 2; // 已审核通过
    const LIVEANCHOR_REFUSE = 3; // 审核拒绝

    const CHECK_STATE_NO = 1000; // 不需处理
    const CHECK_STATE_TODO = 1001; // 待处理
    const CHECK_STATE_OK = 1002; // 处理完成
    const CHECK_STATE_ERR = 1003; // 异常

    const CONTRACT_TYPE_7_RETURN = 1001; // 7天无理由退货
    const CONTRACT_TYPE_DENY_RETURN = 1006; // 不支持退货

    const ADMIN_PLANTFORM_USERID = 10001;

    const PAYMENT_MET_MONEY = 1; //余额支付
    const PAYMENT_MET_RECHARGE_CARD = 2; //充值卡支付
    const PAYMENT_MET_POINTS = 3; //积分支付
    const PAYMENT_MET_CREDIT = 4; //信用支付
    const PAYMENT_MET_REDPACK = 5; //红包支付
    const PAYMENT_MET_OFFLINE = 6; //线下支付
    const PAYMENT_MET_SP = 7; //众宝支付

    const  PAYMENT_CHANNEL_WECHAT = 1403; //微信支付
    const  PAYMENT_CHANNEL_ALIPAY = 1401; //支付宝支付
    const  PAYMENT_CHANNEL_OFFLINE = 1422; //线下支付
    const  PAYMENT_CHANNEL_MONEY = 1406; //余额支付
    const  PAYMENT_CHANNEL_POINTS = 1413; //积分支付

    const WX_MP_ACCESSTOKEN = "wx_token:mp_token"; // 公众号Access_Token
    const WX_XCX_ACCESSTOKEN = "wx_token:xcx_token"; // 小程序Access_Token

    const ACTIVITY_TYPE_GIFTBAG = 1132; // A+B组合套餐
    const ACTIVITY_TYPE_GIFTPACK = 1130; // 礼包活动
    const ACTIVITY_TYPE_MARKETING = 1131; // 市场活动
    const ACTIVITY_TYPE_LOTTERY = 1121; // 幸运大抽奖
    const ACTIVITY_TYPE_FLASHSALE = 1122; // 秒杀
    const ACTIVITY_TYPE_GROUPBOOKING = 1123; // 拼团
    const ACTIVITY_TYPE_CUTPRICE = 1124; // 砍价
    const ACTIVITY_TYPE_YIYUAN = 1125; // 一元购
    const ACTIVITY_TYPE_LIMITED_DISCOUNT = 1103; //限时折扣
    const ACTIVITY_TYPE_GROUPBUY_STORE = 1126; // 团购
    const ACTIVITY_TYPE_PF_GROUPBUY_STORE = 1127; // 批发团购

    const ACTIVITY_TYPE_MULTIPLEDISCOUNT = 1133; // 多件折
    const ACTIVITY_TYPE_BATDISCOUNT = 1135; // 阶梯价
    const ACTIVITY_TYPE_DOUBLE_POINTS = 1136; // 多倍积分
    const ACTIVITY_TYPE_POP = 1137; // 弹窗活动
    const ACTIVITY_TYPE_REDUCTION_AGAIN = 1140; // 折上折
    const ACTIVITY_TYPE_VOUCHER = 1105; // 优惠券


    const ACTIVITY_GROUPBOOKING_SALE_PRICE     = 1; //以固定折扣购买
    const ACTIVITY_GROUPBOOKING_FIXED_AMOUNT   = 2; //以固定价格购买
    const ACTIVITY_GROUPBOOKING_FIXED_DISCOUNT = 3; //优惠固定金额

    const VOUCHER_STATE_UNUSED = 1501; //未用
    const VOUCHER_STATE_USED = 1502; //已用
    const VOUCHER_STATE_TIMEOUT = 1503; //过期
    const VOUCHER_STATE_DEL = 1504; //收回
    const VOUCHER_STATE_UNTIMELY = 1505; //未到使用时间

}
