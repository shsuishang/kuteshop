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
use App\Support\BindConnectCode;
use App\Support\ConstantRole;
use App\Support\PhoneNumberUtils;
use App\Support\StateCode;
use Illuminate\Support\Facades\Hash;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserBindConnectRepository;
use Modules\Account\Repositories\Contracts\UserDistributionRepository;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Contracts\UserLoginRepository;
use Modules\Account\Repositories\Contracts\UserRepository;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Repositories\Contracts\UserAdminRepository;
use Modules\Pay\Services\UserResourceService;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;

class LoginService extends BaseService
{
    private $userRepository;
    private $userBindConnectRepository;
    private $userInfoRepository;
    private $userLoginRepository;
    private $configBaseRepository;
    private $userAdminRepository;
    private $userDistributionRepository;

    private $userResourceService;

    public function __construct(
        UserRepository             $userRepository,
        UserBindConnectRepository  $userBindConnectRepository,
        UserInfoRepository         $userInfoRepository,
        UserLoginRepository        $userLoginRepository,
        ConfigBaseRepository       $configBaseRepository,
        UserAdminRepository        $userAdminRepository,
        UserDistributionRepository $userDistributionRepository,

        UserResourceService        $userResourceService
    )
    {
        $this->userRepository = $userRepository;
        $this->userBindConnectRepository = $userBindConnectRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->userLoginRepository = $userLoginRepository;
        $this->configBaseRepository = $configBaseRepository;
        $this->userAdminRepository = $userAdminRepository;
        $this->userDistributionRepository = $userDistributionRepository;

        $this->userResourceService = $userResourceService;
    }


