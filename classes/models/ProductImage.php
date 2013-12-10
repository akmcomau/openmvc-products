<?php

namespace modules\products\classes\models;

use core\classes\Model;
use core\classes\traits\Thumbnails;

class ProductImage extends Model {
	use Thumbnails;

	protected $tmp_name = NULL;
	protected $original_filename = NULL;

	protected $table       = 'product_image';
	protected $primary_key = 'product_image_id';
	protected $columns     = [
		'product_image_id' => [
			'data_type'      => 'int',
			'auto_increment' => TRUE,
			'null_allowed'   => FALSE,
		],
		'product_id' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_image_name' => [
			'data_type'      => 'text',
			'data_length'    => '256',
			'null_allowed'   => FALSE,
		],
		'product_image_filename' => [
			'data_type'      => 'text',
			'data_length'    => '256',
			'null_allowed'   => FALSE,
		],
		'product_image_width' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_image_height' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
		'product_image_ordering' => [
			'data_type'      => 'int',
			'null_allowed'   => FALSE,
		],
	];

	protected $indexes = [
		'product_id',
	];

	protected $foreign_keys = [
		'product_id' => ['product', 'product_id'],
	];

	public function getOriginalFilename() {
		return $this->original_filename;
	}

	public function setOriginalFilename($file) {
		$this->original_filename = $file;
	}

	public function getTmpName() {
		return $this->tmp_name;
	}

	public function setTmpName($file) {
		$this->tmp_name = $file;
	}

	public function getUrl() {
		$site = $this->config->siteConfig();
		$language = $site->language;
		$theme = $site->theme;
		$path = '/sites/'.$site->namespace.'/themes/product_images/'.$this->product_id.'/';
		return $path.$this->filename;
	}

	public function getThumbnailUrl() {
		$site = $this->config->siteConfig();
		$language = $site->language;
		$theme = $site->theme;
		$path = '/sites/'.$site->namespace.'/themes/product_images/'.$this->product_id.'/';
		return $path.'tn-'.$this->filename;
	}

	public function upload() {
		// make sure the root dir exists
		$site = $this->config->siteConfig();
		$language = $site->language;
		$theme = $site->theme;
		$root = __DIR__.DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS;
		$site_path = $root.'sites'.DS.$site->namespace.DS.'themes'.DS.'product_images'.DS;
		if (!file_exists($site_path)) {
			mkdir($site_path);
		}

		// make sure the product dir exists
		$product_path = $site_path.$this->product_id.DS;
		if (!file_exists($product_path)) {
			mkdir($product_path);
		}

		// insert the record
		$size = getimagesize($this->tmp_name);
		$this->name     = explode('.', $this->original_filename)[0];
		$this->filename = '';
		$this->width    = $size[0];
		$this->height   = $size[1];
		$this->insert();

		// update the filename
		$this->filename = $this->id.'-'.strtolower($this->original_filename);
		$this->update();

		// copy the file
		$filename = $product_path.$this->filename;
		copy($this->tmp_name, $filename);
		$this->makeThumbnails($product_path, $this->filename);
	}

	public function delete() {
		$site = $this->config->siteConfig();
		$language = $site->language;
		$theme = $site->theme;
		$root = __DIR__.DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS;
		$site_path = $root.'sites'.DS.$site->namespace.DS.'themes'.DS.'product_images'.DS;
		$product_path = $site_path.$this->product_id.DS;

		// remove the file
		if (file_exists($product_path.$this->filename))
			unlink($product_path.$this->filename);
		if (file_exists($product_path.'tn-'.$this->filename))
			unlink($product_path.'tn-'.$this->filename);

		parent::delete();
	}
}
