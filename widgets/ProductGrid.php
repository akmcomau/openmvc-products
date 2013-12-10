<?php

namespace modules\products\widgets;

use core\classes\exceptions\RenderableException;
use core\classes\Model;
use core\classes\renderable\Widget;

class ProductGrid extends Widget {

	protected $products = [];

	public function getProductCount() {
		return count($this->products);
	}

	public function getProducts(array $params = NULL, array $ordering = NULL, array $pagination = NULL) {
		$model = new Model($this->config, $this->database);
		$product = $model->getModel('\modules\products\classes\models\Product');
		$this->products = $product->getMulti($params, $ordering, $pagination);
	}

	public function render() {
		$data = ['products' => $this->products];
		$template = $this->getTemplate('widgets/product_grid.php', $data, 'modules'.DS.'products');
		return $template->render();
	}
}