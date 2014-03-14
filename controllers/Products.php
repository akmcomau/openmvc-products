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
			$urls[] = ['url' => $this->config->getSiteUrl().$this->url->getUrl($controller, 'view', [$product->id, $product->name])];
		}

		$categories = $model->getModel('\modules\products\classes\models\ProductCategory')->getMulti([
			'site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()],
		]);
		foreach ($categories as $category) {
			$urls[] = ['url' => $this->config->getSiteUrl().$this->url->getUrl($controller, 'index', ['category', $category->id, $category->name])];
		}

		$urls[] = ['url' => $this->config->getSiteUrl().$this->url->getUrl($controller, 'index', ['category'])];

		return array_merge(
			parent::getAllUrls(NULL, '/view/'),
			$urls
		);
	}

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
		if ($field) {
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
			}

			$this->layout->addMetaTags([
				'title'       => $this->language->get('products_browse_in', $category->name).' :: '.$this->config->siteConfig()->name,
				/** 'description' => 'asdf',  @TODO, add to product_category */
				'keywords'    => $category->name,
			]);
		}

		$data = [
			'categories' => $categories,
			'products' => $products,
			'pagination' => $pagination,
			'sub_heading' => $sub_heading,
			'main_heading' => $main_heading,
			'group_name' => $group_name,
		];

		$this->layout->setTemplateData(['pagination' => $pagination->getStatus()]);
		$template = $this->getTemplate('pages/browse_products.php', $data, 'modules'.DS.'products');
		$this->response->setContent($template->render());
	}

	public function view($product_id) {
		$this->language->loadLanguageFile('products.php', 'modules'.DS.'products');

		$model = new Model($this->config, $this->database);
		$product = $model->getModel('\modules\products\classes\models\Product')->get([
			'site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()],
			'id' => $product_id,
			'sell' => ['type'=>'>', 'value'=>0],
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
					$search_for++;
					$value = strtolower($value);
					$params['or:1'] = [
						'name' => ['type'=>'likelower', 'value'=>'%'.$value.'%'],
						'description' => ['type'=>'likelower', 'value'=>'%'.$value.'%'],
					];
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

}
