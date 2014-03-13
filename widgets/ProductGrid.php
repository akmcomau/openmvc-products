<?php

namespace modules\products\widgets;

use core\classes\exceptions\RenderableException;
use core\classes\Model;
use core\classes\renderable\Widget;

class ProductGrid extends Widget {

	protected $products = [];
	protected $product_count = NULL;


	public function getProducts(array $params = NULL, array $ordering = NULL, array $pagination = NULL) {
		$model = new Model($this->config, $this->database);
		$product = $model->getModel('\modules\products\classes\models\Product');
		$this->products = $product->getMulti($params, $ordering, $pagination);
	}

	public function getProductCount(array $params = NULL, array $ordering = NULL, array $pagination = NULL) {
		if (is_null($this->product_count)) {
			$model = new Model($this->config, $this->database);
			$product = $model->getModel('\modules\products\classes\models\Product');
			$this->product_count = $product->getCount($params);
		}
		return $this->product_count;
	}

	public function render($template = 'widgets/product_grid.php') {
		$data = ['products' => $this->products];
		$template = $this->getTemplate($template, $data, 'modules'.DS.'products');
		return $template->render();
	}
}