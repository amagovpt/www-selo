<?php
  /**
   * AMA Card Template.
   *
   * @var  array  $attributes Block attributes.
   * @var  array  $block Block data.
   * @var  string $context Preview context [editor,frontend].
   */
 
  add_filter( $block["slug"]."/frontend_allow_wrapper", "__return_false" );
  // Lê o SVG do disco (pelo ID do anexo ou pelo caminho em uploads), nunca por HTTP:
  // um pedido HTTP bloqueia a página se o domínio guardado no URL não responder.
  $svg = '';
  $icon_path = '';
  if ( ! empty( $attributes["icon"]["id"] ) ) {
    $icon_path = get_attached_file( (int) $attributes["icon"]["id"] );
  }
  if ( ( ! $icon_path || ! is_readable( $icon_path ) ) && ! empty( $attributes["icon"]["url"] ) ) {
    $uploads = wp_get_upload_dir();
    $rel = strstr( (string) wp_parse_url( $attributes["icon"]["url"], PHP_URL_PATH ), '/wp-content/uploads/' );
    if ( $rel ) {
      $icon_path = $uploads['basedir'] . substr( $rel, strlen( '/wp-content/uploads' ) );
    }
  }
  if ( $icon_path && is_readable( $icon_path ) && 'svg' === strtolower( pathinfo( $icon_path, PATHINFO_EXTENSION ) ) ) {
    $real = realpath( $icon_path );
    if ( $real && 0 === strpos( $real, realpath( wp_get_upload_dir()['basedir'] ) ) ) {
      $svg = (string) file_get_contents( $real );
    }
  }

  $attributesOfSvg = ['stroke','fill', 'height', 'width'];

  foreach ($attributesOfSvg as $attr) {
    $pattern = '/\s*' . preg_quote($attr, '/') . '\s*=\s*(".*?"|\'.*?\'|[^"\'>\s]+)/i';
    $svg = preg_replace($pattern, '', $svg);
  }

  $svg = preg_replace('/\s{2,}/', ' ', $svg);

// Card BODY classes
  $card_text_classes = [];
  $card_bg_classes = [];
  $tag="div";
  if(empty($attributes["html_tag"])) {
      $tag = "div";
  }
  ?>
<div class="step-card-container">               
    <div class="step-card-icon-container <?= $attributes["card_color"]["value"] ?>">
        <?= $svg ?>
    </div>
    <h3 class="h4"><a href="<?=$attributes["link"];?>"><?= $attributes["title"]; ?></a></h3>
</div>
     

 

