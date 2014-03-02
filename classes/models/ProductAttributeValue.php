<?php

namespace modules\products\classes\models;

use core\classes\exceptions\ModelException;
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
		'product_attribute_category_id' => [
			'data_type'      => 'int',
			'null_allowed'   => TRUE,
		],
		'product_attribute_value_text' => [
			'data_type'      => 'text',
			'null_allowed'   => TRUE,
		],
	];

	protected $indexes = [
		'product_id',
		'product_attribute_id',
		'product_attribute_option_id',
		'product_attribute_value_text',
		'product_attribute_category_id',
	];

	protected $foreign_keys = [
		'product_id' => ['product', 'product_id'],
		'product_attribute_id' => ['product_attribute', 'product_attribute_id'],
		'product_attribute_option_id' => ['product_attribute_option', 'product_attribute_option_id'],
	];

	public function getProductAttribute() {
		if (isset($this->objects['product_attribute'])) {
			return $this->objects['product_attribute'];
		}

		$this->objects['product_attribute'] = $this->getModel('\modules\products\classes\models\ProductAttribute')->get([
			'id' => $this->product_attribute_id
		]);

		return $this->objects['product_attribute'];
	}

	public function getValue() {
		$type_array = explode('|', $this->getProductAttribute()->type);
		switch ($type_array[0]) {
			case 'category':
				return $this->product_attribute_category_id;
				break;

			case 'text':
				return $this->product_attribute_value_text;
				break;
		}
		throw new ModelException('Cannot get form type of attribute type: '.$this->type);
	}
}
