<div class="<?php echo $page_class; ?>">
	<br />
	<div class="row">
		<div class="col-md-12">
			<form class="admin-search-form" method="get" id="form-products-search">
				<div class="widget">
					<div class="widget-header">
						<h3><?php echo $text_products_search; ?></h3>
					</div>
					<div class="widget-content">
						<div class="row">
							<div class="col-md-12">
								<div class="row">
									<div class="col-md-2 col-sm-3 title-2column"><?php echo $text_search; ?></div>
									<div class="col-md-9 col-sm-9">
										<input type="text" class="form-control" name="search_query" value="<?php echo htmlspecialchars($form->getValue('search_query')); ?>" />
										<?php echo $form->getHtmlErrorDiv('search_query'); ?>
									</div>
								</div>
							</div>
						</div>
						<hr class="separator-2column" />
						<div class="row">
							<div class="col-md-6">
								<div class="row">
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
							</div>
							<div class="hidden-lg hidden-md">
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
						<div class="align-right">
							<button type="submit" class="btn btn-primary" name="form-products-search-submit"><?php echo $text_search; ?></button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<br />
	<div class="row">
		<div class="col-md-12">
			<form class="admin-search-form" method="get" id="form-products-search">
				<div class="widget">
					<div class="widget-header">
						<h3><?php echo $text_products_search_results; ?></h3>
					</div>
					<div class="widget-content">
						<?php if ($pagination->getMaxPage() > 1) { ?>
							<div class="pagination">
								<?php echo $pagination->getPageLinks(); ?>
							</div>
						<?php } ?>
						<div class="clearfix"></div>
						<?php echo $products->render(); ?>
						<?php if ($pagination->getMaxPage() > 1) { ?>
							<div class="pagination">
								<?php echo $pagination->getPageLinks(); ?>
							</div>
						<?php } ?>
						<div class="clearfix"></div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
