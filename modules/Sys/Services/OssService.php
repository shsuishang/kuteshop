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

use App\Exceptions\ErrorException;
use Illuminate\Support\Str;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use OSS\OssClient as AliyunOssClient;

/**
 * Class OssService.
 *
 * @package Modules\Sys\Services
 */
class OssService
{
    private $configBaseRepository;

    public function __construct(ConfigBaseRepository $configBaseRepository)
    {
        $this->configBaseRepository = $configBaseRepository;
    }


    /**
     * 上传文件到阿里云OSS
     * @param $file
     * @return false
     * @throws \OSS\Core\OssException
     */
    public function ossUploadObject($file)
    {
        // 创建唯一的文件名，包括 UUID 和原始文件的扩展名
        $extension = $file->getClientOriginalExtension();
        $unique_name = Str::uuid() . '.' . $extension;

        $access_key_id = $this->configBaseRepository->getConfig('aliyun_access_key_id');
        $access_key_secret = $this->configBaseRepository->getConfig('aliyun_access_key_secret');
        $endpoint = $this->configBaseRepository->getConfig('aliyun_endpoint');

        $ossClient = new AliyunOssClient(
            $access_key_id,
            $access_key_secret,
            $endpoint
        );

        $bucket = $this->configBaseRepository->getConfig('aliyun_bucket');
        $default_dir = $this->configBaseRepository->getConfig('aliyun_default_dir');

        // 上传文件到阿里云OSS存储桶
        $res = $ossClient->uploadFile(
            $bucket,
            $default_dir . '/' . $unique_name,
            $file->getRealPath()
        );

        if ($res && $res['info']['url']) {
            return $res['info'];
        } else {
            throw new ErrorException(__('OSS上传失败，请重新上传'));
        }

    }

}
