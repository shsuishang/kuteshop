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


namespace Modules\Account\Services;

use App\Exceptions\ErrorException;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserTagBaseRepository;
use Modules\Account\Repositories\Contracts\UserTagGroupRepository;

/**
 * Class UserTagGroupService.
 *
 * @package Modules\Account\Services
 */
class UserTagGroupService extends BaseService
{

    private $userTagBaseRepository;

    public function __construct(UserTagGroupRepository $userTagGroupRepository, UserTagBaseRepository $userTagBaseRepository)
    {
        $this->repository = $userTagGroupRepository;
        $this->userTagBaseRepository = $userTagBaseRepository;
    }


    /**
     * @param $request
     * @return array
     */
    public function tree($request)
    {
        $tag_group_tree = [];
        $user_tag_group = $this->repository->find(['tag_group_enable' => 1]);
        if (!empty($user_tag_group)) {
            foreach ($user_tag_group as $tag_group) {
                $user_tag_rows = $this->userTagBaseRepository->find([
                    'tag_group_id' => $tag_group['tag_group_id'],
                    'tag_enable' => 1
                ]);

                if (!empty($user_tag_rows)) {
                    $tag_group_tree[] = [
                        'tag_title' => $tag_group['tag_group_name'],
                        'children' => array_values($user_tag_rows)
                    ];
                }
            }
        }

        return array_values($tag_group_tree);
    }


    /**
     * 删除
     * @param $tag_group_id
     * @return int|true
     * @throws ErrorException
     */
    public function remove($tag_group_id)
    {
        if ($this->userTagBaseRepository->find(['tag_group_id' => $tag_group_id])) {
            throw new ErrorException(__('分组下有标签不可删除'));
        }
        $flag = $this->repository->remove($tag_group_id);

        return $flag;
    }

}
