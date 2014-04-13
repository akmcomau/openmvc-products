<div class="<?php echo $page_div_class; ?>">
	<h1><?php echo $text_viewed_products; ?></h1>
	<?php if ($products->getProductCount()) { ?>
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
		<?php
	}
	if ($products->getProductCount() == 0 && $categories->getCategoryCount() == 0) { ?>
		<h2><?php echo $this->language->get('no_products_found', [$group_name]); ?></h2>
	<?php } ?>
</div>
