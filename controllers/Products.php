<?php

namespace modules\products\controllers;

use core\classes\exceptions\RedirectException;
use core\classes\exceptions\SoftRedirectException;
use core\classes\renderable\Controller;
use core\classes\Model;
use core\classes\FormValidator;
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

			$this->layout->addMetaTags([
				'title'       => $category->name.' :: '.$this->config->siteConfig()->name,
				/** 'description' => 'asdf',  @TODO, add to product_category */
				'keywords'    => $category->name,
			]);
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

		$description = str_replace("\n", '', $product->description);
		$description = str_replace("\r", '', $description);
		$description = strip_tags($description);
		$description = preg_replace('/\s+/', ' ', $description);
		$description = substr($description, 0, 160).'...';

		$this->layout->addMetaTags([
			'title'       => $product->name.' :: '.$this->config->siteConfig()->name,
			'description' => $description,
			'keywords'    => $product->name.','.$product->model.','.$product->getBrandName(),
		]);

		$data = [
			'product' => $product,
		];

		$template = $this->getTemplate('pages/view_product.php', $data, 'modules'.DS.'products');
		$this->response->setContent($template->render());
	}

	public function search() {
		$this->language->loadLanguageFile('products.php', 'modules'.DS.'products');
		$form = $this->getProductSearchForm();
		$model = new Model($this->config, $this->database);

		$params = ['site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()]];
		if ($form->validate()) {
			$values = $form->getSubmittedValues();
			foreach ($values as $name => $value) {
				if ($name == 'search_query' && !empty($value)) {
					$value = strtolower($value);
					$params['or-1'] = [
						'name' => ['type'=>'like', 'value'=>'%'.$value.'%'],
						'description' => ['type'=>'like', 'value'=>'%'.$value.'%'],
					];
				}
				elseif (preg_match('/search_(brand|category)/', $name, $matches)) {
					if ((int)$value) {
						$params['product_'.$matches[1].'_id'] = (int)$value;
					}
				}
			}
		}

		// get all the product types
		$products = new ProductGrid($this->config, $this->database, $this->request, $this->language);
		$products->getProducts($params);

		$brand = $model->getModel('\modules\products\classes\models\ProductBrand');
		$brands = $brand->getAsOptions($this->allowedSiteIDs());

		$category = $model->getModel('\modules\products\classes\models\ProductCategory');
		$categories = $category->getAsOptions($this->allowedSiteIDs());

		$data = [
			'form' => $form,
			'brands' => $brands,
			'categories' => $categories,
			'products' => $products,
		];

		$template = $this->getTemplate('pages/search_product.php', $data, 'modules'.DS.'products');
		$this->response->setContent($template->render());
	}

	protected function getProductSearchForm() {
		$inputs = [
			'search_query' => [
				'type' => 'string',
				'required' => FALSE,
				'max_length' => 256,
				'message' => $this->language->get('error_search_query'),
			],
			'search_brand' => [
				'type' => 'string',
				'required' => FALSE,
				'max_length' => 256,
				'message' => $this->language->get('error_search_brand'),
			],
			'search_category' => [
				'type' => 'string',
				'required' => FALSE,
				'max_length' => 256,
				'message' => $this->language->get('error_search_category'),
			],
		];

		return new FormValidator($this->request, 'form-products-search', $inputs);
	}

}