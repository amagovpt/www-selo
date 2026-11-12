<li class="container destaque pb-lg-5">
    <div class="row">
        <div class="col-12 col-md-6 align-self-center">
            <a href="<?php echo get_the_permalink(); ?>">
                <h2 class="h2"><?php echo get_the_title(); ?></h2>
            </a>
            <p class="last-update py-2 data"><?= get_the_modified_date() ?></p>
            <p><?= wp_trim_words(get_the_excerpt(), 30) ?></p>
        </div>
        <div class="col-12 col-md-6 align-self-center destaque-imagem">
            <?php echo get_the_post_thumbnail(); ?>
        </div>
    </div>
</li>
