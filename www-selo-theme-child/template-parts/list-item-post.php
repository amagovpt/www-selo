<li class="ama list-item col-lg-8 mt-5">
    <hr class="mb-5">
        <h2 class="h4 titulo"><a href="<?= get_permalink() ?>" class="ligacao"><?php the_title(); ?></a></h2>
    <p class="last-update py-2 data">
        <?= get_the_modified_date() ?>
    </p>
    <p class="py-2 descricao">
        <?= wp_trim_words(get_the_excerpt(), 30) ?>
    </p>
</li>