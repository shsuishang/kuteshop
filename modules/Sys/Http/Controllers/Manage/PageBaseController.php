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
use Modules\Sys\Repositories\Criteria\PageBaseCriteria;
use Modules\Sys\Repositories\Validators\PageBaseValidator;
use Modules\Sys\Services\PageBaseService;

class PageBaseController extends BaseController
{
    private $pageBaseService;
    private $pageBaseValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PageBaseService $pageBaseService, PageBaseValidator $pageBaseValidator)
    {
        $this->pageBaseService = $pageBaseService;
        $this->pageBaseValidator = $pageBaseValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->pageBaseService->list($request, new PageBaseCriteria($request));

        return Respond::success($data);
    }


    /**
     * 格式化请求数据
     * @param $request
     * @return array
     */
    public function formatRequest($request)
    {
        $data = [
            'page_name' => $request['page_name'],  //名称
            'page_type' => $request['page_type'],  //页面类型
            'page_tpl' => $request['page_tpl'],   //页面布局模板
            'page_code' => $request['page_code'],  //页面代码
            'page_nav' => $request['page_nav'],   //导航数据
        ];

        return $data;
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->pageBaseValidator->with($request->all())->passesOrFail('create');
        $data = $this->pageBaseService->add($this->formatRequest($request));

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $page_id = $request['page_id'];
        if (!$request->filled('page_name')) {
            $data = $this->pageBaseService->editState($request, $page_id);
        } else {
            $this->pageBaseValidator->setId($page_id);
            $this->pageBaseValidator->with($request->all())->passesOrFail('update');
            $data = $this->pageBaseService->edit($page_id, $this->formatRequest($request));
        }

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $data = $this->pageBaseService->remove($request['page_id']);

        return Respond::success($data);
    }


    /**
     * 修改状态
     */
    public function editState(Request $request)
    {
        $page_id = $request->input('page_id', -1);
        $data = $this->pageBaseService->editState($page_id, $request);

        return Respond::success($data);
    }


    /**
     * 手机页面列表
     */
    public function listMobile(Request $request)
    {
        $request['size'] = 999;
        $data = $this->pageBaseService->list($request, new PageBaseCriteria($request));
        if ($data['data']) {
            foreach ($data['data'] as $k => $v) {
                $data['data'][$k]['AppId'] = $v['app_id'];
                $data['data'][$k]['Id'] = $v['page_id'];
                $data['data'][$k]['IsActivity'] = $v['page_activity'];
                $data['data'][$k]['IsArticle'] = $v['page_article'];
                $data['data'][$k]['IsGb'] = $v['page_gb'];
                $data['data'][$k]['IsHome'] = $v['page_index'];
                $data['data'][$k]['IsPoint'] = $v['page_point'];
                $data['data'][$k]['IsRelease'] = $v['page_release'];
                $data['data'][$k]['IsSns'] = $v['page_sns'];
                $data['data'][$k]['IsUpgrade'] = $v['page_upgrade'];
                $data['data'][$k]['PageCode'] = $v['page_code'];
                $data['data'][$k]['PageNav'] = $v['page_nav'];
                $data['data'][$k]['PageQRCode'] = $v['page_qrcode'];
                $data['data'][$k]['PageTitle'] = $v['page_name'];
                $data['data'][$k]['ShareImg'] = $v['page_share_image'];
                $data['data'][$k]['ShareTitle'] = $v['page_share_title'];
                $data['data'][$k]['StoreId'] = $v['store_id'];
            }
        }

        return Respond::success($data);
    }


    /**
     * 保存手机模板
     * 从原多商户copy过来的代码
     */
    public function saveMobile(Request $request)
    {
        $app_page_list = $request->input('app_page_list');
        $app_page_list_rows = json_decode($app_page_list, true);
        $store_id = $request->input('store_id', 0);
        $subsite_id = $request->input('subsite_id', 0);
        $tpl_id = $request->input('tpl_id');

        foreach ($app_page_list_rows as $page_nav_row) {
            $data = array();
            $data['store_id'] = $store_id;
            $data['page_tpl'] = $tpl_id;
            if ($page_nav_row['Id']) {
                $data['page_id'] = $page_nav_row['Id'];
            }

            $data['page_type'] = 3;
            $data['page_index'] = intval($page_nav_row['IsHome']);
            $data['page_name'] = $page_nav_row['PageTitle']; // 模块名称
            $data['page_code'] = $page_nav_row['PageCode'];
            $data['page_nav'] = $page_nav_row['PageNav'];
            if (isset($page_nav_row['PageConfig']) && $page_nav_row['PageConfig']) {
                $data['page_config'] = $page_nav_row['PageConfig'];
            }

            $data['page_share_title'] = $page_nav_row['ShareTitle'];
            $data['page_share_image'] = $page_nav_row['ShareImg'];
            $data['subsite_id'] = $subsite_id;

            $result = $this->pageBaseService->saveMobile($data);
        }

        return Respond::success($data);
    }


    public function getDataInfo(Request $request)
    {
        $data = $this->pageBaseService->getDataInfo($request);

        return Respond::success($data);
    }

}
