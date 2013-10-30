<?php

namespace modules\products\classes\models;

use core\classes\Model;

class ProductAttributeValue extends Model {

	protected $table       = 'product_attribute_value';
	protected $primary_key = 'product_attribute_value_id';
	protected $columns     = [
		'product_attribute_value_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'product_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_attribute_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_attribute_option_id' => [
			'data_type'      => 'int',
			'null_allowed'   => TRUE,
		],
		'product_attribute_value_text' => [
			'data_type'      => 'text',
			'data_length'    => 256,
			'null_allowed'   => TRUE,
		],
	];

	protected $indexes = [
		'product_id',
		'product_attribute_id',
		'product_attribute_option_id',
		'product_attribute_value_text',
	];

	protected $foreign_keys = [
		'product_id' => ['product', 'product_id'],
		'product_attribute_id' => ['product_attribute', 'product_attribute_id'],
		'product_attribute_option_id' => ['product_attribute_option', 'product_attribute_option_id'],
	];
}
