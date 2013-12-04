<?php

namespace modules\products\controllers\administrator;

use core\controllers\administrator\CategoryManager;

class ProductCategoryManager extends CategoryManager {

	protected $show_admin_layout = TRUE;
	protected $controller_class = 'administrator/ProductCategoryManager';

	protected $permissions = [
		'brands' => ['administrator'],
		'categories' => ['administrator'],
	];

	public function brands($message = NULL) {
		$this->category_manager($message, '\modules\products\classes\models\ProductBrand', FALSE);
	}

	public function categories($message = NULL) {
		$this->category_manager($message, '\modules\products\classes\models\ProductCategory');
	}

}