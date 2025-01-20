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

use App\Support\StateCode;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Marketing\Services\ActivityItemService;
use Modules\Pt\Services\ProductIndexService;

use Modules\Sys\Repositories\Contracts\PageBaseRepository;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use Modules\Sys\Repositories\Contracts\PageModuleRepository;
use Modules\Pt\Repositories\Contracts\ProductBrandRepository;
use Modules\Sys\Repositories\Contracts\PageMobileEntranceRepository;
use Modules\Pt\Repositories\Contracts\ProductCategoryRepository;
use Modules\Marketing\Repositories\Contracts\ActivityBaseRepository;
use Modules\Cms\Repositories\Contracts\ArticleCategoryRepository;
use Modules\Cms\Repositories\Contracts\ArticleBaseRepository;

use Modules\Sys\Repositories\Criteria\PageBaseCriteria;
use Modules\Sys\Repositories\Criteria\PageMobileEntranceCriteria;
use Modules\Pt\Repositories\Criteria\ProductCategoryCriteria;
use Modules\Cms\Repositories\Criteria\ArticleCategoryCriteria;
use Modules\Cms\Repositories\Criteria\ArticleBaseCriteria;
use Modules\Marketing\Repositories\Criteria\ActivityBaseCriteria;

use App\Exceptions\ErrorException;


/**
 * Class PageBaseService.
 *
 * @package Modules\Sys\Services
 */
class PageBaseService extends BaseService
{
    private $configBaseRepository;
    private $productBrandRepository;


    private $pageMobileEntranceRepository;
    private $productCategoryRepository;
    private $articleBaseRepository;
    private $articleCategoryRepository;
    private $activityBaseRepository;
    private $pageModuleRepository;


    private $productIndexService;

    public function __construct(
        PageBaseRepository           $pageBaseRepository,
        ConfigBaseRepository         $configBaseRepository,
        PageMobileEntranceRepository $pageMobileEntranceRepository,
        ProductBrandRepository       $productBrandRepository,
        ProductCategoryRepository    $productCategoryRepository,
        ArticleCategoryRepository    $articleCategoryRepository,
        ArticleBaseRepository        $articleBaseRepository,
        ActivityBaseRepository       $activityBaseRepository,
        PageModuleRepository         $pageModuleRepository,

        ProductIndexService          $productIndexService,
    )
    {
        $this->repository = $pageBaseRepository;
        $this->pageModuleRepository = $pageModuleRepository;
        $this->configBaseRepository = $configBaseRepository;
        $this->productCategoryRepository = $productCategoryRepository;
        $this->pageMobileEntranceRepository = $pageMobileEntranceRepository;
        $this->productBrandRepository = $productBrandRepository;
        $this->articleCategoryRepository = $articleCategoryRepository;
        $this->articleBaseRepository = $articleBaseRepository;
        $this->activityBaseRepository = $activityBaseRepository;

        $this->productIndexService = $productIndexService;
    }


    /**
     * 修改页面状态
     * @param $request
     * @param $page_id
     * @return bool
     * @throws ErrorException
     */
    public function editState($page_id, $request)
    {
        DB::beginTransaction();

        try {
            $state_fields = [
                'page_index',
                'page_gb',
                'page_activity',
                'page_point',
                'page_gbs',
            ];

            $state_data = [];
            $page_row = $this->repository->getOne($page_id);

            foreach ($state_fields as $field) {
                if ($request->has($field)) {
                    $new_value = $request->boolean($field, false);
                    $state_data[$field] = $new_value;

                    if ($new_value != $page_row[$field]) {
                        $this->repository->editWhere([$field => true], [$field => false]);
                    }
                }
            }

            if (!empty($state_data)) {
                $this->repository->edit($page_id, $state_data);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('操作失败: ') . $e->getMessage());
        }

        return true;
    }


    /**
     * PC端装修数据
     * @param $where
     * @return array|mixed
     * @throws ErrorException
     */
    public function pcDetail($where = [])
    {
        $page_base_row = $this->repository->findOne($where);
        $page_id = $page_base_row['page_id'];
        $page_modules = $this->pageModuleRepository->find(['page_id' => $page_id, 'pm_enable' => 1], ['pm_order' => 'asc']);
        $page_modules = $this->fixPcPageModuleData($page_modules);

        return $page_modules;
    }


