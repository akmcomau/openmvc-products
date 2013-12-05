<?php

namespace modules\products\controllers\administrator;

use core\classes\exceptions\RedirectException;
use core\classes\exceptions\SoftRedirectException;
use core\classes\renderable\Controller;
use core\classes\Model;
use core\classes\Pagination;
use core\classes\FormValidator;
use core\controllers\administrator\CategoryManager;
use modules\subscriptions\classes\models\SubscriptionType;
use modules\subscriptions\classes\models\Subscription;

class Products extends Controller {

	protected $show_admin_layout = TRUE;

	protected $permissions = [
		'config' => ['administrator'],
		'index' => ['administrator'],
		'add' => ['administrator'],
		'brands' => ['administrator'],
		'categories' => ['administrator'],
		'attributes' => ['administrator'],
	];

	public function config() {

	}

	public function index($message = NULL) {
		$this->language->loadLanguageFile('administrator/products.php', 'modules'.DS.'products');
		$form_search = $this->getProductSearchForm();

		$pagination = new Pagination($this->request, 'created', 'desc');

		$params = ['site_id' => ['type'=>'in', 'value'=>$this->allowedSiteIDs()]];
		if ($form_search->validate()) {
			$values = $form_search->getSubmittedValues();
			foreach ($values as $name => $value) {
				if (preg_match('/^search_(first_name|last_name|email|login)$/', $name, $matches) && $value != '') {
					$value = strtolower($value);
					$params['customer_'.$matches[1]] = ['type'=>'like', 'value'=>'%'.$value.'%'];
				}
			}
		}

		// get all the product types
		$model  = new Model($this->config, $this->database);
		$product = $model->getModel('\modules\products\classes\models\Product');
		$products = $product->getMulti($params, $pagination->getOrdering(), $pagination->getLimitOffset());
		$pagination->setRecordCount($product->getCount($params));

		$message_js = NULL;
		switch($message) {
			case 'delete-success':
				$message_js = 'FormValidator.displayPageNotification("success", "'.htmlspecialchars($this->language->get('notification_product_delete_success')).'");';
				break;

			case 'add-success':
				$message_js = 'FormValidator.displayPageNotification("success", "'.htmlspecialchars($this->language->get('notification_product_add_success')).'");';
				break;

			case 'update-success':
				$message_js = 'FormValidator.displayPageNotification("success", "'.htmlspecialchars($this->language->get('notification_product_update_success')).'");';
				break;
		}

		$data = [
			'form' => $form_search,
			'products' => $products,
			'pagination' => $pagination,
			'message_js' => $message_js,
		];

		$template = $this->getTemplate('pages/administrator/list_products.php', $data, 'modules'.DS.'products');
		$this->response->setContent($template->render());
	}