    //登录
    public function login($request)
    {
        $user_name = $request->input('user_account', null);

        //根据用户名查询账户信息
        $user = $this->userRepository->getUser(['user_account' => $user_name]);
        if (!$user) {
            throw new ErrorException(__('用户不存在'));
        }

        $user_base = $user->toArray();
        $user_info = $this->userInfoRepository->getOne($user_base['user_id']);
        if (empty($user_info) || $user_info['user_state'] == 0) {
            throw new ErrorException(__('您的账号已被禁用,请联系管理员'));
        }

        //todo 解密前端传入密码
        if ($request->boolean('encrypt', false)) {
            $private_key = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap(env('PRIVATE_KEY'), 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";

            // 使用 OpenSSL 扩展进行 RSA 解密
            openssl_private_decrypt(base64_decode($request['password']), $decrypted_data, $private_key);

            //解密后的原始数据
            $request['password'] = $decrypted_data;
        }

        $inputPassword = md5($request['password'] . $user['user_salt']);
        if ($inputPassword !== $user['user_password']) {
            throw new ErrorException(__('用户名或者密码不正确'));
        }

        $token = auth()->login($user);

        return ['token' => $token, 'user_id' => $user['user_id']];
    }


    /**
     * @param $request
     * @return mixed
     * @throws \Random\RandomException
     */
    public function register($request)
    {
        $bind_type = $request->input('bind_type', 3);
        $user_intl = $request->input('user_intl', '');
        $user_mobile = $request->input('user_mobile', '');
        $user_email = $request->input('user_email', '');

        // 账号，手机，邮箱注册方式走校验
        $user_account = $request->input('user_account', '');
        $user_base = $this->userRepository->findOne(['user_account' => $user_account]);
        if (!empty($user_base)) {
            throw new ErrorException(__("用户已经存在,请更换用户名"));
        }

        switch ($bind_type) {
            case BindConnectCode::MOBILE:
                if (!PhoneNumberUtils::isValidNumber($user_account)) {
                    throw new ErrorException(__("请输入正确的手机号！"));
                }

                $mobile_info = $this->getMobileCountry($user_account);
                $user_mobile = $mobile_info['mobile'];
                $user_intl = $mobile_info['country_code'];

                $bind_connect = $this->userBindConnectRepository->getOne($user_account);
                if (!empty($bind_connect) && $bind_connect['bind_active']) {
                    throw new ErrorException(__("手机号已经绑定过，不可以使用此手机号注册"));
                }
                break;

            case BindConnectCode::EMAIL:
                $user_email = $request['user_email'] = $user_account;
                $bind_connect = $this->userBindConnectRepository->getOne($user_account);
                if ($bind_connect !== null && $bind_connect['bind_active']) {
                    throw new ErrorException(__("Email已经绑定过，不可以使用此Email注册"));
                }
                break;

            case BindConnectCode::ACCOUNT:
                if (strpos($user_account, '+') !== false) {
                    throw new ErrorException(__("用户账号不可以包含特殊字符串！"));
                }
                break;

            case BindConnectCode::WEIXIN:
            case BindConnectCode::WEIXIN_XCX:
                // 微信相关注册处理逻辑
                break;
        }
        DB::beginTransaction();

        try {
            $user_password = $request->input('password', '');
            if ($user_password == '') {
                $user_password = 'kuteshop@2018';
            }
            $user_id = $this->userRepository->insertUser([
                'name' => $user_account,
                'password' => $user_password
            ]);

            //todo 创建用户登录信息
            $ip = $request->ip();
            $user_login_row = [
                'user_id' => $user_id,
                'user_reg_ip' => $ip,
                'user_reg_date' => getCurDate(),
                'user_reg_time' => getTime(),
                'user_lastlogin_ip' => $ip,
                'user_lastlogin_time' => getTime()
            ];
            $this->userLoginRepository->add($user_login_row);

            // 创建用户信息
            $user_nickname = $request->input('user_nickname', '');
            if ($user_nickname == '') {
                $user_nickname = $user_account;
            }
            $user_avatar = $request->input('user_avatar', '');
            if ($user_avatar == '') {
                $user_avatar = $this->configBaseRepository->getConfig('user_no_avatar', '');
            }
            $user_info_row = [
                'user_id' => $user_id,
                'user_account' => $user_account,
                'user_nickname' => $user_nickname,
                'user_mobile' => $user_mobile,
                'user_intl' => $user_intl,
                'user_gender' => 0,
                'user_email' => $user_email,
                'user_birthday' => $request->input('user_birthday', '1971-01-01'),
                'user_level_id' => $request->input('user_level_id', 1001),
                'user_avatar' => $user_avatar
            ];
            $this->userInfoRepository->add($user_info_row);

            // 手机注册 || 邮箱注册
            if ($bind_type === BindConnectCode::MOBILE || $bind_type === BindConnectCode::EMAIL) {
                $this->checkBind($user_account, $bind_type, $user_id);
            }

            // 初始化用户资源
            if (!$this->userResourceService->initUserPoints($user_id)) {
                throw new ErrorException(__('初始化用户资源失败'));
            }

            if (!$this->userResourceService->initUserExperience($user_id)) {
                throw new ErrorException(__('初始化用户资源失败'));
            }

            // 检查管理员身份
            $role_id = $request->input('role_id', 0);
            if ($role_id != ConstantRole::ROLE_USER) {

                $user_admin_row = [
                    'user_id' => $user_id,
                    'role_id' => $role_id
                ];

                if ($role_id == ConstantRole::ROLE_ADMIN) {
                    $user_admin_row['user_role_id'] = 1001;
                } else if ($role_id == ConstantRole::ROLE_CHAIN) {
                    $user_admin_row['user_role_id'] = 1003;
                    $user_admin_row['chain_id'] = $request->input('chain_id', 0);
                } else if ($role_id == ConstantRole::ROLE_SELLER) {
                    $user_admin_row['user_role_id'] = 1003;
                    $user_admin_row['store_id'] = $request->input('store_id', 0);
                }

                $this->userAdminRepository->add($user_admin_row);
            }

            // 记录用户来源
            $user_parent_id = 0;
            $source_user_id = $this->getSourceUserId($request);
            if ($source_user_id) {
                $user_parent_id = $source_user_id;
            }

            if ($request->has('user_parent_id') && $request->input('user_parent_id', 0)) {
                $user_parent_id = $request->input('user_parent_id');
            }

            // 分销用户来源
            $source_ucc_code = $request->input('source_ucc_code', '');
            if ($user_parent_id) {
                $parent_user = $this->userRepository->getOne($user_parent_id);
                if (!empty($parent_user)) {
                    $activity_id = $request->input('activity_id', 0);
                    $this->addSourceUserId($user_id, $user_parent_id, $activity_id, $source_ucc_code);
                }
            }

            // 邀请码
            /*if ($source_ucc_code) {
                $this->addChannelSourceUserId($user_id, $source_ucc_code);
            }*/

            // 发送欢迎信息
            /*$message_id = "registration-of-welcome-information";
            $args = [
                'user_account' => $user_nickname,
                'register_time' => getDateTime()
            ];
            $this->messageService->sendNoticeMsg($user_id, $message_id, $args);*/

            if ($user_id) {
                $this->bindMobile($user_id, $user_intl, $user_mobile);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('操作失败: ') . $e->getMessage());
        }

        return $user_id;
    }

    public function getSourceUserId($request)
    {
        $source_user_id = $request->input('source_user_id', 0);

        return $source_user_id;
    }


    /**
     * 检测获取用户绑定信息
     * @param $bind_id
     * @return array
     * @throws ErrorException
     */
    public function checkBindInfo($bind_id)
    {
        // 检查绑定信息
        $user_bind_connect = $this->userBindConnectRepository->getOne($bind_id);
        if (empty($user_bind_connect)) {
            throw new ErrorException(__("未找到绑定信息！"));
        }

        return $user_bind_connect;
    }


    /**
     * 旧密码检测
     * @param $user_id
     * @param $password
     * @return true
     * @throws ErrorException
     */
    public function checkUserPassword($user_id, $password)
    {
        $user = $this->userRepository->getOne($user_id);
        $hash_password = Hash::make($password . $user['user_salt']);
        if ($user['user_password'] !== $hash_password) {
            throw new ErrorException(__("旧密码不正确"));
        }

        return true;
    }


    /**
     * 重置登录密码
     */
    public function doResetPasswd($user_id, $password)
    {
        $result = $this->userRepository->setUserPassword($user_id, $password);

        return $result;
    }


    /**
     * 获取手机号信息
     * @param $mobile
     * @return array
     * @throws ErrorException
     */
    public function getMobileCountry($mobile)
    {
        $phoneModelWithCountry = PhoneNumberUtils::getPhoneModelWithCountry($mobile);
        if ($phoneModelWithCountry === null) {
            throw new ErrorException(__("手机号码解析失败！"));
        }

        return [
            'country_code' => '+' . $phoneModelWithCountry->countryCode,
            'mobile' => $phoneModelWithCountry->nationalNumber
        ];

    }


    /**
     * 绑定手机号
     * @param $user_id
     * @param $user_intl
     * @param $mobile
     * @return array
     * @throws ErrorException
     */
    public function bindMobile($user_id, $user_intl, $mobile)
    {
        DB::beginTransaction();
        try {
            $result = $this->doBindMobile($user_id, $user_intl, $mobile);
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('绑定失败: ') . $e->getMessage());
        }
    }


