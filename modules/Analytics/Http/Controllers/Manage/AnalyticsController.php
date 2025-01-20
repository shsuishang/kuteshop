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


namespace Modules\Analytics\Http\Controllers\Manage;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Analytics\Services\AnalyticsOrderService;
use Modules\Analytics\Services\AnalyticsProductService;
use Modules\Analytics\Services\AnalyticsSysService;
use Modules\Analytics\Services\AnalyticsTradeService;
use Modules\Analytics\Services\AnalyticsUserService;

class AnalyticsController extends BaseController
{
    private $analyticsOrderService;
    private $analyticsTradeService;
    private $analyticsSysService;
    private $analyticsUserService;
    private $analyticsProductService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        AnalyticsOrderService   $analyticsOrderService,
        AnalyticsTradeService   $analyticsTradeService,
        AnalyticsSysService     $analyticsSysService,
        AnalyticsUserService    $analyticsUserService,
        AnalyticsProductService $analyticsProductService
    )
    {
        $this->analyticsOrderService = $analyticsOrderService;
        $this->analyticsTradeService = $analyticsTradeService;
        $this->analyticsSysService = $analyticsSysService;
        $this->analyticsUserService = $analyticsUserService;
        $this->analyticsProductService = $analyticsProductService;
    }

    //获取销售额
    public function getSalesAmount(Request $request)
    {
        $data = $this->analyticsTradeService->getSalesAmount($request);

        return Respond::success($data);
    }

    //获取用户访问量
    public function getVisitor()
    {
        $data = $this->analyticsSysService->getVisitor();

        return Respond::success($data);
    }

    //获取新增用户
    public function getRegUser()
    {
        $data = $this->analyticsUserService->getRegUser();

        return Respond::success($data);
    }

    //仪表板看板柱形图数据
    public function getDashboardTimeLine(Request $request)
    {
        $data = $this->analyticsOrderService->getDashboardTimeLine($request);

        return Respond::success($data);
    }

    //时间用户统计
    public function getUserTimeLine(Request $request)
    {
        $stime = $request->get('stime', 0);
        $etime = $request->get('etime', 0);
        $data = $this->analyticsUserService->getUserTimeLine($stime, $etime);

        return Respond::success($data);
    }

    //用户统计
    public function getUserNum(Request $request)
    {
        $data = $this->analyticsUserService->getUserNum($request);

        return Respond::success($data);
    }

    public function getAccessNum(Request $request)
    {
        $data = $this->analyticsSysService->getAccessNum($request);

        return Respond::success($data);
    }

    public function getAccessVisitorTimeLine(Request $request)
    {
        $data = $this->analyticsSysService->getAccessVisitorTimeLine($request);

        return Respond::success($data);
    }


    public function getAccessVisitorNum(Request $request)
    {
        $data = $this->analyticsSysService->getAccessVisitorNum($request);

        return Respond::success($data);
    }


    //获取订单量
    public function getOrderNum()
    {
        $data = $this->analyticsOrderService->getOrderNum();

        return Respond::success($data);
    }

    public function getOrderAmount(Request $request)
    {
        $data = $this->analyticsOrderService->getOrderAmount($request);

        return Respond::success($data);
    }


    //订单销售金额对比图
    public function getSaleOrderAmount(Request $request)
    {
        $data = $this->analyticsOrderService->getSaleOrderAmount($request);

        return Respond::success($data);
    }


    //消费客户统计
    public function getCustomerTimeline(Request $request)
    {
        $days = $request->input('days');
        $data = $this->analyticsOrderService->getCustomerTimeline($days);

        return Respond::success($data);
    }


    public function getOrderNumToday(Request $request)
    {
        $data = $this->analyticsOrderService->getOrderNum();

        return Respond::success($data);
    }


    public function getOrderCustomerNumTimeline(Request $request)
    {
        $data = $this->analyticsOrderService->getOrderCustomerNumTimeline($request);

        return Respond::success($data);
    }


    public function getOrderNumTimeline(Request $request)
    {
        $data = $this->analyticsOrderService->getOrderNumTimeline($request);

        return Respond::success($data);
    }

    public function getOrderItemNumTimeLine(Request $request)
    {
        $data = $this->analyticsOrderService->getOrderItemNumTimeLine($request);

        return Respond::success($data);
    }

    public function getProductNum(Request $request)
    {
        $data = $this->analyticsProductService->getProductNum($request);

        return Respond::success($data);
    }

    public function getAccessItemNum(Request $request)
    {
        $data = $this->analyticsSysService->getAccessItemNum($request);

        return Respond::success($data);
    }

    public function getAccessItemUserNum(Request $request)
    {
        $data = $this->analyticsSysService->getAccessItemUserNum($request);

        return Respond::success($data);
    }

    public function getOrderItemNum(Request $request)
    {
        $data = $this->analyticsOrderService->getOrderItemNum($request);

        return Respond::success($data);
    }

    public function listOrderItemNum(Request $request)
    {
        $data = $this->analyticsOrderService->listOrderItemNum($request);

        return Respond::success($data);
    }

    public function getAccessItemUserTimeLine(Request $request)
    {
        $data = $this->analyticsSysService->getAccessItemUserTimeLine($request);

        return Respond::success($data);
    }

    public function listAccessItem(Request $request)
    {
        $data = $this->analyticsSysService->listAccessItem($request);

        return Respond::success($data);
    }

    public function getAccessItemTimeLine(Request $request)
    {
        $data = $this->analyticsSysService->getAccessItemTimeLine($request);

        return Respond::success($data);
    }

}
