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
use Modules\Pt\Repositories\Contracts\ProductCategoryRepository;
use Modules\Sys\Repositories\Contracts\PageCategoryNavRepository;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use Modules\Sys\Repositories\Contracts\PagePcNavRepository;
use Modules\Sys\Repositories\Criteria\PageCategoryNavCriteria;


/**
 * Class PageCategoryNavService.
 *
 * @package Modules\Sys\Services
 */
class PageCategoryNavService extends BaseService
{

    private $configBaseRepository;
    private $productCategoryRepository;
    private $pagePcNavRepository;


    public function __construct(
        PageCategoryNavRepository $pageCategoryNavRepository,
        ProductCategoryRepository $productCategoryRepository,
        ConfigBaseRepository      $configBaseRepository,
        PagePcNavRepository       $pagePcNavRepository,
    )
    {
        $this->repository = $pageCategoryNavRepository;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->configBaseRepository = $configBaseRepository;
        $this->pagePcNavRepository = $pagePcNavRepository;
    }


    public function getPcLayout($request)
    {
        //todo 1、获取分类导航数据
        $request['size'] = 999;
        $request['category_nav_enable'] = 1;
        $nav_category_rows = $this->list($request, new PageCategoryNavCriteria($request));
        $page_nav_category = $nav_category_rows['data'];

        //todo 获取商品分类
        $product_category_rows = $this->productCategoryRepository->find(['category_is_enable' => 1], ['category_sort' => 'ASC']);
        $product_category_trees = ArrayToTree($product_category_rows, 0, 'children', 'category_');
        $product_category_trees = array_column($product_category_trees, null, 'category_id');

        $item_ids = [];
        foreach ($page_nav_category as $nav_key => $nav_category) {
            $cur_item_ids = array_filter(explode(',', $nav_category['item_ids']));
            if (!empty($cur_item_ids)) {
                $item_ids = array_merge($item_ids, $cur_item_ids);
            }
            $page_nav_category[$nav_key]['item_ids'] = array_unique($cur_item_ids);

            //分类树形结构
            $page_nav_category[$nav_key]['product_category_tree'] = [];
            if (isset($product_category_trees[$nav_category['category_ids']])) {
                $page_nav_category[$nav_key]['product_category_tree'] = $product_category_trees[$nav_category['category_ids']];
            }
        }

        $data['category_nav'] = $page_nav_category;
        $data['all_item_ids'] = $item_ids;

        //todo 2、获取PC导航数据
        $page_pc_navs = $this->pagePcNavRepository->find(['nav_enable' => 1]);
        $data['page_pc_nav'] = array_values($page_pc_navs);

        //todo 3、获取首页底部帮助导航
        $data['footer_article'] = [];
        $page_pc_help = $this->configBaseRepository->getConfig('page_pc_help', '');
        if ($page_pc_help) {
            $data['footer_article'] = json_decode($page_pc_help, true);
        }

        return $data;
    }

}
