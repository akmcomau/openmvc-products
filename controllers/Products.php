<?php

namespace modules\products\controllers;

use core\classes\exceptions\RedirectException;
use core\classes\exceptions\SoftRedirectException;
use core\classes\renderable\Controller;
use core\classes\Model;
use core\classes\Pagination;
use core\widgets\CategoryGrid;
use modules\products\widgets\ProductGrid;

class Products extends Controller {

	public function index($type = NULL, $id = NULL) {
		$model = new Model($this->config, $this->database);
		$this->language->loadLanguageFile('products.php', 'modules'.DS.'products');

		$field = NULL;
		$class = NULL;
		switch ($type) {
			default:
				$id = NULL;
			case 'category':
				$field = 'product_category_id';
				$type = 'category';
				$class = '\modules\products\classes\models\ProductCategory';
				break;
		}

		$categories = new CategoryGrid($this->config, $this->database, $this->request, $this->language);
		$categories->getCategories($class, [
			'site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()],
			'active' => TRUE,
			'parent_id' => (int)$id ? (int)$id : NULL,
		]);

		$products = new ProductGrid($this->config, $this->database, $this->request, $this->language);
		$params = [
			'site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()],
			'featured' => TRUE,
			'active' => TRUE
		];
		if ($field) {
			$params[$field] = $id;
		}
		$products->getProducts($params);

		$group_name = '';
		$main_heading = $this->language->get('categories');
		$sub_heading = $this->language->get('uncategorized_products');
		if ($id) {
			$category = $model->getModel($class)->get([
				'id' => $id,
				'site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()],
			]);
			if ($category) {
				$sub_heading = $this->language->get('products_in', [htmlspecialchars($category->name)]);
				$group_name = htmlspecialchars($category->name);
				$main_heading = $this->language->get('sub_categories', [htmlspecialchars($category->name)]);
			}
		}

		$data = [
			'categories' => $categories,
			'products' => $products,
			'sub_heading' => $sub_heading,
			'main_heading' => $main_heading,
			'group_name' => $group_name,
		];

		$template = $this->getTemplate('pages/browse_products.php', $data, 'modules'.DS.'products');
		$this->response->setContent($template->render());
	}

	public function view($product_id) {
		$this->language->loadLanguageFile('products.php', 'modules'.DS.'products');

		$model = new Model($this->config, $this->database);
		$product = $model->getModel('\modules\products\classes\models\Product')->get([
			'site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()],
			'id' => $product_id,
			'active' => TRUE,
		]);
		if (!$product) {
			throw new SoftRedirectException($this->url->getControllerClass('Root'), 'error404');
		}
		$this->siteProtection($product);

		$data = [
			'product' => $product,
		];

		$template = $this->getTemplate('pages/view_product.php', $data, 'modules'.DS.'products');
		$this->response->setContent($template->render());
	}

}