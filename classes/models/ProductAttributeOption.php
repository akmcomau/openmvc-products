<?php

namespace modules\products\classes\models;

use core\classes\Model;

class ProductAttributeOption extends Model {

	protected $table       = 'product_attribute_option';
	protected $primary_key = 'product_attribute_option_id';
	protected $columns     = [
		'product_attribute_option_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'product_attribute_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_attribute_option_name' => [
			'data_type'      => 'text',
			'data_length'    => 256,
			'null_allowed'   => FALSE,
		],
		'product_attribute_option_value' => [
			'data_type'      => 'text',
			'data_length'    => 256,
			'null_allowed'   => FALSE,
		],
		'product_attribute_option_ordering' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
	];

	protected $indexes = [
		'site_id',
		'product_attribute_id',
	];

	protected $foreign_keys = [
		'product_attribute_id'  => ['product_attribute', 'product_attribute_id'],
	];
}
