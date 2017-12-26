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

	public function getAllUrls($include_filter = NULL, $exclude_filter = NULL) {
		$controller = $this->url->getControllerClassName('\\'.get_class($this));
		$model = new Model($this->config, $this->database);

		$urls = [];
		$products = $model->getModel('\modules\products\classes\models\Product')->getMulti([
			'site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()],
			'sell' => ['type'=>'>', 'value'=>0],
			'active' => TRUE,
		]);
		foreach ($products as $product) {
			$urls[] = ['url' => $this->url->getUrl($controller, 'view', [$product->id, $product->name])];
		}

		$categories = $model->getModel('\modules\products\classes\models\ProductCategory')->getMulti([
			'site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()],
		]);
		foreach ($categories as $category) {
			$urls[] = ['url' => $this->url->getUrl($controller, 'index', ['category', $category->id, $category->name])];
		}

		$urls[] = ['url' => $this->url->getUrl($controller, 'index', ['category'])];

		return array_merge(
			parent::getAllUrls(NULL, '/view/'),
			$urls
		);
	}

	public function index($type = NULL, $id = NULL, $name = NULL) {
		$model = new Model($this->config, $this->database);
		$this->language->loadLanguageFile('products.php', 'modules'.DS.'products');

		$form = $this->getProductSearchForm();

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

		$category_grid = $this->getCategoryGrid('\modules\products\classes\models\ProductCategory');

		$module_config = $this->config->moduleConfig('\modules\products');
		$categories = new CategoryGrid($this->config, $this->database, $this->request, $this->language);
		$categories->getCategories($class,
			[
				'site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()],
				'active' => TRUE,
				'sell' => ['type'=>'>', 'value'=>0],
				'parent_id' => (int)$id ? (int)$id : NULL,
			],
			['name' => 'asc']
		);

		$pagination = new Pagination($this->request, 'name', 'asc');
		$products = new ProductGrid($this->config, $this->database, $this->request, $this->language);
		$params = [
			'site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()],
			'active' => TRUE
		];
		if ($field && $id) {
			$params[$field] = $id;
		}
		$products->getProducts($params, $pagination->getOrdering(), $pagination->getLimitOffset());
		$pagination->setRecordCount($products->getProductCount($params));

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

				// make sure this has the correct title in the params
				$method_params = $this->request->getMethodParams();
				$method_params[2] = $this->url->canonical($category->name);
				$this->request->setMethodParams($method_params);

				$this->layout->addMetaTags([
					'title'       => $this->language->get('products_browse_in', $category->name).' :: '.$this->config->siteConfig()->name,
					/** 'description' => 'asdf',  @TODO, add to product_category */
					'keywords'    => $category->name,
				]);
			}
			else {
				throw new SoftRedirectException($this->url->getControllerClass('Root'), 'error404');
			}
		}

		$data = [
			'categories' => $categories,
			'category_grid' => $category_grid,
			'products' => $products,
			'pagination' => $pagination,
			'sub_heading' => $sub_heading,
			'main_heading' => $main_heading,
			'group_name' => $group_name,
			'form' => $form,
		];

		$this->layout->setTemplateData(['pagination' => $pagination->getStatus()]);
		$template = $this->getTemplate('pages/browse_products.php', $data, 'modules'.DS.'products');
		$this->response->setContent($template->render());
	}

	public function viewed() {
		$this->language->loadLanguageFile('products.php', 'modules'.DS.'products');
		$form = $this->getProductSearchForm();

		$categories = $this->getCategoryGrid('\modules\products\classes\models\ProductCategory');

		$model = new Model($this->config, $this->database);
		$product = $model->getModel('\\modules\\products\\classes\\models\\Product');

		$order_by = [];
		foreach ($product->getViewedIds($this->request) as $id) {
			$order_by["product_id = $id DESC"] = 'SQL';
		}

		$pagination = new Pagination($this->request, 'name', 'asc');
		$products = new ProductGrid($this->config, $this->database, $this->request, $this->language);
		$params = [
			'site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()],
			'id' => ['type' => 'in', 'value' => $product->getViewedIds($this->request)],
			'active' => TRUE
		];
		$products->getProducts($params, $order_by, $pagination->getLimitOffset());
		$pagination->setRecordCount($products->getProductCount($params));

		$data = [
			'categories' => $categories,
			'products' => $products,
			'pagination' => $pagination,
			'form' => $form,
		];

		$this->layout->setTemplateData(['pagination' => $pagination->getStatus()]);
		$template = $this->getTemplate('pages/viewed_products.php', $data, 'modules'.DS.'products');
		$this->response->setContent($template->render());
	}

	public function view($product_id, $name = NULL) {
		$this->language->loadLanguageFile('products.php', 'modules'.DS.'products');
		$form = $this->getProductSearchForm();

		$categories = $this->getCategoryGrid('\modules\products\classes\models\ProductCategory');

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

		// make sure this has the correct title in the params
		$method_params = $this->request->getMethodParams();
		$method_params[1] = $this->url->canonical($product->name);
		$this->request->setMethodParams($method_params);

		$product->trackViewed($this->request);

		$description = str_replace("\n", '', $product->description);
		$description = str_replace("\r", '', $description);
		$description = strip_tags($description);
		$description = preg_replace('/\s+/', ' ', $description);
		$description = substr($description, 0, 160).'...';

		if ($this->authentication->administratorLoggedIn()) {
			$this->layout->setTemplateData([
				'admin_panel_extra' => [
					'edit_product' => [
						'text_tag' => 'edit_product',
						'controller' => 'administrator/Products',
						'method' => 'editProduct',
						'params' => [$product->id],
						'icon' => 'fa fa-pencil-square-o',
						'after' => 'edit_page',
					],
				],
			]);
		}

		$meta_tags = [
			'title'       => $product->name.' :: '.$this->config->siteConfig()->name,
			'description' => $description,
			'keywords'    => $product->name.','.$product->model.','.$product->getBrandName(),
		];
		if ($this->config->siteConfig()->og_meta_tags) {
			$meta_tags['og:title'] = $meta_tags['title'];
			$meta_tags['og:description'] = $meta_tags['description'];
		}

		// get the products main image
		$images = $product->getImages();
		if (count($images)) {
			$meta_tags['og:image'] = $images[0]->getUrl();
		}

		$this->layout->addMetaTags($meta_tags);

		$data = [
			'categories' => $categories,
			'product' => $product,
			'form' => $form,
		];

		$template = $this->getTemplate('pages/view_product.php', $data, 'modules'.DS.'products');
		$this->response->setContent($template->render());
	}

	public function search() {
		$this->language->loadLanguageFile('products.php', 'modules'.DS.'products');
		$form = $this->getProductSearchForm();
		$model = new Model($this->config, $this->database);

		$category_grid = $this->getCategoryGrid('\modules\products\classes\models\ProductCategory');

		$search_in = $search_for = 0;
		$params = [
			'active' => TRUE,
			'sell' => ['type'=>'>', 'value'=>0],
			'site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()]
		];
		if ($form->validate()) {
			$values = $form->getSubmittedValues();
			foreach ($values as $name => $value) {
				if ($name == 'search_query' && !empty($value)) {
					$counter = 0;
					$words = explode(" ", $value);
					foreach ($words as $word) {
						$search_for++;
						$params['name:'.$counter++] = [
							 'type'  => 'likelower',
							 'value' => '%'.$word.'%'
						];
					}
				}
				elseif (preg_match('/search_(brand|category)/', $name, $matches)) {
					if ((int)$value) {
						$search_in++;
						$params['product_'.$matches[1].'_id'] = (int)$value;
					}
				}
			}
		}

		// get all the product types
		$pagination = new Pagination($this->request, 'name', 'asc');
		$products = new ProductGrid($this->config, $this->database, $this->request, $this->language);
		$products->getProducts($params, $pagination->getOrdering(), $pagination->getLimitOffset());
		$pagination->setRecordCount($products->getProductCount($params));

		$brand = $model->getModel('\modules\products\classes\models\ProductBrand');
		$brands = $brand->getAsOptions($this->allowedSiteIDs());
		$brand = (int)$form->getValue('search_brand') ? $brand->get(['id' => $form->getValue('search_brand')]) : NULL;

		$category = $model->getModel('\modules\products\classes\models\ProductCategory');
		$categories = $category->getAsOptions($this->allowedSiteIDs());
		$category = (int)$form->getValue('search_category') ? $category->get(['id' => $form->getValue('search_category')]) : NULL;

		$single = $brand ? $brand : $category;

		$description = '';
		$title = $this->language->get('products_search_results');
		if ($search_for == 1 && $search_in == 0) {
			$description = $title = $this->language->get('products_search_results_for', $form->getValue('search_query'));
		}
		elseif ($search_for == 0 && $search_in == 1) {
			$description = $title = $this->language->get('products_search_results_in', $single->name);
		}
		elseif ($search_for == 0 && $search_in == 2) {
			$description = $title = $this->language->get('products_search_results_in2', [$category->name, $brand->name]);
		}
		elseif ($search_for == 1 && $search_in == 1) {
			$description = $title = $this->language->get('products_search_results_for_in', [$form->getValue('search_query'), $single->name]);
		}
		elseif ($search_for == 1 && $search_in == 2) {
			$description = $title = $this->language->get('products_search_results_for_in2', [$form->getValue('search_query'), $category->name, $brand->name]);
		}
		$this->layout->addMetaTags([
			'title' => $title.' :: '.$this->config->siteConfig()->name,
			'description' => $this->language->get('meta_description_prefix').$description.$this->language->get('meta_description_suffix'),
		]);

		$data = [
			'category_grid' => $category_grid,
			'form' => $form,
			'brands' => $brands,
			'pagination' => $pagination,
			'categories' => $categories,
			'products' => $products,
		];

		$this->layout->setTemplateData(['pagination' => $pagination->getStatus()]);
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

	protected function getCategoryGrid($class) {
		$module_config = $this->config->moduleConfig('\modules\products');
		$categories = new CategoryGrid($this->config, $this->database, $this->request, $this->language);
		$categories->getCategories($class,
			[
				'site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()],
				'active' => TRUE,
				'sell' => ['type'=>'>', 'value'=>0],
			],
			['name' => 'asc']
		);

		return $categories;
	}
}
