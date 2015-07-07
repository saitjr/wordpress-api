<?php
namespace Api\Controller;
use Think\Controller;

/**
* 客户端接口基类
*/
class ApiController extends Controller
{
	/**
	 * 返回客户端统一格式
	 * @param  任意格式 	$data    	返回的数据
	 * @param  字符串   	$msg     	请求返回的消息
	 * @param  整型    	$status 	请求返回的状态码  
	 */
	public function jsonReturn($data, $msg, $status) {

		// 如果data是空，则处理为空字典
		if (!$data) {
			
			$data =  (object)null;
		}

		$returnData['data'] = $data;
		$returnData['msg'] = $msg;
		$returnData['status'] = $status;

		$this->ajaxReturn($returnData, 'JSON');
	}
}