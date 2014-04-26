<?php

namespace modules\products\classes\models;

use core\classes\exceptions\ModelException;
use core\classes\Template;
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
			'data_length'    => 64,
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
		'product_attribute_required_admin' => [
			'data_type'      => 'bool',
			'null_allowed'   => FALSE,
			'default_value'  => 'TRUE',
		],
		'product_attribute_user_selectable' => [
			'data_type'      => 'bool',
			'null_allowed'   => FALSE,
			'default_value'  => 'FALSE',
		],
		'product_attribute_required_user' => [
			'data_type'      => 'bool',
			'null_allowed'   => FALSE,
			'default_value'  => 'FALSE',
		],
		'product_attribute_visible' => [
			'data_type'      => 'bool',
			'null_allowed'   => FALSE,
			'default_value'  => 'TRUE',
		],
	];

	protected $indexes = [
		'site_id',
		'product_attribute_visible',
	];

	public function insert() {
		if (!$this->ordering) {
			$sql = "SELECT MAX(product_attribute_ordering) FROM product_attribute";
			$ordering = $this->database->queryValue($sql);
			$this->ordering = $ordering + 1;
		}

		parent::insert();
	}

	public function delete() {
		$sql = "DELETE FROM product_attribute_value WHERE product_attribute_id=".(int)$this->id;
		$this->database->executeQuery($sql);

		$sql = "DELETE FROM product_attribute_option WHERE product_attribute_id=".(int)$this->id;
		$this->database->executeQuery($sql);

		parent::delete();
	}

	public function getFormType() {
		$type_array = explode('|', $this->type);
		switch ($type_array[0]) {
			case 'category':
				return 'integer';
				break;

			case 'text':
				return 'string';
				break;

			case 'integer':
				return 'integer';
				break;

			case 'float':
				return 'float';
				break;
		}
		throw new ModelException('Cannot get form type of attribute type: '.$this->type);
	}

	public function getValueOption($value) {
		$type_array = explode('|', $this->type);
		switch ($type_array[0]) {
			case '':
				return '';
				break;
		}
		return NULL;
	}

	public function getValueCategory($value) {
		$type_array = explode('|', $this->type);
		switch ($type_array[0]) {
			case 'category':
				return (int)$value;
				break;
		}
		return NULL;
	}

	public function getValueText($value) {
		$type_array = explode('|', $this->type);
		switch ($type_array[0]) {
			case 'text':
				return $value;
				break;

			case 'integer':
				return $value;
				break;

			case 'float':
				return $value;
				break;
		}
		return NULL;
	}

	public function renderAdmin($form, $language, $element_name, $value) {
		$type_array = explode('|', $this->type);
		switch ($type_array[0]) {
			case 'category':
				return $this->renderCategory($form, $language, $element_name, $value, $type_array[1]);
				break;

			case 'text':
				return $this->renderText($form, $language, $element_name, $value);
				break;

			case 'float':
				return $this->renderFloat($form, $language, $element_name, $value);
				break;

			case 'integer':
				return $this->renderInteger($form, $language, $element_name, $value);
				break;
		}
	}

	protected function renderCategory($form, $language, $element_name, $selected, $class) {
		$category = $this->getModel($class);
		$categories = $category->getAsOptions([$this->config->siteConfig()->site_id]);

		$data = [
			'form' => $form,
			'language' => $language,
			'element_name' => $element_name,
			'selected' => $selected,
			'categories' => $categories,
		];

		$filename = 'elements'.DS.'category.php';
		$template = new Template($this->config, $language, $filename, $data, 'modules'.DS.'products');
		return $template->render();
	}

	protected function renderText($form, $language, $element_name, $value) {
		$data = [
			'form' => $form,
			'language' => $language,
			'element_name' => $element_name,
			'value' => $value,
		];

		$filename = 'elements'.DS.'text.php';
		$template = new Template($this->config, $language, $filename, $data, 'modules'.DS.'products');
		return $template->render();
	}

	protected function renderInteger($form, $language, $element_name, $value) {
		$data = [
			'form' => $form,
			'language' => $language,
			'element_name' => $element_name,
			'value' => $value,
		];

		$filename = 'elements'.DS.'integer.php';
		$template = new Template($this->config, $language, $filename, $data, 'modules'.DS.'products');
		return $template->render();
	}

	protected function renderFloat($form, $language, $element_name, $value) {
		$data = [
			'form' => $form,
			'language' => $language,
			'element_name' => $element_name,
			'value' => $value,
		];

		$filename = 'elements'.DS.'float.php';
		$template = new Template($this->config, $language, $filename, $data, 'modules'.DS.'products');
		return $template->render();
	}
}
