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


namespace Modules\Sys\Http\Controllers;

use App\Exceptions\ErrorException;
use App\Support\Image;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;


class ImageController extends BaseController
{
    private $rootPath;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->rootPath = storage_path();
    }

    /**
     * 设置 Header 头
     * @param $type
     * @return void
     */
    public function setHeader($type)
    {
        switch ($type){
            case 'jpg':
            case 'jpeg':
                return ['Content-Type' => 'image/jpeg'];
            case 'gif':
                return ['Content-Type' => 'image/gif'];
            default:
                return ['Content-Type' => 'image/png'];
        }

    }


    /**
     * 获取图片
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws ErrorException
     */
    public function get(Request $request)
    {
        $file = $request->path();
        $ext = strtolower(pathinfo($file,PATHINFO_EXTENSION)); //extension

        if(!in_array($ext,array('jpg','jpeg','gif','png')))
        {
            throw new ErrorException('非图片类型');
        }

        $file_row = explode('!', $file);
        $original_path = $this->rootPath.ltrim($file_row[0],'image.php'); //原图路径

        //缩略图形式
        if (isset($file_row[1]))
        {
            $thumb_img = str_replace('uploads','thumb',$original_path).'!'.$file_row[1]; //缩略图路径
            if(file_exists($thumb_img))
            {
                $img = file_get_contents($thumb_img); //已经存在 直接返回
            }else{

                //todo 判断原图文件是否存在
                if (!is_file($original_path))
                {
                    throw new ErrorException('不存在的图像文件');
                }

                //todo 获取缩略图的宽高
                $width  = 100;
                $height = 100;
                $size_rows = explode('.',$file_row[1]); //数组：['100x100','jpg']
                if(!empty($size_rows))
                {
                    $size_row = explode('x',$size_rows[0]);
                    $width    = isset($size_row[0]) ? $size_row[0] : 100;
                    $height   = isset($size_row[1]) ? $size_row[1] : 100;
                }

                //todo 生成缩略图
                $image = new Image($original_path);
                $img = $image->thumb($width,$height,$thumb_img);

            }

        }else{
            //todo 判断文件是否存在
            if (!is_file($original_path))
            {
                throw new ErrorException('不存在的图像文件');
            }

            //todo 获取原图
            $img = file_get_contents($original_path);

        }

        $header = $this->setHeader($ext);
        return Response()->make($img,200,$header);
    }


}