    /**
     * 执行绑定手机操作
     * @param $user_id
     * @param $user_intl
     * @param $mobile
     * @return array
     * @throws ErrorException
     */
    public function doBindMobile($user_id, $user_intl, $mobile)
    {
        $bind_id = sprintf("%s%d", $user_intl, $mobile);
        $bind_connect_row = $this->userBindConnectRepository->getOne($bind_id);

        if (!empty($bind_connect_row)) {
            //todo 判断是否存在绑定用户
            if ($bind_connect_row['user_id']) {
                if ($bind_connect_row['user_id'] != $user_id) {
                    throw new ErrorException(__('该手机号已被绑定！'));
                }
            } else {
                $this->userBindConnectRepository->edit($bind_id, ['user_id' => $user_id]);
            }

            //更新用户手机信息
            $user_info = $this->userInfoRepository->getOne($user_id);
            if ($user_info['user_intl'] == '' || $user_info['user_mobile'] == '') {
                $this->userInfoRepository->edit($user_id, [
                    'user_intl' => $user_intl,
                    'user_mobile' => $mobile
                ]);
            }

        } else {

            $user_bind_mobile = $this->userBindConnectRepository->findOne([
                'user_id' => $user_id,
                'bind_type' => BindConnectCode::MOBILE
            ]);
            if (!empty($user_bind_mobile)) {
                $this->userBindConnectRepository->remove($user_bind_mobile['bind_id']);
            }

            $this->userBindConnectRepository->add([
                'bind_id' => $bind_id,
                'bind_type' => BindConnectCode::MOBILE,
                'user_id' => $user_id,
                'bind_active' => true,
                'bind_openid' => $bind_id
            ]);

            $this->userInfoRepository->edit($user_id, [
                'user_intl' => $user_intl,
                'user_mobile' => $mobile
            ]);
        }

        return [
            'user_id' => $user_id,
            'bind_id' => $bind_id
        ];
    }


