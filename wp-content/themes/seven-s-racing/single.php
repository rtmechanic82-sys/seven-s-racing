<?php get_header(); ?><main><section class="page-hero"><div class="container"><p class="eyebrow"><?php echo esc_html(get_the_date()); ?></p><h1 class="display"><?php the_title(); ?></h1></div></section><article class="content-section"><div class="container" style="max-width:820px"><?php while(have_posts()):the_post();the_content();endwhile;?></div></article></main><?php get_footer(); ?>

