<?php

namespace modules\products\classes\models;

use core\classes\Model;

class ProductCategoryLink extends Model {

	protected $table       = 'product_category_link';
	protected $primary_key = 'product_category_link_id';
	protected $columns     = [
		'product_category_link_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'product_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_category_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
	];

	protected $indexes = [
		'product_id',
		'product_category_id',
	];

	protected $foreign_keys = [
		'product_id' => ['product', 'product_id'],
		'product_category_id' => ['product_category', 'product_category_id'],
	];

}