    /**
     * PC页面楼层绑定商品等数据
     * @param $data
     * @return array|mixed
     * @throws ErrorException
     */
    public function fixPcPageModuleData($data = array())
    {
        if (!empty($data)) {
            foreach ($data as $pm_id => $module_row) {
                switch ($module_row['module_id']) {
                    case 1001:
                    case 1004:
                    case 1005:
                    case 1006:
                    case 2000:
                    case 2001:
                    case 2003:
                    case 2004:
                    case 3002:
                        //读取商品
                        $item_ids = array();
                        $pm_json = $data[$pm_id]['pm_json'];
                        if (isset($pm_json['tabs']) && $pm_json['tabs']) {
                            foreach ($pm_json['tabs'] as $index => $tab) {
                                $item_ids = array_merge((array)$item_ids, (array)array_column($tab['items'], 'item_id'));
                            }
                            $item_ids = array_unique($item_ids);
                            $item_rows = $this->productIndexService->getItems($item_ids);

                            foreach ($pm_json['tabs'] as $index => $tab) {
                                foreach ($tab['items'] as $k => $item) {

                                    if (isset($item_rows[$item['item_id']])) {
                                        if ($item_rows[$item['item_id']]['item_enable'] != StateCode::PRODUCT_STATE_NORMAL) {
                                            unset($pm_json['tabs'][$index]['items'][$k]);
                                            continue;
                                        }

                                        $item_row = $item_rows[$item['item_id']];
                                        $pm_json['tabs'][$index]['items'][$k]['item_unit_price'] = $item_row['item_unit_price'];
                                        $pm_json['tabs'][$index]['items'][$k]['item_sale_price'] = $item_row['item_sale_price'];
                                        $pm_json['tabs'][$index]['items'][$k]['item_market_price'] = $item_row['item_market_price'];
                                        $pm_json['tabs'][$index]['items'][$k]['picimg'] = $item_row['product_image'];
                                        $pm_json['tabs'][$index]['items'][$k]['name'] = $item_row['product_item_name'];

                                        //参与活动
                                        $pm_json['tabs'][$index]['items'][$k]['activity_type_id'] = 0;
                                        $pm_json['tabs'][$index]['items'][$k]['activity_type_name'] = '';
                                        $activityItemService = app(ActivityItemService::class);
                                        $activity_item_data = $activityItemService->getNormalActivityItems($item_ids);
                                        $activity_item_rows = array_column($activity_item_data['items'], null, 'item_id');
                                        if (isset($item['item_id']) && isset($activity_item_rows[$item['item_id']]['activity_type_id'])) {
                                            $pm_json['tabs'][$index]['items'][$k]['activity_type_id'] = $activity_item_rows[$item['item_id']]['activity_type_id'];
                                            $pm_json['tabs'][$index]['items'][$k]['item_sale_price'] = $activity_item_rows[$item['item_id']]['activity_item_price'];
                                        }
                                    } else {
                                        unset($pm_json['tabs'][$index]['items'][$k]);
                                        continue;
                                    }
                                }
                            }
                        }

                        $data[$pm_id]['pm_json'] = $pm_json;
                        break;

                    case 1104:
                        //获取推荐品牌
                        $brand_rows = $this->productBrandRepository->find(array(
                            'brand_recommend' => 1,
                            'brand_enable' => 1
                        ), array(), 1, 30);
                        $data[$pm_id]['pm_json']['brand_rows'] = array_values($brand_rows);
                        break;
                }
            }
        }

        return $data;
    }


    /**
     * 保存模板
     */
    public function saveMobile($data)
    {
        //根据tpl_id, 获取App Id
        $app_row = $this->repository->findOne([
            'store_id' => $data['store_id'],
            'page_tpl' => $data['page_tpl'],
            'page_type' => 3
        ]);

        if ($app_row) {
            $data['app_id'] = $app_row['app_id'];
            $page_row = $this->repository->getOne($data['page_id']);
            if (empty($page_row)) {
                $result = $this->repository->add($data);
            } else {
                if ($page_row && $page_row['store_id'] != $data['store_id']) {
                    throw new ErrorException(__('权限有误！'));
                }

                $result = $this->repository->edit($data['page_id'], $data);
            }

            if (!$result) {
                throw new ErrorException(__('保存模板数据失败'));
            }
        } else {
            throw new ErrorException(__('权限有误！'));
        }

        return $result;
    }


    /**
     * 手机端首页装修数据
     * @param $where
     * @return mixed
     * @throws ErrorException
     */
    public function mobileDetail($where = [])
    {
        $page_base_row = $this->repository->findOne($where);
        $page_base_row = $this->fixData($page_base_row);

        return $page_base_row;
    }


