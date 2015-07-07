<?php
namespace Api\Controller;

/**
* 文章分类接口类
*/
class CategoryController extends ApiController {

	public function categoryList() {

		$termModel = M('terms');
		$termTaxonomyModel = M('term_taxonomy');

		$categoryList = $termModel->table('wp_terms wt, wp_term_taxonomy wtt')->field(array('wt.term_id' => 'id', 'name' => 'name', 'count' => 'articleCount', 'description' => 'description'))->where("wtt.taxonomy = 'category' AND wt.term_id = wtt.term_id")->order('count desc')->select();
		
		$this->jsonReturn(array('categoryList' => $categoryList), '读取完成', 1);
		return;
	}
}

?>