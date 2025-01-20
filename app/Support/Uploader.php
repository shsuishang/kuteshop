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


namespace App\Support;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class Uploader
{

    protected $file;
    private   $error = '';      //上传错误信息
    private   $fullPath = '';   //绝对地址
    private   $fileSize = '';   //文件大小

    private   $config = array(
        'maxSize'  => 3*1024,//上传的文件大小限制(0-不做限制)
        'exts'     => array('jpg','jpeg','gif','png','doc','docx','xls','xlsx','ppt','pptx','pdf','rar','zip','mp4'),//允许上传的文件后缀
        'subName'  => '',          //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => '/uploads', //保存根路径
        'savePath' => '',          //保存路径
        'thumb'    => array(),     //是裁剪压缩比例
    );

    public function __construct($config = array())
    {
        $this->config = array_merge($this->config,$config);

        if(!empty($this->config['exts'])){

            if(is_string($this->exts)){
                $this->config['exts'] = explode(',',$this->exts);
            }

            $this->config['exts'] = array_map('strtolower', $this->exts);
        }

        $this->config['subName'] = $this->subName ? ltrim($this->subName,'/'):'/'.date('Ymd');

        $this->fullPath = storage_path().$this->config['rootPath'];
    }

    public function __get($name){
        return $this->config[$name];
    }

    public function __set($name,$value){
        if(isset($this->config[$name])){
            $this->config[$name] = $value;
        }
    }

    public function __isset($name){
        return isset($this->config[$name]);
    }

    public function getError()
    {
        return $this->error;
    }

    public function  upload($file)
    {
        if(empty($file)){
            $this->error = '没有上传的文件';
            return false;
        }

        //检验路径
        if(!$this->checkRootPath($this->fullPath)){
            $this->error = $this->$this->getError();
            return false;
        }

        //检验路径
        $fileSavePath = $this->fullPath.$this->savePath.$this->subName;
        if(!$this->checkSavePath($fileSavePath)){
            $this->error=$this->getError();
            return false;
        }

        $files = array();
        if(!is_array($file)){
            //如果不是数组转成数组
            $files[] = $file;
        }else{
            $files = $file;
        }

        $info = array();
        foreach($files as $key=>$f)
        {
            $this->file = $f;
            $f->ext = strtolower($f->getClientOriginalExtension());
            /*文件上传检查*/
            if(!$this->check($f))
            {
                continue;
            }

            //替换随机字符串
            $randNum = rand(1, 10000000000) . rand(1, 10000000000);
            $fileName = time().substr($randNum,0,6).'.'.$f->ext;

            /* 保存文件 并记录保存成功的文件 */
            if($this->file->move($fileSavePath,$fileName)){
                $url_prefix = env('APP_URL') . '/image.php';
                $url_path   = $this->rootPath.$this->savePath.$this->subName.'/'.$fileName;
                $info[] = [
                    "original"   => $f->getClientOriginalName(),
                    "size"       => $this->fileSize,
                    "state"      => 'SUCCESS',
                    "title"      => $fileName,
                    "type"       => $f->ext,
                    "url"        => $url_prefix . $url_path,
                    "url_path"   => $url_path,
                    "url_prefix" => $url_prefix.'/uploads/'
                ];
            }
        }

        return is_array($info) ? $info : false;
    }


    public function checkRootPath($rootPath)
    {
        if(!(is_dir($rootPath)) && is_writable($rootPath)){
            $this->error = '上传目录不存在';
            return false;
        }

        return true;
    }


    /**
     * 检测上传目录
     * @param $savePath
     * @return bool
     */
    public function checkSavePath($savePath)
    {
        //检测目录
        if( !$this->mkdir($savePath)){
            return false;
        }else{
            /* 检测目录是否可写 */
            if(!is_writable($savePath))
            {
                $this->error = '上传目录不可写！';
                return false;
            }else{
                return true;
            }
        }
    }


    /**
     * 检验上传文件
     * @param $file
     * @return bool
     */
    private function check($file)
    {
        $this->fileSize = $file->getSize();

        /* 检查文件大小 */
        if(!$this->checkSize($this->fileSize))
        {
            $this->error ='上传文件大小不符！';
            return false;
        }
        /* 检查文件后缀 */
        if(!$this->checkExt($file->ext))
        {
            $this->error = '上传文件后缀不允许';
            return false;
        }

        /* 通过检测*/
        return true;
    }


    /**
     * 检测文件大小
     * @param $size
     * @return bool
     */
    private function checkSize($size)
    {
        return !($size > $this->maxSize) || (0 == $this->maxSize);
    }


    /**
     * 检测文件后缀
     * @param $size
     * @return bool
     */
    private function checkExt($ext)
    {
        return empty($this->config['exts']) ? true : in_array(strtolower($ext),$this->exts);
    }


    /**
    * 创建目录
    * @param  string $savePath 要创建的目录
    * @return boolean 创建状态，true-成功，false-失败
    */
    protected function mkdir($savePath)
    {
        if(is_dir($savePath)) {
            return true;
        }

        if(mkdir($savePath,0777,true)){
            return true;
        }else{
            $this->error = "目录创建失败";
            return false;
        }

    }


}
