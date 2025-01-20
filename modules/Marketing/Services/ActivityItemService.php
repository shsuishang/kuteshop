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


namespace Modules\Marketing\Services;

use App\Support\StateCode;
use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Marketing\Repositories\Contracts\ActivityBaseRepository;
use Modules\Marketing\Repositories\Contracts\ActivityItemRepository;
use Modules\Pt\Repositories\Contracts\ProductBaseRepository;
use Modules\Pt\Repositories\Contracts\ProductIndexRepository;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;

/**
 * Class ActivityItemService.
 *
 * @package Modules\Marketing\Services
 */
class ActivityItemService extends BaseService
{

    private $activityBaseRepository;
    private $productItemRepository;
    private $productBaseRepository;
    private $productIndexRepository;
    private $userInfoRepository;

    public function __construct(
        ActivityItemRepository $activityItemRepository,
        ActivityBaseRepository $activityBaseRepository,
        ProductItemRepository  $productItemRepository,
        ProductBaseRepository  $productBaseRepository,
        ProductIndexRepository $productIndexRepository,
        UserInfoRepository     $userInfoRepository
    )
    {
        $this->repository = $activityItemRepository;
        $this->activityBaseRepository = $activityBaseRepository;
        $this->productItemRepository = $productItemRepository;
        $this->productBaseRepository = $productBaseRepository;
        $this->productIndexRepository = $productIndexRepository;
        $this->userInfoRepository = $userInfoRepository;
    }

    public function getActivityBuyItems($activity_id): array
    {
        $data = [];
        return $data;
    }

    public function addActivityBuyItems($request, &$msg)
    {
        return true;
    }

    public function addDiscountItem($activity_id, $product_items, $activity_base)
    {
        return true;
    }

    public function editActivityTypeIds($activity_base, $product_ids)
    {
        $flag_row = [];
        return is_ok($flag_row);
    }

    public function checkItem($item_ids, &$msg = '')
    {
        return $item_ids;
    }

    public function editActivityItem($request): mixed
    {
        return true;
    }

    public function removeItem($request): bool
    {
        return true;
    }

    public function getActivityInfo($item_ids, $user_id = 0): array
    {
        $data = [];

        return $data;
    }

    public function editBatchPrice($activity_id, $discount): bool
    {
        return true;
    }

    public function getNormalActivityItems($item_ids = [])
    {
        return [];
    }


}