    /**
     * 解绑手机号
     * @param $user_id
     * @param $user_intl
     * @param $mobile
     * @throws ErrorException
     */
    public function unBindMobile($user_id, $user_intl, $mobile)
    {
        DB::beginTransaction();
        try {
            $bind_id = sprintf("%s%d", $user_intl, $mobile);
            $bind_connect_row = $this->userBindConnectRepository->getOne($bind_id);

            if (!empty($bind_connect_row) && $bind_connect_row['user_id'] == $user_id) {
                $this->userBindConnectRepository->remove($bind_id);
                $this->userInfoRepository->edit($user_id, [
                    'user_intl' => '',
                    'user_mobile' => ''
                ]);
            } else {
                throw new ErrorException(__('解绑信息有误，无权限操作'));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException(__('解绑失败: ') . $e->getMessage());
        }
    }


    /**
     * 实名认证信息
     * @param $user_id
     * @param $data
     * @return mixed
     * @throws ErrorException
     */
    public function saveCertificate($user_id, $data)
    {
        $user_is_authentication = StateCode::USER_CERTIFICATION_VERIFY;
        $user_info = $this->userInfoRepository->getOne($user_id);

        if (empty($user_info)) {
            throw new ErrorException(__('用户信息不存在！'));
        }

        if ($user_info['user_is_authentication'] == StateCode::USER_CERTIFICATION_VERIFY || $user_info['user_is_authentication'] == StateCode::USER_CERTIFICATION_YES) {
            throw new ErrorException(__('已提交，请勿重复提交！'));
        }

        try {
            $this->userInfoRepository->edit($user_id, [
                'user_is_authentication' => $user_is_authentication,
                'user_realname' => $data['user_realname'],
                'user_idcard' => $data['user_idcard'],
                'user_idcard_images' => $data['user_idcard_images']
            ]);
        } catch (\Exception $e) {
            throw new ErrorException(__('实名认证信息提交失败: ') . $e->getMessage());
        }

        return $data;
    }

    /**
     * 绑定信息
     * @param $bind_id
     * @param $bind_type
     * @param $user_id
     * @param $bind_info
     * @return true
     * @throws ErrorException
     */
    public function checkBind($bind_id, $bind_type, $user_id, $bind_info = [])
    {
        // 获取绑定信息
        $bind_row = $this->userBindConnectRepository->getOne($bind_id);

        if (!empty($bind_row) && $bind_row['user_id']) {
            // 验证通过, 登录成功
            $bind_user_id = $bind_row['user_id'];

            if ($user_id && $user_id === $bind_user_id) {
                throw new ErrorException(__('非法请求,已经登录用户不应该访问到此页面-重复绑定'));
            } elseif (!$user_id && $user_id === $bind_user_id) {
                throw new ErrorException(__('非法请求,错误绑定数据'));
            }
        } elseif (!empty($bind_row) && !$bind_row['user_id']) {
            // 更新绑定信息
            if (!$this->userBindConnectRepository->edit($bind_id, ['user_id' => $user_id])) {
                throw new ErrorException(__('绑定信息有误'));
            }
        } elseif ($bind_row === null) {
            //添加绑定信息
            $bind_info['bind_id'] = $bind_id;
            $bind_info['bind_type'] = $bind_type;
            $bind_info['user_id'] = $user_id;
            $bind_info['bind_active'] = true;

            if (!$this->userBindConnectRepository->add($bind_info)) {
                throw new ErrorException(__('添加绑定信息失败'));
            }
        }

        return true;
    }


    public function addSourceUserId($user_id, $user_parent_id, $activity_id, $source_ucc_code)
    {
        return true;
    }


    public function addChannelSourceUserId($user_id, $source_ucc_code)
    {
        return true;
    }


    /**
     * 手机验证码登录
     * @param $verify_key
     * @return array
     * @throws ErrorException
     */
    public function doSmsLogin($verify_key)
    {
        $mobile_row = $this->getMobileCountry($verify_key);
        $bind_id = sprintf("%s%d", $mobile_row['country_code'], $mobile_row['mobile']);
        $bind_row = $this->checkBindInfo($bind_id);
        $user_id = $bind_row['user_id'];
        $user = $this->userRepository->getUser(['user_id' => $user_id]);
        if (!$user) {
            throw new ErrorException(__('用户不存在'));
        }
        $token = auth()->login($user);

        return ['token' => $token, 'user_id' => $user['user_id']];
    }


    /**
     * 经验值规则
     * @return array
     */
    public function listsExpRule()
    {
        $data = [
            'exp_reg' => $this->configBaseRepository->getConfig('exp_reg'),
            'exp_login' => $this->configBaseRepository->getConfig('exp_login'),
            'exp_consume_max' => $this->configBaseRepository->getConfig('exp_consume_max'),
            'exp_consume_rate' => $this->configBaseRepository->getConfig('exp_consume_rate'),
            'exp_evaluate_good' => $this->configBaseRepository->getConfig('exp_evaluate_good')
        ];

        return $data;
    }

}