    /**
     * 手机页面装修数据
     * @param $page_base_res
     * @return mixed
     * @throws ErrorException
     */
    private function fixData($page_base_res)
    {
        $im_enable = $this->configBaseRepository->getConfig("im_enable", false);
        $im_user_id = $this->configBaseRepository->getConfig("site_im", 10001);
        $page_base_res['im_enable'] = $im_enable;
        $page_base_res['im_user_id'] = $im_user_id;
        $page_base_res['puid'] = 0;

        if (!empty($user)) {
            $service_user_id = $this->configBaseRepository->getConfig("service_user_id");
            $page_base_res['puid'] = $service_user_id;
        }

        $page_code_rows = json_decode($page_base_res['page_code'], true);
        if (!is_array($page_code_rows)) {
            $page_code_rows = array();
        }
        $item_id_row = array();
        $item_id_104 = [];

        foreach ($page_code_rows as $k => $page_code_row) {

            //商品列表组件
            if ($page_code_row['eltmType'] == 4) {
                if (!empty($page_code_row['eltm4']['data'])) {
                    $item_id_row = array_merge($item_id_row, array_column($page_code_row['eltm4']['data'], 'did'));
                }
            }

            if ($page_code_row['eltmType'] == 16) {
                if (!empty($page_code_row['eltm4']['data'])) {
                    $item_id_row = array_merge($item_id_row, array_column($page_code_row['eltm16']['data'], 'did'));
                }
            }

            if ($page_code_row['eltmType'] == 104) {
                if (!empty($page_code_row['eltm104']['data'])) {
                    $eltm104_data = $page_code_row['eltm104']['data'];
                    if (!empty($eltm104_data)) {
                        foreach ($eltm104_data as $dk => $row) {
                            $ids = $row['ids'];
                            $ids = array_filter(explode(',', $ids));
                            $page_code_rows[$k]['eltm104']['data'][$dk]['ids'] = $ids;
                            if (!empty($ids)) {
                                $item_id_104 = array_merge($item_id_104, $ids);
                            }
                        }
                    }
                }
            }
        }

        if (!empty($item_id_row)) {
            $currency_exchange_rate = 1;
            $product_item_rows = $this->productIndexService->getItems($item_id_row);

            foreach ($page_code_rows as $id => $page_code_row) {
                $eltmType = $page_code_row["eltmType"];
                switch ($eltmType) {
                    case 4:
                    case 16:
                        $eltm_key = "eltm" . $eltmType;
                        $eltm_data = $page_code_row[$eltm_key];
                        if (!empty($eltm_data) && isset($eltm_data["data"]) && !empty($eltm_data["data"])) {
                            $data = $eltm_data["data"];
                            foreach ($data as $index => $item) {
                                $product_item_row = [];
                                if (isset($product_item_rows[$item['did']]) && $product_item_rows[$item['did']]) {
                                    $product_item_row = $product_item_rows[$item['did']];
                                }

                                if (!empty($product_item_row)) {
                                    $item_unit_price = $product_item_row['item_unit_price'];
                                    $ItemSalePrice = $product_item_row['item_unit_price'];

                                    if ($currency_exchange_rate != 1 && $item_unit_price > 0) {
                                        $item_unit_price = bcmul($item_unit_price, $currency_exchange_rate, 2);
                                    }
                                    if ($ItemSalePrice != 1 && $ItemSalePrice > 0) {
                                        $ItemSalePrice = bcmul($ItemSalePrice, $currency_exchange_rate, 2);
                                    }

                                    $data[$index]['item_unit_price'] = $item_unit_price;
                                    $data[$index]['ItemSalePrice'] = $ItemSalePrice;
                                }
                            }

                            $page_code_rows[$id][$eltm_key]['data'] = array_values($data);
                        }

                        break;
                }
            }
        }

        $page_base_res['page_code'] = json_encode($page_code_rows);

        return $page_base_res;
    }


