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
use Modules\Cms\Repositories\Contracts\ArticleCommentRepository;
use Modules\Cms\Repositories\Contracts\ArticleTagRepository;
use Modules\Cms\Repositories\Criteria\ArticleBaseCriteria;

class ArticleBaseService extends BaseService
{
    private $articleTagRepository;
    private $articleCategoryRepository;
    private $articleCommentRepository;

    public function __construct(
        ArticleBaseRepository     $articleBaseRepository,
        ArticleTagRepository      $articleTagRepository,
        ArticleCategoryRepository $articleCategoryRepository,
        ArticleCommentRepository  $articleCommentRepository
    )
    {
        $this->repository = $articleBaseRepository;
        $this->articleTagRepository = $articleTagRepository;
        $this->articleCategoryRepository = $articleCategoryRepository;
        $this->articleCommentRepository = $articleCommentRepository;
    }


    /**
     * 获取列表
     * @return array
     */
    public function getLists($request)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list(new ArticleBaseCriteria($request), $limit);

        if (!empty($data['data'])) {
            $items = $data['data'];

            //todo 获取分类名称
            $category_ids = array_column_unique($data['data'], 'category_id');
            $category_rows = $this->articleCategoryRepository->gets($category_ids);

            foreach ($items as $k => $item) {
                $items[$k]['category_name'] = $category_rows[$item['category_id']]['category_name'];

                if (!empty($item['article_tags'])) {
                    //todo 获取该文章绑定Tag
                    $tag_ids = explode(",", $item['article_tags']);
                    $tag_rows = $this->articleTagRepository->find([['tag_id', 'IN', $tag_ids]]);
                    $items[$k]['article_tag_list'] = $tag_rows;
                }
            }

            $data['data'] = $items;
        }

        return $data;
    }


    /**
     * 新增文章
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function addArticleBase($request)
    {
        try {
            DB::beginTransaction();

            $article_data = $request;
            $article_flag = $this->repository->add($article_data);

            if ($article_flag) {
                //todo 增加分类内容数目
                $category_id = $article_data['category_id'] ?? 0;
                if ($category_id && $this->articleCategoryRepository->getOne($category_id)) {
                    $this->articleCategoryRepository->incrementFieldByIds([$category_id], 'category_count');
                }

                //todo 批量更新 标签 内容数目
                $article_tags = $article_data['article_tags'];
                if ($article_tags) {
                    $tag_ids = explode(',', $article_tags);
                    $this->articleTagRepository->incrementFieldByIds($tag_ids, 'tag_count');
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {

            DB::rollBack();
            throw new ErrorException(__('文章添加失败: ') . $e->getMessage());
        }

    }


    /**
     * 修改文章
     * @param $article_id
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function editArticleBase($article_id, $request)
    {
        try {
            DB::beginTransaction();

            $article_row = $this->repository->getOne($article_id);
            if (empty($article_row)) {
                throw new ErrorException(__("文章不存在"));
            }

            //todo 修改文章数据
            $article_data = $request;
            $article_flag = $this->repository->edit($article_id, $article_data);

            if ($article_flag) {
                $old_category_id = $article_row['category_id'];
                $category_id = $article_data['category_id'] ?? 0;

                //todo 增加分类内容数目
                if ($category_id && $this->articleCategoryRepository->getOne($category_id) && $old_category_id != $category_id) {
                    $flag_row[] = $this->articleCategoryRepository->incrementFieldByIds([$category_id], 'category_count');
                }

                //todo 变更原有分类内容数目
                if ($old_category_id && $old_category_id != $category_id) {
                    $flag_row[] = $this->articleCategoryRepository->decrementFieldByIds([$old_category_id], 'category_count');
                }

                //todo 批量更新 标签 内容数目
                $article_tags = $article_data['article_tags'] ?? '';
                if ($article_tags) {
                    $old_tag_ids = explode(',', $article_row['article_tags']);
                    $tag_ids = explode(',', $article_tags);

                    //元素在 $old_tag_ids 不在 $tag_ids 减少tag内容数
                    $not_in_tag_ids = array_diff($old_tag_ids, $tag_ids);
                    if ($old_tag_ids && !empty($not_in_tag_ids)) {
                        $flag_row[] = $this->articleTagRepository->decrementFieldByIds($not_in_tag_ids, 'tag_count');
                    }

                    // Find the elements in $tag_ids that are not in $old_tag_ids
                    $not_in_old_tag_ids = array_diff($tag_ids, $old_tag_ids);
                    if (!empty($not_in_old_tag_ids) && $tag_ids) {
                        $flag_row[] = $this->articleTagRepository->incrementFieldByIds($not_in_old_tag_ids, 'tag_count');
                    }
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {

            DB::rollBack();
            throw new ErrorException(__('文章修改失败: ') . $e->getMessage());
        }

    }


    /**
     * 删除文章
     * @param $article_id
     * @return bool
     * @throws ErrorException
     */
    public function removeArticleBase($article_id)
    {
        try {

            DB::beginTransaction();

            $this->removeArticleRelevance($article_id);

            //todo 执行删除操作
            $this->repository->remove($article_id);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('文章删除失败: ') . $e->getMessage());
        }
    }


    /**
     * 删除文章相关数据
     * @param $article_id
     * @return boolean
     * @throws ErrorException
     */
    public function removeArticleRelevance($article_id)
    {
        $article_row = $this->repository->getOne($article_id);
        if (empty($article_row)) {
            throw new ErrorException(__("文章不存在"));
        }

        $flag_row = [];

        //todo 1、减少 分类内容数目
        if ($article_row['category_id']) {
            $flag_row[] = $this->articleCategoryRepository->decrementFieldByIds([$article_row['category_id']], 'category_count');
        }

        //todo 2、减少 标签 内容数目
        $article_tag_ids = explode(',', $article_row['article_tags']);
        if (!empty($article_tag_ids)) {
            $flag_row[] = $this->articleTagRepository->decrementFieldByIds($article_tag_ids, 'tag_count');
        }

        //todo 3、删除 该文章下的所有评论
        $comment_ids = $this->articleCommentRepository->findKey(['article_id' => $article_id]);
        if ($comment_ids) {
            $flag_row[] = $this->articleCommentRepository->remove($comment_ids);
        }

        return is_ok($flag_row);
    }


    public function removeBatch($request)
    {
        $article_id_str = $request['article_id'];
        $article_ids = explode(',', $article_id_str);

        if ($article_ids) {
            try {

                DB::beginTransaction();

                foreach ($article_ids as $article_id) {
                    $remove_flag = $this->removeArticleRelevance($article_id);
                    if (!$remove_flag) {
                        throw new ErrorException(__('文章关联信息删除失败'));
                    }

                    //todo 执行删除操作
                    $article_flag = $this->repository->remove($article_id);
                    if (!$article_flag) {
                        throw new ErrorException(__('文章删除失败'));
                    }
                }

                DB::commit();
                return true;

            } catch (\Exception $e) {
                DB::rollBack();
                throw new ErrorException(__('文章删除失败: ') . $e->getMessage());
            }

        }

        return true;
    }

}
