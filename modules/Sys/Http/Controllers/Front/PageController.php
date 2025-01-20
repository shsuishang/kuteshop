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


namespace Modules\Sys\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Sys\Services\PageBaseService;
use Modules\Sys\Services\PageCategoryNavService;
use Modules\Account\Repositories\Models\User;


class PageController extends BaseController
{
    private $pageBaseService;
    private $pageCategoryNavService;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        PageBaseService        $pageBaseService,
        PageCategoryNavService $pageCategoryNavService
    )
    {
        $this->pageBaseService = $pageBaseService;
        $this->pageCategoryNavService = $pageCategoryNavService;
    }


    /**
     * 首页装修数据
     */
    public function getPcPage(Request $request)
    {
        $page_type = $request->input('page_index', 'page_index');

        // 映射数组
        $page_mapping = [
            'page_index' => 'page_index',
            'page_sns' => 'page_sns',
            'page_article' => 'page_article',
            'page_point' => 'page_point',
            'page_upgrade' => 'page_upgrade',
            'page_zerobuy' => 'page_zerobuy',
            'page_higharea' => 'page_higharea',
            'page_taday' => 'page_taday',
            'page_everyday' => 'page_everyday',
            'page_secondkill' => 'page_secondkill',
            'page_secondday' => 'page_secondday',
            'page_rura' => 'page_rura',
            'page_likeyou' => 'page_likeyou',
            'page_exchange' => 'page_exchange',
            'page_new' => 'page_new',
            'page_newperson' => 'page_newperson',
        ];

        $where = ['page_type' => 2];

        // 动态设置条件
        if (isset($page_mapping[$page_type])) {
            $where[$page_mapping[$page_type]] = 1;
        } else {
            $where['page_index'] = 1; // 默认值
        }

        // 获取页面详情
        $page_rows = $this->pageBaseService->pcDetail($where);
        $data['floor'] = array_values($page_rows);

        return Respond::success($data);
    }


    /**
     * PC页面导航数据
     */
    public function pcLayout(Request $request)
    {
        $data = $this->pageCategoryNavService->getPcLayout($request);

        // 获取当前登录用户信息
        $user_row = User::getUser();
        if ($user_row) {
            $data['user_nickname'] = "Hi," . $user_row['user_nickname'] . "!";
            $data['user_avatar'] = $user_row['user_avatar'];
        }

        return Respond::success($data);
    }


    public function getMobilePage(Request $request)
    {
        $page_type = $request->input('page_index', 'page_index');

        // 定义字段映射
        $page_fields = [
            'page_index',
            'page_sns',
            'page_article',
            'page_point',
            'page_upgrade',
            'page_zerobuy',
            'page_higharea',
            'page_taday',
            'page_everyday',
            'page_secondkill',
            'page_secondday',
            'page_rura',
            'page_likeyou',
            'page_exchange',
            'page_new',
            'page_newperson',
        ];

        // 验证请求的类型是否有效
        if (in_array($page_type, $page_fields)) {
            $where = [$page_type => 1];
        } else {
            $where['page_index'] = 1; // 默认值
        }
        $where['page_type'] = 3;

        $page_base_res = $this->pageBaseService->mobileDetail($where);

        return Respond::success($page_base_res);
    }

}
