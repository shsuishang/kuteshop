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


/*
 * (c) suishang
 * Uniformly return data
 */

declare(strict_types=1);

namespace App\Support;

use Symfony\Component\HttpFoundation\Response;

/**
 * 设置控制器返回数据格式
 */
class Respond
{
    // 默认200
    private static $httpCode = Response::HTTP_OK;  // http状态码


    /**
     * 设置返回码,连贯操作
     *
     * @param int $httpCode
     *
     * @return Respond
     */
    public static function setHttpCode(int $httpCode)
    {
        static::$httpCode = $httpCode;
        return static::class;
    }


    /**
     * 正确响应格式
     * @param $data
     * @param string $msg
     * @param int $code
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data, string $msg = '操作成功', int $code = Response::HTTP_OK, $status = 200)
    {
        $data = (is_object($data) ? $data->toArray() : $data);

        //转换分页数据
        if (isset($data['data'])) {
            $data = Respond::transformPageData($data);
        }

        return response()->json([
            'status' => $status,
            'code' => $code,
            'msg' => $msg,
            'data' => $data
        ]);
    }


    /**
     * 错误响应格式
     * @param $msg
     * @param int $code
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($msg = 'ERROR', int $code = Response::HTTP_BAD_REQUEST, $status = 250)
    {
        return response()->json([
            'status' => $status,
            'code' => $code,
            'msg' => $msg
        ]);
    }


    /**
     * 不带数据，只返回成功状态码
     * @param string $msg
     * @param int $code
     * @param $status
     * @return \Illuminate\Http\JsonResponse
     */
    public static function ok(string $msg = 'OK', int $code = Response::HTTP_OK, $status = 200)
    {
        return response()->json([
            'status' => $status,
            'code' => $code,
            'msg' => $msg,
        ]);
    }


    /**
     * @param $data
     * @return array
     */
    public static function transformPageData($data)
    {
        $return_data = $data;
        $return_data['items'] = $data['data']; //返回数据
        $return_data['page'] = $data['current_page']; //当前页数
        $return_data['records'] = $data['total']; //总条数
        $return_data['size'] = $data['limit']; //显示行数
        $return_data['total'] = $data['last_page']; //总页数

        unset($return_data['data']);
        unset($return_data['current_page']);
        unset($return_data['limit']);
        unset($return_data['last_page']);
        if (isset($return_data['first_page_url'])) {
            unset($return_data['first_page_url']);
            unset($return_data['from']);
            unset($return_data['last_page_url']);
            unset($return_data['links']);
            unset($return_data['next_page_url']);
            unset($return_data['path']);
            unset($return_data['per_page']);
            unset($return_data['prev_page_url']);
            unset($return_data['to']);
        }

        return $return_data;
    }
}
