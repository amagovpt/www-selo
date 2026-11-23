
<?php
/**
 * Lazyblock para imagens.
 *
 * @var array  $attributes Block attributes.
 * @var array  $block Block data.
 * @var string $context Preview context [editor,frontend].
 */

// --------------------------------------------------
// 1. Definir a largura da imagem.
// --------------------------------------------------

$tamanho_imagem = $attributes['tamanho-imagem']['value'] ?? '';

switch ($tamanho_imagem) {
    case '150':
        $width = '150';
        break;

    case '300':
        $width = '300';
        break;

    case '1024':
        $width = '1024';
        break;

    default:
        $width = '';
        break;
}

// Largura personalizada.
$largura = $attributes['largura'] ?? '';

if (
    $largura !== '' &&
    is_numeric($largura) &&
    $largura > 0
) {
    $width = (string) absint($largura);
}

// --------------------------------------------------
// 2. Definir a altura da imagem.
// --------------------------------------------------

$altura = $attributes['altura'] ?? '';

$height = (
    $altura !== '' &&
    is_numeric($altura) &&
    $altura > 0
)
    ? (string) absint($altura)
    : '';

// --------------------------------------------------
// 3. Texto alternativo.
// --------------------------------------------------

$alt = $attributes['imagem-alt'] ?? '';

if (!is_string($alt)) {
    $alt = '';
}

// --------------------------------------------------
// 4. Obter o URL da imagem.
// --------------------------------------------------

$imagem_ama = $attributes['imagem-ama'] ?? '';

$imagem_url = '';

if (is_array($imagem_ama)) {

    $imagem_url = $imagem_ama['url'] ?? '';

} elseif (is_string($imagem_ama)) {

    $imagem_url = $imagem_ama;

}

// --------------------------------------------------
// 5. Classes personalizadas.
// --------------------------------------------------

$classes_personalizadas = $attributes['classe-personalizadas'] ?? '';

if (!is_string($classes_personalizadas)) {
    $classes_personalizadas = '';
}

$classes = trim(
    'img-fluid d-block mx-auto ' . $classes_personalizadas
);

// --------------------------------------------------
// 6. Ligação associada à imagem.
// --------------------------------------------------

$ligacao = $attributes['ligacao'] ?? '';

if (!is_string($ligacao)) {
    $ligacao = '';
}

// --------------------------------------------------
// 7. Renderizar a imagem ou apresentar aviso.
// --------------------------------------------------

if (!empty($imagem_url)) :

    // Construir os atributos dimensionais.
    $atributos_dimensao = '';

    if ($width !== '') {
        $atributos_dimensao .= ' width="' . esc_attr($width) . '"';
    }

    if ($height !== '') {
        $atributos_dimensao .= ' height="' . esc_attr($height) . '"';
    }

    // Construir a imagem.
    $imagem_html = sprintf(
        '<img alt="%1$s" src="%2$s" class="%3$s"%4$s>',
        esc_attr($alt),
        esc_url($imagem_url),
        esc_attr($classes),
        $atributos_dimensao
    );

    // Se existir ligação, envolver a imagem num link.
    if (!empty($ligacao)) : ?>

        <a href="<?= esc_url($ligacao); ?>">
            <?= $imagem_html; ?>
        </a>

    <?php else : ?>

        <?= $imagem_html; ?>

    <?php endif;

elseif (isset($context) && $context === 'editor') : ?>

    <!-- Aviso apresentado apenas no editor Gutenberg. -->
    <div
        class="components-notice is-warning"
        role="status"
    >
        <div class="components-notice__content">
            <strong>Imagem não selecionada.</strong>
            <p>
                Este bloco ainda não tem uma imagem associada.
                Seleciona uma imagem através da Biblioteca multimédia,
                carrega um ficheiro ou introduz um URL.
            </p>
        </div>
    </div>

<?php endif; ?>
