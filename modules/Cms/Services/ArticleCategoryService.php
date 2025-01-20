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


namespace Modules\Cms\Services;

use App\Exceptions\ErrorException;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Cms\Repositories\Contracts\ArticleBaseRepository;
use Modules\Cms\Repositories\Contracts\ArticleCategoryRepository;
use Modules\Cms\Repositories\Criteria\ArticleCategoryCriteria;

class ArticleCategoryService extends BaseService
{
    private $articleBaseRepository;

    public function __construct(
        ArticleCategoryRepository $articleCategoryRepository,
        ArticleBaseRepository     $articleBaseRepository
    )
    {
        $this->repository = $articleCategoryRepository;
        $this->articleBaseRepository = $articleBaseRepository;
    }


    /**
     * 获取分类列表 平台后端
     */
    public function tree($request)
    {
        $this->repository->pushCriteria(new ArticleCategoryCriteria($request));
        $rows = $this->repository->orderBy('category_order', 'ASC')->orderBy('category_id', 'ASC')->all()->toArray();

        //todo 绑定上下级关系
        $data = ArrayToTree($rows, 0, 'children', 'category_');

        return $data;
    }


    /**
     * 创建
     */
    public function addCategory($request)
    {
        try {
            DB::beginTransaction();

            //todo 1、添加分类
            $this->repository->add($request);

            //todo 2、修改上级叶节点状态
            if ($request['category_parent_id']) {
                $this->repository->edit($request['category_parent_id'], ['category_is_leaf' => 0]);
            }

            //todo 3、提交事务
            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('添加失败: ') . $e->getMessage());
        }

    }


    /**
     * 删除分类 支持批量删除
     * @param $category_id
     * @return int
     * @throws ErrorException
     */
    public function removeCategory($category_id)
    {
        $row = $this->repository->getOne($category_id);
        if ($category_id && empty($row)) {
            throw new ErrorException(__('分类不存在'));
        }

        $sub_category = $this->repository->find(['category_parent_id' => $category_id]);
        if (!empty($sub_category)) {
            throw new ErrorException(__('该分类下有子分类,不允许删除'));
        }

        //todo 判断分类下是否有文章
        $article_base = $this->articleBaseRepository->find([['category_id', 'IN', [$category_id]]]);
        if (!empty($article_base)) {
            throw new ErrorException(__('该分类下有文章引用！'));
        }

        $del_category_parent_ids = array();
        $row['category_parent_id'] && array_push($del_category_parent_ids, $row['category_parent_id']);

        try {
            DB::beginTransaction();

            //todo 1、执行删除操作
            $this->repository->remove($category_id);

            //todo 2、修改上级叶节点状态
            $this->changeLeaf($del_category_parent_ids);

            //todo 3、提交事务
            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('删除失败: ') . $e->getMessage());
        }
    }

    //todo 更改叶结点状态
    public function changeLeaf($ids)
    {
        $flag_row = [];
        foreach ($ids as $id) {
            $exists = $this->repository->findWhere(['category_parent_id' => $id])->toArray();
            if (empty($exists)) {
                $flag_row[] = $this->repository->editWhere(['category_id' => $id], ['category_is_leaf' => 1]);
            }
        }

        return is_ok($flag_row);
    }

}
