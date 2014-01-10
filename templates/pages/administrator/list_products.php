<div class="container">
	<div class="row">
		<div class="col-md-12">
			<form class="admin-search-form" method="get" id="form-products-search">
				<div class="widget">
					<div class="widget-header">
						<h3><?php echo $text_products_search; ?></h3>
					</div>
					<div class="widget-content">
						<div class="row">
							<div class="col-md-6">
								<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_sku; ?></div>
								<div class="col-md-9 col-sm-9 ">
									<input type="text" class="form-control" name="search_sku" value="<?php echo htmlspecialchars($form->getValue('search_sku')); ?>" />
									<?php echo $form->getHtmlErrorDiv('search_sku'); ?>
								</div>
							</div>
							<div class="col-md-6 visible-xs">
								<hr class="separator-2column" />
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_model; ?></div>
									<div class="col-md-9 col-sm-9 ">
										<input type="text" class="form-control" name="search_model" value="<?php echo htmlspecialchars($form->getValue('search_model')); ?>" />
										<?php echo $form->getHtmlErrorDiv('search_model'); ?>
									</div>
								</div>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-6">
								<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_name; ?></div>
								<div class="col-md-9 col-sm-9 ">
									<input type="text" class="form-control" name="search_name" value="<?php echo htmlspecialchars($form->getValue('search_name')); ?>" />
									<?php echo $form->getHtmlErrorDiv('search_name'); ?>
								</div>
							</div>
							<div class="col-md-6 visible-xs">
								<hr class="separator-2column" />
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_brand; ?></div>
									<div class="col-md-9 col-sm-9 ">
										<select name="search_brand" class="form-control">
											<option value=""></option>
											<?php foreach ($brands as $value => $text) { ?>
												<option value="<?php echo $value; ?>" <?php if ($value == $form->getValue('search_brand')) echo 'selected="selected"'; ?>><?php echo $text; ?></option>
											<?php } ?>
										</select>
										<?php echo $form->getHtmlErrorDiv('search_brand'); ?>
									</div>
								</div>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-6">
								<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_category; ?></div>
								<div class="col-md-9 col-sm-9 ">
									<select name="search_category" class="form-control">
										<option value=""></option>
										<?php foreach ($categories as $value => $text) { ?>
											<option value="<?php echo $value; ?>" <?php if ($value == $form->getValue('search_category')) echo 'selected="selected"'; ?>><?php echo $text; ?></option>
										<?php } ?>
									</select>
									<?php echo $form->getHtmlErrorDiv('search_category'); ?>
								</div>
							</div>
							<div class="col-md-6 visible-xs">
								<hr class="separator-2column" />
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="col-md-3 col-sm-3 title-2column"><?php echo $text_active; ?></div>
									<div class="col-md-9 col-sm-9 ">
										<select name="search_active" class="form-control">
											<option value=""></option>
											<option value="1" <?php if (strlen($form->getValue('search_active')) && (int)$form->getValue('search_active') == 1) echo 'selected="selected"'; ?>><?php echo $text_yes; ?></option>
											<option value="0" <?php if (strlen($form->getValue('search_active')) && (int)$form->getValue('search_active') == 0) echo 'selected="selected"'; ?>><?php echo $text_no; ?></option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="align-right">
							<button type="submit" class="btn btn-primary" name="form-products-search-submit"><?php echo $text_search; ?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="widget">
				<div class="widget-header">
					<h3><?php echo $text_products_search_results; ?></h3>
				</div>
				<div class="widget-content">
					<div class="pagination">
						<?php echo $pagination->getPageLinks(); ?>
					</div>
					<form action="<?php echo $this->url->getUrl('administrator/Products', 'deleteProducts'); ?>" method="post">
						<table class="table">
							<tr>
								<th></th>
								<th nowrap="nowrap"><?php echo $text_sku; ?> <?php echo $pagination->getSortUrls('sku'); ?></th>
								<th nowrap="nowrap"><?php echo $text_model; ?> <?php echo $pagination->getSortUrls('model'); ?></th>
								<th nowrap="nowrap"><?php echo $text_name; ?> <?php echo $pagination->getSortUrls('name'); ?></th>
								<th nowrap="nowrap"><?php echo $text_cost_price; ?> <?php echo $pagination->getSortUrls('cost_price'); ?></th>
								<th nowrap="nowrap"><?php echo $text_sell_price; ?> <?php echo $pagination->getSortUrls('sell_price'); ?></th>
								<th nowrap="nowrap"><?php echo $text_active; ?> <?php echo $pagination->getSortUrls('active'); ?></th>
								<th></th>
							</tr>
							<?php foreach ($products as $product) { ?>
							<tr>
								<td class="select"><input type="checkbox" name="selected[]" value="<?php echo $product->id; ?>" /></td>
								<td><?php echo $product->sku; ?></td>
								<td><?php echo $product->model; ?></td>
								<td><?php echo $product->name; ?></td>
								<td><?php echo strlen($product->cost) ? money_format('%n', $product->cost) : ''; ?></td>
								<td><?php echo money_format('%n', $product->sell); ?></td>
								<td><?php echo $product->active ? $text_yes : $text_no; ?></td>
								<td>
									<a href="<?php echo $this->url->getUrl('administrator/Products', 'editProduct', [$product->id]); ?>" class="btn btn-primary"><i class="fa fa-edit" title="<?php echo $text_edit; ?>"></i></a>
								</td>
							</tr>
							<?php } ?>
						</table>
						<button type="submit" class="btn btn-primary" name="form-product-type-list-submit" onclick="return deleteSelected();"><?php echo $text_delete_selected; ?></button>
					</form>
					<div class="pagination">
						<?php echo $pagination->getPageLinks(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	<?php echo $form->getJavascriptValidation(); ?>
	<?php echo $message_js; ?>
</script>
