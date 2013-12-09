<?php

namespace modules\products\controllers;

use core\classes\exceptions\RedirectException;
use core\classes\exceptions\SoftRedirectException;
use core\classes\renderable\Controller;
use core\classes\Model;
use core\classes\Pagination;

class Products extends Controller {

	public function index() {

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