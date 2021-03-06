<div class="<?php echo $page_class; ?> view-product">
	<h1><?php echo htmlspecialchars($product->name); ?></h1>
	<div class="row">
		<div class="col-md-3 col-sm-3 image">
			<?php
				$images = $product->getImages();
				$image  = NULL;
				if (count($images)) {
					$image = $images[0];
					?><img class="img-responsive" src="<?php echo $image->getUrl(); ?>" /><?php
				}
			?>
		</div>
		<div class="col-md-9 col-sm-9 details">
			<hr class="separator-2column" />
			<div class="row">
				<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_name; ?>:</div>
				<div class="col-md-9 col-sm-9 "><?php echo htmlspecialchars($product->name); ?></div>
			</div>
			<hr class="separator-2column" />
			<div class="row">
				<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_sku; ?>:</div>
				<div class="col-md-9 col-sm-9 "><?php echo htmlspecialchars($product->sku); ?></div>
			</div>
			<hr class="separator-2column" />
			<div class="row">
				<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_model_no; ?>:</div>
				<div class="col-md-9 col-sm-9 "><?php echo htmlspecialchars($product->model); ?></div>
			</div>
			<hr class="separator-2column" />
			<?php if ($product->getBrandName()) { ?>
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_brand; ?>:</div>
					<div class="col-md-9 col-sm-9 "><?php echo htmlspecialchars($product->getBrandName()); ?></div>
				</div>
				<hr class="separator-2column" />
			<?php } ?>
			<?php if ($product->getCategoryName()) { ?>
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_category; ?>:</div>
					<div class="col-md-9 col-sm-9 "><?php echo htmlspecialchars($product->getCategoryName()); ?></div>
				</div>
				<hr class="separator-2column" />
			<?php } ?>
			<?php foreach ($product->getAttributes() as $attribute) { ?>
				<?php if (!empty($attribute->product_attribute_value_text)) { ?>
					<div class="row">
						<div class="col-md-3 col-sm-3 title-2column"><?php echo $attribute->getProductAttribute()->name; ?>:</div>
						<div class="col-md-9 col-sm-9 "><?php echo htmlspecialchars($attribute->product_attribute_value_text); ?></div>
					</div>
					<hr class="separator-2column" >
				<?php } ?>
			<?php } ?>
			<?php if ($product->description) { ?>
				<div class="row">
					<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_description; ?>:</div>
					<div class="col-md-9 col-sm-9 "><?php echo $product->description; ?></div>
				</div>
				<hr class="separator-2column" />
			<?php } ?>
			<div class="price"><?php echo  money_format('%n', $product->getSellPrice()); ?></div>
			<div class="button"><a href="<?php echo $this->url->getUrl('Cart', 'add', ['product', $product->id]); ?>" class="btn btn-primary"><?php echo $text_add_to_cart; ?></a></div>
		</div>
	</div>
</div>
