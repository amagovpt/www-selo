<li class="facilitador-wrapper">
    <div class="foto">
        <?php
        if(has_post_thumbnail(get_the_ID())) {
            the_post_thumbnail("thumbnail");
        }
        ?>
    </div>
    <div class="facilitador-text">
        <div class="nome h5"><?= the_title(); ?></div>
        <div class="desc mb-3"><?= wp_trim_words(get_the_excerpt(), 30); ?></div>
        <div class="redes">
            <?php
            $linkedin = get_field("facilitador_linkedin");
            if($linkedin && !empty($linkedin)) {
                ?>
                <ul class="d-flex list-inline mb-0">
                    <li>
                        <a class="facilitador-rs-link" href="<?= $linkedin ?>" target="_blank" title="Ir para o Linkedin do facilitador">
                            <span class="icon-share-linkedin" aria-hidden="true"></span>
                        </a>
                    </li>
                </ul>
            <?php } ?>
            <div class="ms-lg-auto">
                <a class="ama btn btn-secondary" href="<?= get_permalink() ?>">Ver biografia<div class="icon-arrow-bullets list_arrow_link" aria-hidden="true"></div></a>
            </div>
        </div>
    </div>
</li>