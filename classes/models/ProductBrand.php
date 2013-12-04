<?php

namespace modules\products\classes\models;

use core\classes\models\Category;

class ProductBrand extends Category {

	protected $link_type = 'foreign-key';
	protected $link_from = 'product';

	protected $table       = 'product_brand';
	protected $primary_key = 'product_brand_id';
	protected $columns     = [
		'product_brand_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'site_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_brand_name' => [
			'data_type'      => 'text',
			'data_length'    => '128',
			'null_allowed'   => FALSE,
		],
		'product_brand_parent_id' => [
			'data_type'      => 'int',
			'null_allowed'   => TRUE,
		],
	];

	protected $indexes = [
		'site_id',
		'product_brand_parent_id',
	];

	protected $foreign_keys = [
		'product_brand_parent_id' => ['product_brand', 'product_brand_id'],
	];
}
