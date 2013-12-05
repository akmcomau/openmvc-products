<div class="row">
	<?php foreach($products as $product) { ?>
		<div class="col-md-4 col-sm-6 product-cell">
			<a href="<?php echo $product->getUrl($this->url); ?>" class="product">
				<h4><?php echo htmlspecialchars($product->name); ?></h4>
				<?php
					$images = $product->getImages();
					$image  = NULL;
					if (count($images)) {
						$image = $images[0];
						?><img src="<?php echo $image->getThumbnailUrl(); ?>" /><?php
					}
				?>
			</a>
			<div class="add-to-cart">
				<span class="price"><?php echo  money_format('%n', $product->sell); ?></span>
				<a href="" class="btn btn-primary"><?php echo $text_add_to_cart; ?></a>
			</div>
		</div>
	<?php } ?>
</div>
