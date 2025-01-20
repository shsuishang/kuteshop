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


namespace Modules\Shop\Services;

use Illuminate\Support\Facades\Cache;
use Kuteshop\Core\Service\BaseService;
use Modules\Pt\Services\ProductIndexService;
use Modules\Shop\Repositories\Contracts\UserSearchHistoryRepository;

/**
 * Class UserProductBrowseService.
 *
 * @package Modules\Shop\Services
 */
class UserProductBrowseService extends BaseService
{

    public function __construct(UserSearchHistoryRepository $userSearchHistoryRepository)
    {
        $this->repository = $userSearchHistoryRepository;
    }


    /**
     * 浏览记录
     * @param $request
     * @return array
     */
    public function getList($request)
    {
        $data = [];
        $user_id = $request->get('user_id', 0);
        $cache_key = sprintf("user_id|%d", $user_id);
        $product_browse_rows = Cache::get($cache_key);
        $product_browse_rows = !empty($product_browse_rows) ? json_decode($product_browse_rows, true) : [];
        if (!empty($product_browse_rows)) {
            $item_ids = array_column($product_browse_rows, 'item_id');
            $item_rows = app(ProductIndexService::class)->getItems($item_ids);
            foreach ($product_browse_rows as $row) {
                if (isset($item_rows[$row['item_id']])) {
                    $data[] = $item_rows[$row['item_id']];
                }
            }
        }

        return $data;
    }


    /**
     * 添加
     * @param $item_id
     * @param $user_id
     * @return array|mixed
     */
    public function addBrowser($item_id, $user_id)
    {
        $product_browse = [
            'item_id' => $item_id,
            'browse_time' => getTime(),
        ];

        $cache_key = sprintf("user_id|%d", $user_id);
        $product_browse_rows = Cache::get($cache_key);
        $product_browse_rows = !empty($product_browse_rows) ? json_decode($product_browse_rows, true) : [];

        $product_browse_rows = array_filter($product_browse_rows, function ($browse) use ($item_id) {
            return $browse['item_id'] !== $item_id;
        });

        if (count($product_browse_rows) >= 10) {
            array_pop($product_browse_rows);
        }

        array_unshift($product_browse_rows, $product_browse);
        Cache::put($cache_key, json_encode($product_browse_rows));

        return $product_browse_rows;
    }


    /**
     * 删除
     * @param $item_id
     * @param $user_id
     * @return true
     */
    public function removeBrowser($item_id, $user_id)
    {
        $cache_key = sprintf("user_id|%d", $user_id);
        $product_browse_rows = Cache::get($cache_key);
        $product_browse_rows = !empty($product_browse_rows) ? json_decode($product_browse_rows, true) : [];

        $product_browse_rows = array_filter($product_browse_rows, function ($browse) use ($item_id) {
            return $browse['item_id'] !== $item_id;
        });

        Cache::put($cache_key, json_encode($product_browse_rows));

        return true;
    }

}
