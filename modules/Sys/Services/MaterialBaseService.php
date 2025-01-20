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
use Modules\Sys\Repositories\Contracts\MaterialBaseRepository;

/**
 * Class MaterialBaseService.
 *
 * @package Modules\Sys\Services
 */
class MaterialBaseService extends BaseService
{

    public function __construct(MaterialBaseRepository $materialBaseRepository)
    {
        $this->repository = $materialBaseRepository;
    }


    /**
     * @param $request
     * @return true
     * @throws ErrorException
     */
    public function addMaterial($request)
    {
        $add_row = [
            'user_id' => $request->input('user_id', 0),
            'store_id' => $request->input('store_id', 0),
            'gallery_id' => $request->input('gallery_id', 0),
            'material_type' => $request->input('material_type', 'image'),
            'material_name' => $request->input('material_name', ''),
            'material_alt' => $request->input('material_alt', ''),
            'material_url' => $request->input('material_url', ''),
            'material_path' => $request->input('material_path', ''),
            'material_size' => $request->input('material_size', 0),
            'material_mime_type' => $request->input('material_mime_type', 'image/png')
        ];

        $result = $this->repository->add($add_row);

        if ($result) {
            return true;
        } else {
            throw new ErrorException(__('操作失败'));
        }
    }


    /**
     * 获取 material_mime_type
     * @param $extension
     * @return string
     */
    public function getContentType($extension)
    {
        switch ($extension) {
            case ".bmp":
                return "image/bmp";
            case ".gif":
                return "image/gif";
            case ".jpeg":
            case ".jpg":
            case ".png":
                return "image/jpeg";
            case ".html":
                return "text/html";
            case ".txt":
                return "text/plain";
            case ".vsd":
                return "application/vnd.visio";
            case ".ppt":
            case ".pptx":
                return "application/vnd.ms-powerpoint";
            case ".doc":
            case ".docx":
                return "application/msword";
            case ".xml":
                return "text/xml";
            case ".mp4":
                return "video/mp4";
            case ".awf":
                return "application/vnd.adobe.workflow";
            case ".wav":
                return "audio/wav";
            case ".zip":
                return "application/zip";
            case ".pdf":
                return "application/pdf";
            case ".ogg":
                return "application/ogg";
            case ".js":
                return "application/javascript";
            default:
                return "multipart/form-data";
        }
    }

}
