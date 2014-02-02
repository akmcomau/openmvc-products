<div class="container">
	<div class="row">
		<div class="col-md-12">
			<form class="admin-form" method="post" id="form-product" enctype="multipart/form-data">
				<div class="widget">
					<div class="widget-header">
						<h3><?php
							if ($is_add_page) echo $text_product_add_header;
						 	else echo $text_product_update_header;
						?></h3>
					</div>
					<div class="widget-content">
						<div class="row">
							<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_name; ?></div>
							<div class="col-md-9 col-sm-9 ">
								<input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product->name); ?>" />
								<?php echo $form->getHtmlErrorDiv('name'); ?>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_sku; ?></div>
							<div class="col-md-9 col-sm-9 ">
								<input type="text" class="form-control" name="sku" value="<?php echo htmlspecialchars($product->sku); ?>" />
								<?php echo $form->getHtmlErrorDiv('sku'); ?>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_model; ?></div>
							<div class="col-md-9 col-sm-9 ">
								<input type="text" class="form-control" name="model" value="<?php echo htmlspecialchars($product->model); ?>" />
								<?php echo $form->getHtmlErrorDiv('model'); ?>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_brand; ?></div>
							<div class="col-md-9 col-sm-9 ">
								<select name="brand" class="form-control">
									<option value=""></option>
									<?php foreach ($brands as $value => $text) { ?>
										<option value="<?php echo $value; ?>" <?php if ($value == $product->brand_id) echo 'selected="selected"'; ?>><?php echo $text; ?></option>
									<?php } ?>
								</select>
								<?php echo $form->getHtmlErrorDiv('brand'); ?>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_category; ?></div>
							<div class="col-md-9 col-sm-9 ">
								<select name="category" class="form-control">
									<option value=""></option>
									<?php foreach ($categories as $value => $text) { ?>
										<option value="<?php echo $value; ?>" <?php if ($product->getCategory() && $value == $product->getCategory()->id) echo 'selected="selected"'; ?>><?php echo $text; ?></option>
									<?php } ?>
								</select>
								<?php echo $form->getHtmlErrorDiv('category'); ?>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_featured; ?></div>
							<div class="col-md-9 col-sm-9 ">
								<select class="form-control" name="featured">
									<option value="1" <?php if(!is_null($product->featured) && $product->featured) echo 'selected="selected"'; ?>><?php echo $text_yes; ?></option>
									<option value="0" <?php if(!is_null($product->featured) && !$product->featured) echo 'selected="selected"'; ?>><?php echo $text_no; ?></option>
								</select>
								<?php echo $form->getHtmlErrorDiv('featured'); ?>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_active; ?></div>
							<div class="col-md-9 col-sm-9 ">
								<select class="form-control" name="active">
									<option value="1" <?php if(!is_null($product->active) && $product->active) echo 'selected="selected"'; ?>><?php echo $text_yes; ?></option>
									<option value="0" <?php if(!is_null($product->active) && !$product->active) echo 'selected="selected"'; ?>><?php echo $text_no; ?></option>
								</select>
								<?php echo $form->getHtmlErrorDiv('active'); ?>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_cost_price; ?></div>
							<div class="col-md-9 col-sm-9 ">
								<input type="text" class="form-control" name="cost" value="<?php echo htmlspecialchars($product->cost); ?>" />
								<?php echo $form->getHtmlErrorDiv('cost'); ?>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_sell_price; ?></div>
							<div class="col-md-9 col-sm-9 ">
								<input type="text" class="form-control" name="sell" value="<?php echo htmlspecialchars($product->sell); ?>" />
								<?php echo $form->getHtmlErrorDiv('sell'); ?>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_description; ?></div>
							<div class="col-md-9 col-sm-9 ">
								<textarea class="form-control ckeditor" name="description"><?php echo htmlspecialchars($product->description); ?></textarea>
								<?php echo $form->getHtmlErrorDiv('description'); ?>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_images; ?></div>
							<div class="col-md-9 col-sm-9 ">
								<strong><?php echo $text_upload_images; ?></strong>
								<select name="num_images" id="num_images" class="form-control" onchange="update_uploads();">
									<?php for($i=1; $i<=10; $i++) {
										echo "<option value=\"$i\">$i</option>";
									} ?>
								</select>
								<br />
								<div id="image_uploads"></div>
								<br />
								<strong><?php echo $text_current_images; ?></strong>
								<div id="current_images" class="row">
									<?php foreach ($product->getImages() as $image) {
										if ($image->id) {
											?>
											<div class="col-md-4 col-sm-6 align-center">
												<img src="<?php echo $image->getThumbnailUrl(); ?>" />
												<br />
												<label>
													<input type="checkbox" name="delete_images[]" value="<?php echo $image->id; ?>" />
													<?php echo $text_delete; ?>
												</label>
											</div>
											<?php
										}
									} ?>
								</div>
							</div>
						</div>
						<hr class="separator-2column" />

						<?php foreach ($product->getAllAttributes() as $attribute) { ?>
							<div class="row">
								<div class="col-md-3 col-sm-3 title-2column"><?php echo $attribute->name; ?></div>
								<div class="col-md-9 col-sm-9 ">
									<?php echo $attribute->renderAdmin($form, $this->language, 'attribute_'.$attribute->id, $product->getAttributeValue($attribute)); ?>
								</div>
							</div>
							<hr class="separator-2column" />
						<?php } ?>

						<div class="col-md-12 align-center">
							<button class="btn btn-primary" type="submit" name="form-product-submit"><?php
								if ($is_add_page) echo $text_product_add_button;
								else echo $text_product_update_button;
							?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	<?php echo $form->getJavascriptValidation(); ?>

	function update_uploads() {
		var num_images = $('#num_images').val();

		var images = [];
		$('#image_uploads').find('input').each(function() {
			images[images.length] = $(this);
		});

		$('#image_uploads').html('');
		for (var i=0; i<num_images; i++) {
			var input = $('<input type="file" />');
			if (typeof(images[i]) != 'undefined') {
				input = images[i];
			}
			else {
				input.attr('name', 'image[]');
			}
			$('#image_uploads').append(input);
			$('#image_uploads').append($('<br />'));
		}
	}
	update_uploads();
</script>
