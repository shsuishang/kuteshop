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


namespace Modules\Sys\Http\Controllers\Front;

use App\Exceptions\ErrorException;
use App\Support\Uploader;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Sys\Services\ConfigBaseService;
use Modules\Sys\Services\MaterialBaseService;
use Modules\Sys\Services\OssService;

class UploadController extends BaseController
{

    public $materialBaseService = null;
    public $ossService = null;
    public $savePath = null;
    public $userId = 10001;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(MaterialBaseService $materialBaseService, OssService $ossService)
    {
        $this->materialBaseService = $materialBaseService;
        $this->ossService = $ossService;

        $this->userId = User::getUserId();
        $this->savePath = '/' . $this->userId;
    }


    /**
     * 上传接口
     * @param Request $request
     * @return void
     * @throws ErrorException
     */
    public function index(Request $request)
    {

        //获取上传的文件信息
        $file = $request->file('upfile');
        $file_name = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

        $request['material_mime_type'] = $this->materialBaseService->getContentType($extension);
        $request['material_name'] = $file_name;
        $request['material_alt'] = $file_name;

        $image_max_filesize = ConfigBaseService::getConfig('upload_max_filesize') * 1024;
        $config = [
            'maxSize' => $image_max_filesize,
            'savePath' => $this->savePath
        ];

        $is_simulate = $request->input('is_simulate', 0);
        $upload_type = ConfigBaseService::getConfig('upload_type');
        if ($upload_type == 1) {
            //阿里云存储
            $result = $this->ossService->ossUploadObject($file);
            $material_url = $result['url'];
            $material_size = $result['size_upload'];
            $result['file_url'] = $result['url'];
            $result['file_name'] = $file_name;
            $result['file_path'] = $material_url;
            $result['file_size'] = $material_size;
            $result['file_type'] = $extension;
            $result['mime_type'] = $request['material_mime_type'];
            $result['type'] = $material_url;
        } else {
            $uploader = new Uploader($config);
            $res = $uploader->upload($file);
            if (empty($res) || !$res) {
                throw new ErrorException('上传失败' . $uploader->getError());
            }

            if ($res[0]['state'] == 'SUCCESS') {
                $result = $res[0];
                $material_url = $result['url'];
                $material_size = $result['size'];
                $result['file_url'] = $result['url'];
                $result['file_name'] = $file_name;
                $result['file_path'] = $material_url;
                $result['file_size'] = $material_size;
                $result['file_type'] = $extension;
                $result['mime_type'] = $request['material_mime_type'];
                $result['type'] = $material_url;
                $request['material_path'] = $result['url_path'];
            }
        }

        $request['material_size'] = $material_size;
        $request['user_id'] = $this->userId;

        if ($material_url) {
            $request['material_url'] = $material_url;
            //todo 添加到素材表
            $this->materialBaseService->addMaterial($request);
        }

        echo json_encode([
            'status' => 200,
            'data' => $result,
            'code' => 200,
            'msg' => ''
        ]);
        die;

    }

}
