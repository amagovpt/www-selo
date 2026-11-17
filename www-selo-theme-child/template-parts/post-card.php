<article class="post-card post-card-wrapper"  >
    <div class="post-thumbnail<?= has_post_thumbnail() ? '' : ' post-thumbnail--vazia'; ?>">
        <?php if ( has_post_thumbnail() ) : ?>
            <img src="<?= esc_url( get_the_post_thumbnail_url( null, 'medium_large' ) ); ?>" alt="">
        <?php endif; ?>
    </div>
    <div class="post-body">
        <h3 class="post-title">
            <a href="<?= esc_url( get_permalink() ); ?>">
                <?= esc_html( wp_trim_words( get_the_title(), 10 ) ); ?>
            </a>
        </h3>
        <div class="post-footer">
            <p class="post-date">
                <?php // get_the_date: the_date() não repete a data de artigos do mesmo dia. ?>
                <time datetime="<?= esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?= esc_html( get_the_date() ); ?></time>
            </p>
            <span class="icon-arrow-bullets icon-footer" aria-hidden="true"> </span>
        </div>
    </div>
</article>