<article class="post-card post-card-wrapper"  >
    <div class="post-thumbnail">
        <img src="<?= get_the_post_thumbnail_url(); ?>" alt="">
    </div>
    <div class="post-body">
        <h3 class="post-title">
            <a href="<?= get_permalink() ?>" >
                <?= wp_trim_words(get_the_title(), 10); ?>
            </a>
        </h3>
        <div class="post-footer">
            <p class="post-date">
                <?= the_date(); ?>
            </p>
            <span class="icon-arrow-bullets icon-footer" aria-hidden="true"> </span>
        </div>
    </div>
</article>