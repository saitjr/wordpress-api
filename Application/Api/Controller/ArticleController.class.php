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
		
		// 通过wp_posts，wp_postmeta两张表，获得文章基本信息与文章浏览量
		$articleList = $articleModel->table('wp_posts wp, wp_postmeta wpostmeta')->field(array('ID' => 'id', 'post_date' => 'date', 'post_title' => 'title', 'post_author', 'comment_count' => 'commentCount', 'wpostmeta.meta_value' => 'views'))->where("post_status = 'publish' AND post_type = 'post' AND wp.id = wpostmeta.post_id AND wpostmeta.meta_key = 'views'")->limit($startRow, $pageSize)->order('id DESC')->select();
		
		// 遍历文章列表，获取文章作者信息与分类信息
		foreach ($articleList as $key => $value) {

			$authorId = $value["post_author"];
			$postId = $value['id'];
			$value['author'] = $this->authorByAuthorId($authorId);
			unset($value['post_author']);

			$categorys = $this->categoryByPostId($postId);
			$value['categorys'] = $categorys;

			$articleList[$key] = $value;
		}
		
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
		
		// 通过wp_posts，wp_postmeta两张表，获得文章基本信息与文章浏览量
		$articleList = $articleModel->table('wp_posts wposts, wp_postmeta wpostmeta')->field(array('wposts.ID' => 'id', 'post_content' => 'content', 'post_title' => 'title', 'post_date' => 'date', 'comment_count' => 'commentCount', 'post_author', 'wpostmeta.meta_value' => 'views'))->where("wposts.ID = $articleId AND wpostmeta.meta_key = 'views'")->select();
		$articleInfo = $articleList[0];

		if (empty($articleInfo)) {
			
			$this->jsonReturn(null, '找不到该文章', 0);
			return;
		}

		$articleInfo['content'] = base64_encode($articleInfo['content']);
		
		$commentModel = M('comments');
		// 通过wp_comments表获得文章评论
		$commentList = $commentModel->field(array('comment_id' => 'id', 'comment_author' => 'author', 'comment_author_email' => 'authorEmail', 'comment_date' => 'date', 'comment_content' => 'content'))->where("comment_post_ID = $articleId")->select();
		$articleInfo['comments'] = $commentList;
		
		// 获得文章作者信息
		$articleInfo['author'] = $this->authorByAuthorId($articleInfo['post_author']);
		unset($articleInfo['post_author']);
	
		// 获得文章分类信息
		$postId = $articleInfo["id"];
		$categorys = $this->categoryByPostId($postId);
		$articleInfo['categorys'] = $categorys;

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
		$articleModel = M('posts');
		
		// 通过wp_posts，wp_postmeta两张表，获得文章基本信息与文章浏览量
		// 通过wp_term_taxonomy，wp_term_relationships，wp_posts三张表来确定文章的分类
		$articleList = $articleModel->table('wp_term_taxonomy wtt, wp_terms wt, wp_term_relationships wtr, wp_posts wposts, wp_postmeta wpostmeta')->field(array('wposts.ID'=>'id', 'post_date' => 'date', 'post_title' => 'title', 'comment_count' => 'commentCount', 'wpostmeta.meta_value' => 'views', 'post_author'))->where("wtt.term_taxonomy_id = $categoryId AND wtt.term_taxonomy_id = wtr.term_taxonomy_id AND wtr.object_id = wposts.ID AND wt.term_id = wtt.term_id AND wposts.id = wpostmeta.post_id AND wpostmeta.meta_key = 'views' AND wposts.post_status = 'publish' AND wposts.post_type = 'post' AND wtt.taxonomy = 'category'")->limit($startRow, $pageSize)->order('id desc')->select();
		
		// 遍历文章列表，绑定作者与文章分类
		foreach ($articleList as $key => $value) {
			// 作者信息
			$authorId = $value["post_author"];
			$postId = $value['id'];
			$value['author'] = $this->authorByAuthorId($authorId);
			unset($value['post_author']);
			
			// 文章文类
			$postId = $value["id"];
			$categorys = $this->categoryByPostId($postId);
			$value['categorys'] = $categorys;

			$articleList[$key] = $value;
		}

		$this->jsonReturn(array('articleList' => $articleList), '读取成功', 1);
		return;
	}
	
	/*
		热门文章接口
	 */
	public function hotArticles() {

		$categoryId = I('categoryId');
		$articleCount = I('articleCount');

		if ($articleCount <= 0) {
			$articleCount = 10;
		}

		$articleModel = M('posts');

		$articleList = null;
		
		// 如果传了分类id，则在当前分类中，进行浏览量排序
		// 如果没有传分类id，则在所有文章中，进行浏览量排序
		// 因为wp_postmeta表中，记录文章浏览量的meta_value是string类型，所以排序时要用mysql的CONVERT方法将meta_velue转成int
		if ($categoryId <= 0) {
			$articleList = $articleModel->table('wp_posts wp, wp_postmeta wpostmeta')->field(array('ID' => 'id', 'post_date' => 'date', 'post_title' => 'title', 'post_author', 'comment_count' => 'commentCount', 'meta_value' => 'views'))->where("post_status = 'publish' AND post_type = 'post' AND wp.id = wpostmeta.post_id AND wpostmeta.meta_key = 'views'")->limit(0, $articleCount)->order('CONVERT(views, SIGNED) DESC')->select();
		} else {
			// 因为在排序的同时，还要进行分类查询，所以要查询wp_term_taxonomy，wp_terms与wp_term_relationships表
			$articleList = $articleModel->table('wp_term_taxonomy wtt, wp_terms wt, wp_term_relationships wtr, wp_posts wposts, wp_postmeta wpostmeta')->field(array('wposts.ID'=>'id', 'post_date' => 'date', 'post_title' => 'title', 'comment_count' => 'commentCount', 'wpostmeta.meta_value' => 'views', 'post_author'))->where("wtt.term_id = $categoryId AND wtt.term_taxonomy_id = wtr.term_taxonomy_id AND wtr.object_id = wposts.ID AND wt.term_id = wtt.term_id AND wposts.id = wpostmeta.post_id AND wpostmeta.meta_key = 'views' AND wposts.post_status = 'publish' AND wposts.post_type = 'post'")->limit(0, $articleCount)->order('CONVERT(views, SIGNED) DESC')->select();
		}
		
		foreach ($articleList as $key => $value) {
			
			$authorId = $value["post_author"];
			$postId = $value['id'];
			$value['author'] = $this->authorByAuthorId($authorId);
			unset($value['post_author']);
			
			$categorys = $this->categoryByPostId($postId);
			// $categorys = $articleModel->table('wp_term_taxonomy wtt, wp_terms wt, wp_term_relationships wtr')->field(array('wtt.term_taxonomy_id' => 'id', 'name', 'count'))->where("wtt.term_taxonomy_id = wtr.term_taxonomy_id AND wtr.object_id = $postId AND wt.term_id = wtt.term_id AND wtt.taxonomy = 'category'")->select();
			$value['categorys'] = $categorys;

			$articleList[$key] = $value;
		}
		$this->jsonReturn(array('articleList' => $articleList), '读取成功', 1);
		return;
	}
	
	/*
		私有方法：通过文章id获得该文章的分类数组
	 */
	private function categoryByPostId($postId = 0) {

		$articleModel = M('posts');

		$categorys = $articleModel->table('wp_term_taxonomy wtt, wp_terms wt, wp_term_relationships wtr')->field(array('wtt.term_taxonomy_id' => 'id', 'name', 'count'))->where("wtt.term_taxonomy_id = wtr.term_taxonomy_id AND wtr.object_id = $postId AND wt.term_id = wtt.term_id AND wtt.taxonomy = 'category'")->select();
		return $categorys;
	}
	
	/*
		私有方法：通过文章作者id获得该作者其他信息
	 */
	private function authorByAuthorId($authorId = 0) {

		$articleModel = M('posts');

		$user = $articleModel->table('wp_users')->field(array('display_name' => 'name', 'ID' => 'id'))->where("ID = $authorId")->select();
		return $user[0];
	}
}
