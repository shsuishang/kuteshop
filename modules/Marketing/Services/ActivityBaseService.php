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
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Contracts\UserLevelRepository;
use Modules\Marketing\Repositories\Contracts\ActivityBaseRepository;
use App\Exceptions\ErrorException;
use Modules\Marketing\Repositories\Contracts\ActivityItemRepository;
use Modules\Marketing\Repositories\Criteria\ActivityBaseCriteria;
use Modules\Pt\Repositories\Contracts\ProductBaseRepository;
use Modules\Pt\Repositories\Contracts\ProductIndexRepository;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;
use Modules\Shop\Repositories\Contracts\UserVoucherNumRepository;


/**
 * Class ActivityBaseService.
 *
 * @package Modules\Marketing\Services
 */
class ActivityBaseService extends BaseService
{

    private $activityItemRepository;
    private $userVoucherNumRepository;
    private $userLevelRepository;
    private $userInfoRepository;
    private $productItemRepository;
    private $productBaseRepository;
    private $productIndexRepository;

    public function __construct(
        ActivityBaseRepository   $activityBaseRepository,
        ActivityItemRepository   $activityItemRepository,
        UserVoucherNumRepository $userVoucherNumRepository,
        UserLevelRepository      $userLevelRepository,
        UserInfoRepository       $userInfoRepository,
        ProductItemRepository    $productItemRepository,
        ProductBaseRepository    $productBaseRepository,
        ProductIndexRepository   $productIndexRepository
    )
    {
        $this->repository = $activityBaseRepository;
        $this->activityItemRepository = $activityItemRepository;
        $this->userVoucherNumRepository = $userVoucherNumRepository;
        $this->userLevelRepository = $userLevelRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->productItemRepository = $productItemRepository;
        $this->productBaseRepository = $productBaseRepository;
        $this->productIndexRepository = $productIndexRepository;
    }


    /**
     * 获取列表
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list($criteria, $limit);
        $data['limit'] = $limit;

        return $data;
    }

    public function checkActivityState($activity_base): array
    {
        return $activity_base;
    }

    public function getActivityItems($activity_ids): array
    {
        $activity_product_items = [];
        return $activity_product_items;
    }

    public function editState($request)
    {
        return true;
    }

    public function editActivityBase($activity_id, $activity_base)
    {
        return true;
    }

    public function editActivityTypeIds($activity_base, $product_ids)
    {

        $flag_row = [];
        return is_ok($flag_row);
    }

    public function listVoucher($request, $user_id)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list(new ActivityBaseCriteria($request), $limit);

        return $data;
    }

    public function updateActivityState()
    {
        return true;
    }

}
