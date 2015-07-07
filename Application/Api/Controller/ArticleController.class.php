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
		$articleList = $articleModel->table('wp_term_taxonomy wtt, wp_terms wt, wp_term_relationships wtr, wp_posts wposts, wp_postmeta wpostmeta, wp_users wusers')->field(array('wposts.ID'=>'id', 'post_date' => 'date', 'post_title' => 'title', 'comment_count' => 'commentCount', 'post_author' => 'authorId', 'wusers.user_nicename' => 'authorName', 'wpostmeta.meta_value' => 'views', 'wtt.term_id' => 'categoryId', 'wt.name' => 'categoryName'))->where("wtt.term_taxonomy_id = wtr.term_taxonomy_id AND wtr.object_id = wposts.ID AND wt.term_id = wtt.term_id AND wtt.taxonomy = 'category' AND wposts.id = wpostmeta.post_id AND wpostmeta.meta_key = 'views' AND wusers.ID = wposts.post_author AND wposts.post_status = 'publish' AND wposts.post_type = 'post'")->limit($startRow, $pageSize)->order('wposts.id desc')->select();
		
		$this->jsonReturn(array('articleList' => $articleList), '读取成功', 1);
		return;
	}

	/*
		文章详情接口
	 */
	public function articleById() {

		$articleId = I('articleId');

		if ($articleId <= 0) {
			
			$this->jsonReturn(null, '文章id无效', 0);
			return;
		}
		$articleModel = M('posts');
		$articleList = $articleModel->table('wp_posts wposts, wp_postmeta wpostmeta, wp_users wusers')->field(array('wposts.ID' => 'id', 'post_content' => 'content', 'post_title' => 'title', 'post_date' => 'date', 'comment_count' => 'commentCount', 'wusers.user_nicename' => 'authorName', 'post_author' => 'authorId', 'wpostmeta.meta_value' => 'views'))->where("wposts.ID = $articleId AND wusers.ID = wposts.post_author AND wpostmeta.meta_key = 'views'")->select();
		$articleInfo = $articleList[0];

		if (empty($articleInfo)) {
			
			$this->jsonReturn(null, '找不到该文章', 0);
			return;
		}

		$articleInfo['content'] = base64_encode($articleInfo['content']);

		$commentModel = M('comments');
		$commentList = $commentModel->field(array('comment_id' => 'commentId', 'comment_author' => 'commentAuthor', 'comment_author_email' => 'commentAuthorEmail', 'comment_date' => 'commentDate', 'comment_content' => 'commentContent'))->where("comment_post_ID = $articleId")->select();

		$articleInfo['comments'] = $commentList;
		// dump($articleInfo);
		$this->jsonReturn(array('articleInfo' => $articleInfo), '读取成功', 1);
		return;
	}

	/*
		查看文章时，文章浏览量自增
	 */
	public function articleViewed() {

		$articleId = I('articleId');

		if ($articleId <= 0) {
			
			$this->jsonReturn(null, '文章id无效', 0);
			return;
		}

		$metaModel = M('postmeta');
		$flag = $metaModel->where("post_id = $articleId AND meta_key = 'views'")->setInc('meta_value');
		// echo $metaModel->getLastSql();

		if ($flag != 0) {
			
			$this->jsonReturn(null, '修改成功', 1);
			return;
		}
		$this->jsonReturn(null, '修改失败', 0);
		return;
	}

	/*
		通过分类id获取文章列表接口
	 */
	public function articleByCategoryId() {

		$categoryId = I('categoryId');
		$page = I('page');
		$pageSize = I('pageSize');
		
		if ($pageSize <= 0) {
			
			$pageSize = 15;
		}
		if ($categoryId <= 0) {
			
			$this->jsonReturn(null, '分类id无效', 0);
			return;
		}

		$startRow = $page * $pageSize;
		$termModel = M('terms');

		$articleList = $termModel->table('wp_term_taxonomy wtt, wp_terms wt, wp_term_relationships wtr, wp_posts wposts, wp_postmeta wpostmeta, wp_users wusers')->field(array('wposts.ID'=>'id', 'post_date' => 'date', 'post_title' => 'title', 'comment_count' => 'commentCount', 'post_author' => 'authorId', 'wusers.user_nicename' => 'authorName', 'wpostmeta.meta_value' => 'views', 'wtt.term_id' => 'categoryId', 'wt.name' => 'categoryName'))->where("wtt.term_id = $categoryId AND wtt.term_taxonomy_id = wtr.term_taxonomy_id AND wtr.object_id = wposts.ID AND wt.term_id = wtt.term_id AND wposts.id = wpostmeta.post_id AND wpostmeta.meta_key = 'views' AND wusers.ID = wposts.post_author AND wposts.post_status = 'publish' AND wposts.post_type = 'post'")->limit($startRow, $pageSize)->order('id desc')->select();
		
		$this->jsonReturn(array('articleList' => $articleList), '读取成功', 1);
		return;
	}
}