    /**
     * 手机端装修页面根据类型选择数据
     * @param $request
     * @return array|mixed[]
     */
    public function getDataInfo($request)
    {
        $type = $request->input('type', 0);
        $search_name = $request->input('name', '');
        switch ($type) {
            case 1:
            case 104:
                //todo 获取商品数据
                $request['size'] = 8;
                $request['product_name'] = $search_name;
                $request['item_enable'] = StateCode::PRODUCT_STATE_NORMAL;
                $data = $this->productIndexService->listItem($request);
                if (!empty($data['data'])) {
                    foreach ($data['data'] as $k => $item) {
                        $data['data'][$k] = [
                            'id' => $item['item_id'],
                            'path' => $item['product_image'],
                            'name' => $item['product_item_name'],
                            'MarketPice' => $item['item_market_price'],
                            'ItemSalePrice' => $item['item_unit_price'],
                            'ProductTips' => $item['product_tips'],
                            'flexNum' => 0,
                            'selectType' => 0
                        ];
                    }
                }
                break;
            case 2:
                //todo 获取分类数据
                $request['category_name'] = $search_name;
                $request['category_is_enable'] = 1;
                $data = $this->productCategoryRepository->list(new ProductCategoryCriteria($request), 8);
                if (!empty($data['data'])) {
                    foreach ($data['data'] as $k => $item) {
                        $data['data'][$k] = [
                            'id' => $item['category_id'],
                            'path' => $item['category_image'],
                            'name' => $item['category_name'],
                            'flexNum' => 0,
                            'selectType' => 0
                        ];
                    }
                }
                break;
            case 4:
                //todo 快捷入口数据
                $request['entrance_name'] = $search_name;
                $request['entrance_enable'] = 1;
                $data = $this->pageMobileEntranceRepository->list(new PageMobileEntranceCriteria($request), 50);
                if (!empty($data['data'])) {
                    foreach ($data['data'] as $k => $item) {
                        $data['data'][$k] = [
                            'id' => $item['entrance_id'],
                            'path' => $item['entrance_image'],
                            'name' => $item['entrance_name'],
                            "AppUrl" => $item['entrance_path'],
                            'flexNum' => 0,
                            'selectType' => 0
                        ];
                    }
                }
                break;
            case 5:
                // todo 获取资讯分类
                $request['category_name'] = $search_name;
                $data = $this->articleCategoryRepository->list(new ArticleCategoryCriteria($request), 8);
                if (!empty($data['data'])) {
                    foreach ($data['data'] as $k => $item) {
                        $data['data'][$k] = [
                            'id' => $item['category_id'],
                            'path' => $item['category_image_url'],
                            'name' => $item['category_name'],
                            "AppUrl" => sprintf("/pagesub/article/list?cid=%d", $item['category_id']),
                            'flexNum' => 0,
                            'selectType' => 0
                        ];
                    }
                }
                break;
            case 6:
                //todo 获取资讯列表
                $request['article_title'] = $search_name;
                $request['article_status'] = 1;
                $data = $this->articleBaseRepository->list(new ArticleBaseCriteria($request), 8);
                if (!empty($data['data'])) {
                    foreach ($data['data'] as $k => $item) {
                        $data['data'][$k] = [
                            'id' => $item['article_id'],
                            'path' => $item['article_image'],
                            'name' => $item['article_title'],
                            "AppUrl" => sprintf("/pagesub/article/detail?id=%d", $item['article_id']),
                            'flexNum' => 0,
                            'selectType' => 0
                        ];
                    }
                }
                break;
            case 8:
                //todo 获取手机页面
                $request['page_type'] = 3;
                $request['page_name'] = $search_name;
                $data = $this->repository->list(new PageBaseCriteria($request), 8);

                if (!empty($data['data'])) {
                    foreach ($data['data'] as $k => $item) {
                        $data['data'][$k] = [
                            'id' => $item['page_id'],
                            'name' => $item['page_name'],
                            'flexNum' => 0,
                            'selectType' => 0
                        ];
                    }
                }
                break;
            case 17:
                //todo 获取优惠券列表
                $size = 8;
                $request['activity_name'] = $search_name;
                $request['activity_state'] = [StateCode::ACTIVITY_STATE_NORMAL, StateCode::ACTIVITY_STATE_WAITING];
                $request['activity_type_id'] = StateCode::ACTIVITY_TYPE_VOUCHER;
                $data = $this->activityBaseRepository->list(new ActivityBaseCriteria($request), $size);
                if (!empty($data['data'])) {
                    foreach ($data['data'] as $k => $item) {
                        $data['data'][$k] = [
                            'id' => $item['activity_id'],
                            'path' => $item['activity_rule']['voucher']['voucher_image'],
                            'name' => $item['activity_name'],
                            'ItemSalePrice' => $item['activity_rule']['voucher']['voucher_price'], //优惠券面额
                            'StartTime' => "/Date(-62135596800000)/",
                            'EndTime' => "/Date(-62135596800000)/",
                            'OrderCount' => intval($item['activity_rule']['voucher']['voucher_quantity_use']), //领取数量
                            'flexNum' => 0,
                            'selectType' => 0
                        ];
                    }
                }
                break;
            default:
                /**
                 * 10-获取社区分类
                 * 11-获取社区帖子
                 * 12-获取拼团商品
                 * 14获取秒杀商品
                 */
                $data = [];
                break;
        }

        return $data;
    }

}
