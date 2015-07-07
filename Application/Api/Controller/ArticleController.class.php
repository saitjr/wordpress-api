<?php
namespace Api\Controller;

class ArticleController extends ApiController {
	
	public function articleList() {

		$page = I('page');
		$pageSize = I('pageSize');
		
		if ($pageSize <= 0) {
			
			$pageSize = 15;
		}
		$startRow = $page * $pageSize;

		$articleModel = M('posts');
		//  'ID, post_date, post_title, '
		$articleList = $articleModel->field(array('ID'=>'id', 'post_date' => 'date', 'post_title' => 'title', 'comment_count' => 'commentCount'))->where("post_status = 'publish' AND post_type = 'post'")->limit($startRow, $pageSize)->order('id desc')->select();
		dump($articleList);
	}
}