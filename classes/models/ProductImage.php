<?php

namespace modules\products\classes\models;

use core\classes\Model;

class ProductImage extends Model {

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

	protected function makeThumbnails($updir, $img) {
		$max_image_width  = 200;
		$max_image_height = 150;
		$thumb_beforeword = "tn-";
		$arr_image_details = getimagesize("$updir" . "$img"); // pass id to thumb name
		$original_width = $arr_image_details[0];
		$original_height = $arr_image_details[1];
		if ($original_width > $max_image_width || $original_height > $max_image_height) {
			$ratio = 0;
			$ratio_x = $max_image_width  / $original_width;
			$ratio_y = $max_image_height / $original_height;
			if ($ratio_x < $ratio_y) {
				$ratio = $ratio_x;
			}
			else {
				$ratio = $ratio_y;
			}
			$new_width = (int)($ratio * $original_width);
			$new_height = (int)($ratio * $original_height);
		}

		if ($arr_image_details[2] == 1) {
			$imgt = "ImageGIF";
			$imgcreatefrom = "ImageCreateFromGIF";
		}
		if ($arr_image_details[2] == 2) {
			$imgt = "ImageJPEG";
			$imgcreatefrom = "ImageCreateFromJPEG";
		}
		if ($arr_image_details[2] == 3) {
			$imgt = "ImagePNG";
			$imgcreatefrom = "ImageCreateFromPNG";
		}
		if ($imgt) {
			$old_image = $imgcreatefrom("$updir" . "$img");
			$new_image = imagecreatetruecolor($new_width, $new_height);
			imagecopyresized($new_image, $old_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
			$imgt($new_image, "$updir" . "$thumb_beforeword" . "$img");
		}
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
