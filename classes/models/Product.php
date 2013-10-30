<?php

namespace modules\products\classes\models;

use core\classes\Model;

class Product extends Model {

	protected $table       = 'product';
	protected $primary_key = 'product_id';
	protected $columns     = [
		'product_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'site_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_active' => [
			'data_type'      => 'bool',
			'null_allowed'   => FALSE,
			'default_value'  => 'TRUE',
		],
		'product_featured' => [
			'data_type'      => 'bool',
			'null_allowed'   => FALSE,
			'default_value'  => 'FALSE',
		],
		'product_sku' => [
			'data_type'      => 'text',
			'data_length'    => 128,
			'null_allowed'   => FALSE,
		],
		'product_model' => [
			'data_type'      => 'text',
			'data_length'    => 128,
			'null_allowed'   => FALSE,
		],
		'product_name' => [
			'data_type'      => 'text',
			'data_length'    => 128,
			'null_allowed'   => FALSE,
		],
		'product_brand_id' => [
			'data_type'      => 'text',
			'data_length'    => 128,
			'null_allowed'   => FALSE,
		],
		'product_description' => [
			'data_type'      => 'text',
			'data_length'    => 256,
			'null_allowed'   => FALSE,
		],
		'product_cost' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
		'product_sell' => [
			'data_type'      => 'numeric',
			'data_length'    => [6, 4],
			'null_allowed'   => FALSE,
		],
	];

	protected $indexes = [
		'site_id',
		'product_active',
		'product_featured',
		'product_sku',
		'product_model',
	];

	protected $foreign_keys = [
		'product_brand_id'  => ['product_brand', 'product_brand_id'],
	];
}
