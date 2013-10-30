<?php

namespace modules\products\classes\models;

use core\classes\models\Category;

class ProductCategory extends Category {

	protected $table       = 'product_category';
	protected $primary_key = 'product_category_id';
	protected $columns     = [
		'product_category_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'site_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_category_name' => [
			'data_type'      => 'text',
			'data_length'    => '128',
			'null_allowed'   => FALSE,
		],
		'product_category_parent_id' => [
			'data_type'      => 'int',
			'null_allowed'   => TRUE,
		],
	];

	protected $indexes = [
		'site_id',
		'product_category_parent_id',
	];

	protected $foreign_keys = [
		'product_category_parent_id' => ['product_category', 'product_category_id'],
	];
}
