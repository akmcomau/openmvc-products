<?php

namespace modules\products\classes\models;

use core\classes\Model;
use modules\checkout\classes\models\CheckoutInterface;

class CheckoutProduct extends Model implements CheckoutInterface {

	protected $table       = 'checkout_product';
	protected $primary_key = 'checkout_product_id';
	protected $columns     = [
		'checkout_product_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'checkout_item_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
	];

	protected $indexes = [
		'checkout_item_id',
		'product_id',
	];

	protected $foreign_keys = [
		'checkout_item_id' => ['checkout_item', 'checkout_item_id'],
		'product_id' => ['product', 'product_id'],
	];

	public function getCheckoutItem() {
		if (!isset($this->objects['checkout_item'])) {
			$this->objects['checkout_item'] = $this->getModel('modules\checkout\classes\models\CheckoutItem')->get([
				'id' => $this->checkout_item_id
			]);
		}

		return $this->objects['checkout_item'];
	}

	public function getSellPrice() {
		$item = $this->getCheckoutItem();
		if ($item) {
			return $item->sell_price;
		}
		else {
			return 0;
		}
	}

	public function getCostPrice() {
		$item = $this->getCheckoutItem();
		if ($item) {
			return $item->cost_price;
		}
		else {
			return 0;
		}
	}
}