	protected function getProductSearchForm() {
		$inputs = [
			'search_sku' => [
				'type' => 'string',
				'required' => FALSE,
				'max_length' => 64,
				'message' => $this->language->get('error_search_sku'),
			],
			'search_model' => [
				'type' => 'string',
				'required' => FALSE,
				'max_length' => 64,
				'message' => $this->language->get('error_search_model'),
			],
			'search_name' => [
				'type' => 'string',
				'required' => FALSE,
				'max_length' => 256,
				'message' => $this->language->get('error_search_name'),
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
			'search_active' => [
				'type' => 'string',
				'required' => FALSE,
				'max_length' => 256,
				'message' => $this->language->get('error_search_active'),
			],
		];

		return new FormValidator($this->request, 'form-products-search', $inputs);
	}

	protected function updateProductFromRequest(FormValidator $form, $product) {
		// basic info
		$product->sku = $form->getValue('sku');
		$product->model = $form->getValue('model');
		$product->name = $form->getValue('name');
		$product->active = (int)$form->getValue('active') ? TRUE : FALSE;
		$product->featured = (int)$form->getValue('featured') ? TRUE : FALSE;
		$product->description = $form->getValue('description');
		$product->cost = empty($form->getValue('cost')) ? NULL : $form->getValue('cost');
		$product->sell = $form->getValue('sell');
		$product->brand_id = ((int)$form->getValue('brand')) ? (int)$form->getValue('brand') : NULL;

		// image uploads
		$ordering = $product->getNumImages();
		for ($i=0; $i<$this->request->requestParam('num_images'); $i++) {
			if ($this->request->fileParam('image')['error'][$i] == 0) {
				$image = $product->getModel('\modules\products\classes\models\ProductImage');
				$image->setTmpName($this->request->fileParam('image')['tmp_name'][$i]);
				$image->setOriginalFilename($this->request->fileParam('image')['name'][$i]);
				$image->ordering   = ++$ordering;
				$image->product_id = $product->id;
				$product->addImage($image);
			}
		}

		// deleted images
		$delete_images = $this->request->requestParam('delete_images');
		for ($i=0; $i<count($delete_images); $i++) {
			$product->removeImage($delete_images[$i]);
		}

		$product->setCategory(NULL);
		if ((int)$form->getValue('category')) {
			$product_category = $product->getModel('\modules\products\classes\models\ProductCategory')->get([
				'id' => (int)$form->getValue('category'),
			]);
			if ($product_category) {
				$product->setCategory($product_category);
			}
		}
	}


	public function addProduct() {
		$this->language->loadLanguageFile('administrator/products.php', 'modules'.DS.'products');
		$model = new Model($this->config, $this->database);
		$product = $model->getModel('\modules\products\classes\models\Product');
		$product->site_id = $this->config->siteConfig()->site_id;
		$form = $this->getProductForm(TRUE, $product);

		if ($form->validate()) {
			$this->updateProductFromRequest($form, $product);
			$product->insert();
			$product->updateImages();
			throw new RedirectException($this->url->getUrl('administrator/Products', 'index', ['add-success']));
		}
		elseif ($form->isSubmitted()) {
			$this->updateProductFromRequest($form, $product);
			$form->setNotification('error', $this->language->get('notification_product_add_error'));
		}

		$brand = $model->getModel('\modules\products\classes\models\ProductBrand');
		$brands = $brand->getAsOptions($this->allowedSiteIDs());

		$category = $model->getModel('\modules\products\classes\models\ProductCategory');
		$categories = $category->getAsOptions($this->allowedSiteIDs());

		$data = [
			'is_add_page' => TRUE,
			'form' => $form,
			'brands' => $brands,
			'categories' => $categories,
			'product' => $product,
		];
		$template = $this->getTemplate('pages/administrator/add_edit_product.php', $data, 'modules'.DS.'products');
		$this->response->setContent($template->render());
	}

	public function editProduct($product_id) {
		$this->language->loadLanguageFile('administrator/products.php', 'modules'.DS.'products');
		$model = new Model($this->config, $this->database);
		$product = $model->getModel('\modules\products\classes\models\Product')->get([
			'id' => (int)$product_id
		]);
		$this->siteProtection($product);
		$form = $this->getProductForm(FALSE, $product);

		if ($form->validate()) {
			$this->updateProductFromRequest($form, $product);
			$product->update();
			$product->updateImages();
			throw new RedirectException($this->url->getUrl('administrator/Products', 'index', ['update-success']));
		}
		elseif ($form->isSubmitted()) {
			$this->updateProductFromRequest($form, $product);
			$form->setNotification('error', $this->language->get('notification_product_update_error'));
		}

		$brand = $model->getModel('\modules\products\classes\models\ProductBrand');
		$brands = $brand->getAsOptions($this->allowedSiteIDs());

		$category = $model->getModel('\modules\products\classes\models\ProductCategory');
		$categories = $category->getAsOptions($this->allowedSiteIDs());

		$data = [
			'is_add_page' => FALSE,
			'form' => $form,
			'brands' => $brands,
			'categories' => $categories,
			'product' => $product,
		];
		$template = $this->getTemplate('pages/administrator/add_edit_product.php', $data, 'modules'.DS.'products');
		$this->response->setContent($template->render());
	}

	protected function getProductForm($is_add_page, $product) {
		$model  = new Model($this->config, $this->database);
		$inputs = [
			'name' => [
				'type' => 'string',
				'max_length' => 128,
				'required' => TRUE,
				'message' => $this->language->get('error_product_name'),
			],
			'sku' => [
				'type' => 'string',
				'max_length' => 64,
				'required' => TRUE,
				'message' => $this->language->get('error_product_sku'),
			],
			'model' => [
				'type' => 'string',
				'max_length' => 64,
				'required' => TRUE,
				'message' => $this->language->get('error_product_model'),
			],
			'brand' => [
				'type' => 'integer',
				'required' => FALSE,
				'message' => $this->language->get('error_product_brand'),
			],
			'category' => [
				'type' => 'integer',
				'required' => FALSE,
				'message' => $this->language->get('error_product_category'),
			],
			'featured' => [
				'type' => 'integer',
				'required' => TRUE,
				'message' => $this->language->get('error_product_featured'),
			],
			'active' => [
				'type' => 'integer',
				'required' => TRUE,
				'message' => $this->language->get('error_product_active'),
			],
			'description' => [
				'type' => 'string',
				'max_length' => 65535,
				'required' => FALSE,
				'message' => $this->language->get('error_product_description'),
			],
			'cost' => [
				'type' => 'money',
				'required' => FALSE,
				'message' => $this->language->get('error_product_cost'),
			],
			'sell' => [
				'type' => 'money',
				'required' => TRUE,
				'message' => $this->language->get('error_product_sell'),
			],
		];

		$validators = [
			'sku' => [
				[
					'type'     => 'function',
					'message'  => $this->language->get('error_sku_already_exists'),
					'function' => function($value) use ($model, $product, $is_add_page) {
						if (!$is_add_page && $product->sku == $value) {
							return TRUE;
						}
						$product = $model->getModel('\modules\products\classes\models\Product');
						$product = $product->get(['sku' => $value]);
						return $product ? FALSE : TRUE;
					}
				],
			],
		];

		return new FormValidator($this->request, 'form-product', $inputs, $validators);
	}

	public function attributes($message = NULL) {

	}

	public function brands($message = NULL) {
		$manager = new CategoryManager($this->config, $this->database, $this->request, $this->response);
		$manager->category_manager($message, '\modules\products\classes\models\ProductBrand', FALSE);
	}

	public function categories($message = NULL) {
		$manager = new CategoryManager($this->config, $this->database, $this->request, $this->response);
		$manager->category_manager($message, '\modules\products\classes\models\ProductCategory');
	}

}