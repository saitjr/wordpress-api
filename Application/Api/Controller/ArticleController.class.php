<?php
namespace Api\Controller;

/*
	文章接口类
 */
class ArticleController extends ApiController {
	
	/*
		文章列表接口
	 */
	public function articleList() {

		$page = I('page');
		$pageSize = I('pageSize');
		
		if ($pageSize <= 0) {
			
			$pageSize = 15;
		}
		$startRow = $page * $pageSize;
		$articleModel = M('posts');
		$articleList = $articleModel->table('wp_posts wposts, wp_postmeta wpostmeta, wp_users wusers')->field(array('wposts.ID'=>'id', 'post_date' => 'date', 'post_title' => 'title', 'comment_count' => 'commentCount', 'post_author' => 'authorId', 'wusers.user_nicename' => 'authorName', 'wpostmeta.meta_value' => 'views'))->where("wposts.id = wpostmeta.post_id AND wpostmeta.meta_key = 'views' AND wusers.ID = wposts.post_author AND wposts.post_status = 'publish' AND wposts.post_type = 'post'")->limit($startRow, $pageSize)->order('id desc')->select();
		
		$this->jsonReturn(array('articleList' => $articleList), '读取成功', 1);
		return;
	}

	public function articleById() {

		$articleId = I('articleId');

		if ($articleId <= 0) {
			
			$this->jsonReturn(null, '文章id无效', 0);
			return;
		}
		$articleModel = M('posts');
		$articleList = $articleModel->table('wp_posts wposts, wp_postmeta wpostmeta, wp_users wusers')->field(array('wposts.ID' => 'id', 'post_content' => 'content', 'post_title' => 'title', 'post_date' => 'date', 'comment_count' => 'commentCount', 'wusers.user_nicename' => 'authorName', 'post_author' => 'authorId', 'wpostmeta.meta_value' => 'views'))->where("wposts.ID = $articleId AND wusers.ID = wposts.post_author AND wpostmeta.meta_key = 'views'")->select();
		$articleInfo = $articleList[0];

		$articleInfo['content'] = base64_encode($articleInfo['content']);

		$commentModel = M('comments');
		$commentList = $commentModel->field(array('comment_id' => 'commentId', 'comment_author' => 'commentAuthor', 'comment_author_email' => 'commentAuthorEmail', 'comment_date' => 'commentDate', 'comment_content' => 'commentContent'))->where("comment_post_ID = $articleId")->select();

		$articleInfo['comments'] = $commentList;
		// dump($articleInfo);
		$this->jsonReturn(array('articleInfo' => $articleInfo), '读取成功', 1);
	}
}