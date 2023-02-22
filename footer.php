<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>

</div>

<?php wp_footer(); ?>

<footer>
    <div class="container">
        <?php 
        wp_nav_menu(
            array(
                'theme_location'  => 'footer',
                'menu_class' => 'footer-menu',
            )
        ); ?>
        
        <div class="footer-logos">
            <?php dynamic_sidebar( 'Footer' ); ?>
        </div>

        <div class="logo-selo">
            <img src="<?php echo get_template_directory_uri() . '/img/selo-ouro.svg'; ?>" alt="">
        </div>

        <?php dynamic_sidebar( 'Copyright' ); ?>
    </div>
</footer>

</body>

</html>