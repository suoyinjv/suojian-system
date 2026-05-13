<?php

namespace Home\Controller;

/**
 * 机构注册
 * @author   Devil
 * @blog     http://gong.gg/
 * @version  1.0.0
 * @datetime 2024-01-01T00:00:00+0800
 */
class RegisterController extends CommonController
{
	/**
	 * [_initialize 前置操作-继承公共前置方法]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  1.0.0
	 * @datetime 2024-01-01T00:00:00+0800
	 */
	public function _initialize()
	{
		// 调用父类前置方法
		parent::_initialize();
	}

	/**
	 * [Index 机构注册页面]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  1.0.0
	 * @datetime 2024-01-01T00:00:00+0800
	 */
	public function Index()
	{
		// 已登录则跳转
		if(!empty($_SESSION['admin']))
		{
			$this->redirect('Admin/Index/Index');
		}
		$this->display('Index');
	}

	/**
	 * [Register 机构注册处理]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  1.0.0
	 * @datetime 2024-01-01T00:00:00+0800
	 */
	public function Register()
	{
		// 是否ajax请求
		if(!IS_AJAX)
		{
			$this->error(L('common_unauthorized_access'));
		}

		// 参数校验
		$name = I('name', '');
		$domain = I('domain', '');
		$username = I('username', '');
		$mobile = I('mobile', '');
		$pwd = trim(I('pwd', ''));

		// 机构名称校验
		if(empty($name))
		{
			$this->ajaxReturn('机构名称不能为空', -1);
		}

		// 域名/标识符校验
		if(empty($domain))
		{
			$this->ajaxReturn('域名/标识符不能为空', -2);
		}
		if(!preg_match('/^[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9]$/', $domain))
		{
			$this->ajaxReturn('域名/标识符格式不正确，只允许字母、数字和连字符', -3);
		}
		if(strlen($domain) < 2 || strlen($domain) > 50)
		{
			$this->ajaxReturn('域名/标识符长度需在2-50个字符之间', -4);
		}

		// 用户名校验
		if(empty($username))
		{
			$this->ajaxReturn('联系人不能为空', -5);
		}

		// 手机号校验
		if(!CheckMobile($mobile))
		{
			$this->ajaxReturn(L('common_mobile_format_error'), -6);
		}

		// 密码校验
		if(!CheckLoginPwd($pwd))
		{
			$this->ajaxReturn(L('user_reg_pwd_format'), -7);
		}

		// 验证域名/标识符唯一性
		$domain_exist = M('Campus')->where(array('domain'=>$domain))->count();
		if($domain_exist > 0)
		{
			$this->ajaxReturn('该域名/标识符已被注册', -8);
		}

		// 启动事务
		$model = M();
		$model->startTrans();
		try {
			// 1. 创建校区
			$campus_data = array(
				'name'		=> $name,
				'domain'	=> $domain,
				'site_name'	=> $name,
				'status'	=> 1,
				'add_time'	=> time(),
			);
			$campus_id = M('Campus')->add($campus_data);
			if($campus_id === false)
			{
				throw new \Exception('校区创建失败');
			}

			// 2. 创建管理员账号
			$salt = GetNumberCode(6);
			$admin_data = array(
				'username'		=> $username,
				'login_pwd'		=> LoginPwdEncryption($pwd, $salt),
				'login_salt'	=> $salt,
				'mobile'		=> $mobile,
				'campus_id'		=> $campus_id,
				'is_super'		=> 0,
				'add_time'		=> time(),
			);
			$admin_id = M('Admin')->add($admin_data);
			if($admin_id === false)
			{
				throw new \Exception('管理员创建失败');
			}

			// 提交事务
			$model->commit();

			// 3. 自动登录
			$_SESSION['admin'] = array(
				'id'			=> $admin_id,
				'campus_id'		=> $campus_id,
				'username'		=> $username,
				'mobile'		=> $mobile,
				'is_super'		=> 0,
			);

			$this->ajaxReturn('注册成功');

		} catch(\Exception $e) {
			$model->rollback();
			$this->ajaxReturn($e->getMessage(), -100);
		}
	}

	/**
	 * [CheckDomain 验证域名/标识符唯一性]
	 * @author   Devil
	 * @blog     http://gong.gg/
	 * @version  1.0.0
	 * @datetime 2024-01-01T00:00:00+0800
	 */
	public function CheckDomain()
	{
		if(!IS_AJAX)
		{
			$this->error(L('common_unauthorized_access'));
		}

		$domain = I('domain', '');
		if(empty($domain))
		{
			$this->ajaxReturn('域名/标识符不能为空', -1);
		}

		$count = M('Campus')->where(array('domain'=>$domain))->count();
		if($count > 0)
		{
			$this->ajaxReturn('该域名/标识符已被注册', -2);
		}
		$this->ajaxReturn('可以使用');
	}
}
?>
