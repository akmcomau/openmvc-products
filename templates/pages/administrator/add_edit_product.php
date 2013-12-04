<div class="container">
	<div class="row">
		<div class="col-md-12">
			<form class="admin-form" method="post" id="form-product">
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
</script>
