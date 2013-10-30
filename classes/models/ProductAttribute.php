<?php

namespace modules\products\classes\models;

use core\classes\Model;

class ProductAttribute extends Model {

	protected $table       = 'product_attribute';
	protected $primary_key = 'product_attribute_id';
	protected $columns     = [
		'product_attribute_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'site_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_attribute_type' => [
			'data_type'      => 'text',
			'data_length'    => 32,
			'null_allowed'   => FALSE,
		],
		'product_attribute_name' => [
			'data_type'      => 'text',
			'data_length'    => 256,
			'null_allowed'   => FALSE,
		],
		'product_attribute_description' => [
			'data_type'      => 'text',
			'data_length'    => 256,
			'null_allowed'   => FALSE,
		],
		'product_attribute_ordering' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
	];

	protected $indexes = [
		'site_id',
	];
}
