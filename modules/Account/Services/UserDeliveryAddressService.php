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

use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserDeliveryAddressRepository;
use App\Exceptions\ErrorException;

/**
 * Class UserDeliveryAddressService.
 *
 * @package Modules\Account\Services
 */
class UserDeliveryAddressService extends BaseService
{

    public function __construct(UserDeliveryAddressRepository $userDeliveryAddressRepository)
    {
        $this->repository = $userDeliveryAddressRepository;
    }


    /**
     * 新增或修改
     * @param $request
     * @param $ud_id
     * @return bool
     * @throws ErrorException
     */
    public function saveAddress($request, $ud_id = null)
    {
        $user_id = $request['user_id'];
        $row = $this->repository->findOne(['ud_is_default' => 1, 'user_id' => $user_id]);

        $address_row = [
            'user_id' => $request->get('user_id'), //用户编号
            'ud_name' => $request->input('ud_name', 0), //联系人
            'ud_mobile' => $request->input('ud_mobile', ''), //手机号码
            'ud_intl' => $request->input('ud_intl', '+86'), //国家编码
            'ud_address' => $request->input('ud_address', ''), //详细地址
            'ud_postalcode' => $request->input('ud_postalcode', ''), //邮政编码
            'ud_is_default' => $request->boolean('ud_is_default', 0), //是否默认(BOOL):0-非默认;1-默认
            'ud_province' => $request->input('ud_province', ''), //省份
            'ud_city' => $request->input('ud_city', ''), //市
            'ud_county' => $request->input('ud_county', ''), //县
            'ud_province_id' => $request->input('ud_province_id', 0), //省份ID
            'ud_city_id' => $request->input('ud_city_id', 0), //城市ID
            'ud_county_id' => $request->input('ud_county_id', 0) //县ID
        ];

        DB::beginTransaction();

        if ($ud_id) {
            $result = $this->repository->edit($ud_id, $address_row);
        } else {
            $result = $this->repository->add($address_row);
        }

        //todo 将原默认地址 置为 0
        $default_result = true;
        if ($row && ($ud_id && $row['ud_id'] != $ud_id || !$ud_id && $address_row['ud_is_default'])) {
            $default_result = $this->repository->edit($row['ud_id'], ['ud_is_default' => 0]);
        }

        if ($result && $default_result) {
            DB::commit();
            return true;
        } else {
            DB::rollBack();
            throw new ErrorException(__('操作失败'));
        }

    }


    /**
     * 获取一条用户收货地址
     * @param $ud_id
     * @param $user_id
     * @return array|false|mixed
     */
    public function getOneAddress($ud_id = 0, $user_id = 0)
    {
        $data = [];
        if (!$ud_id) {
            $address_rows = $this->repository->find(['user_id' => $user_id]);
            if (!empty($address_rows)) {
                $data = current($address_rows);
            }
        } else {
            $data = $this->repository->getOne($ud_id);
        }

        return $data;
    }

}
