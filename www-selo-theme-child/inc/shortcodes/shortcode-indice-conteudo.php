<?php
/**
 * Shortcode para gerar um índice de conteúdo baseado nos títulos (headings) da página.
 * 
 * Uso: [indice_conteudo]
 * Uso com atributos: [indice_conteudo titulo="Navegação" niveis="2,3"]
 * 
 * @param array $atts Atributos do shortcode
 * @return string HTML do índice de conteúdo
 */
function selo_indice_conteudo_shortcode($atts)
{
    // Atributos do shortcode
    $atts = shortcode_atts(
        array(
            'titulo' => 'Índice de Conteúdo',
            'niveis' => '2,3,4'
        ),
        $atts,
        'indice_conteudo'
    );

    // Obtém o conteúdo da página/post atual
    global $post;
    
    if (!isset($post) || empty($post->post_content)) {
        return '';
    }

    // Usa o conteúdo direto sem aplicar filtros para evitar recursão
    $content = $post->post_content;
    
    // Parse dos níveis permitidos
    $niveis_permitidos = array_map('trim', explode(',', $atts['niveis']));
    
    // Extrai os headings do conteúdo
    $headings = selo_extrair_headings_com_ids($content, $niveis_permitidos);
    
    if (empty($headings)) {
        return '';
    }
    
    // Gera o HTML do índice
    $output = '<nav class="indice-conteudo" aria-label="' . esc_attr($atts['titulo']) . '">';
    
    if (!empty($atts['titulo'])) {
        $output .= '<h2 class="indice-conteudo-titulo">' . esc_html($atts['titulo']) . '</h2>';
    }
    
    $output .= selo_gerar_lista_hierarquica($headings);
    $output .= '</nav>';
    
    return $output;
}
add_shortcode('indice_conteudo', 'selo_indice_conteudo_shortcode');

/**
 * Extrai headings com IDs do conteúdo HTML na ordem em que aparecem
 * 
 * @param string $content Conteúdo HTML
 * @param array $niveis_permitidos Níveis de headings a incluir
 * @return array Array de headings ordenados por posição
 */
function selo_extrair_headings_com_ids($content, $niveis_permitidos)
{
    $headings = array();
    
    if (empty($content)) {
        return $headings;
    }
    
    // Suprime warnings do DOMDocument
    $previous_value = libxml_use_internal_errors(true);
    
    $dom = new DOMDocument();
    
    // Tenta carregar o HTML
    @$dom->loadHTML(
        '<?xml encoding="UTF-8">' . $content,
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    
    libxml_clear_errors();
    libxml_use_internal_errors($previous_value);
    
    // Cria um XPath para buscar todos os headings de uma vez
    $xpath = new DOMXPath($dom);
    
    // Constrói a query XPath para buscar todos os níveis permitidos
    $queries = array();
    foreach ($niveis_permitidos as $nivel) {
        $nivel = trim($nivel);
        if (!empty($nivel) && is_numeric($nivel)) {
            $queries[] = "//h" . $nivel;
        }
    }
    
    if (empty($queries)) {
        return $headings;
    }
    
    // Busca todos os headings de uma vez
    $query = implode(' | ', $queries);
    $nodes = $xpath->query($query);
    
    // Processa os headings na ordem em que aparecem no documento
    if ($nodes && $nodes->length > 0) {
        foreach ($nodes as $node) {
            $id = $node->getAttribute('id');
            
            // Só inclui headings que têm ID
            if (!empty($id)) {
                // Extrai o número do nível (h2 -> 2, h3 -> 3, etc)
                $nivel = (int)str_replace('h', '', strtolower($node->nodeName));
                
                $headings[] = array(
                    'nivel' => $nivel,
                    'id' => $id,
                    'texto' => trim($node->textContent)
                );
            }
        }
    }
    
    return $headings;
}

/**
 * Gera uma lista hierárquica HTML a partir dos headings
 * Cria estrutura aninhada: H2 > H3 > H4
 * 
 * @param array $headings Array de headings
 * @return string HTML da lista
 */
function selo_gerar_lista_hierarquica($headings)
{
    if (empty($headings)) {
        return '';
    }
    
    $output = '<ul class="indice-conteudo-lista">';
    $nivel_anterior = null;
    $stack = array(); // Pilha para rastrear níveis abertos
    
    foreach ($headings as $heading) {
        $nivel_atual = $heading['nivel'];
        
        // Primeiro item
        if ($nivel_anterior === null) {
            $output .= '<li>';
            $output .= '<a href="#' . esc_attr($heading['id']) . '">';
            $output .= esc_html($heading['texto']);
            $output .= '</a>';
        }
        // Mesmo nível - fecha o item anterior e abre novo
        elseif ($nivel_atual === $nivel_anterior) {
            $output .= '</li>';
            $output .= '<li>';
            $output .= '<a href="#' . esc_attr($heading['id']) . '">';
            $output .= esc_html($heading['texto']);
            $output .= '</a>';
        }
        // Nível mais profundo (filho) - abre nova lista
        elseif ($nivel_atual > $nivel_anterior) {
            // Pode ter múltiplos níveis de diferença
            $diferencaNiveis = $nivel_atual - $nivel_anterior;
            for ($i = 0; $i < $diferencaNiveis; $i++) {
                $output .= '<ul>';
                array_push($stack, $nivel_anterior + $i);
            }
            $output .= '<li>';
            $output .= '<a href="#' . esc_attr($heading['id']) . '">';
            $output .= esc_html($heading['texto']);
            $output .= '</a>';
        }
        // Nível menos profundo (volta para cima)
        else {
            // Fecha listas até chegar no nível correto
            while (!empty($stack) && end($stack) >= $nivel_atual) {
                $output .= '</li></ul>';
                array_pop($stack);
            }
            $output .= '</li>';
            $output .= '<li>';
            $output .= '<a href="#' . esc_attr($heading['id']) . '">';
            $output .= esc_html($heading['texto']);
            $output .= '</a>';
        }
        
        $nivel_anterior = $nivel_atual;
    }
    
    // Fecha todas as tags abertas
    $output .= '</li>';
    while (!empty($stack)) {
        $output .= '</ul></li>';
        array_pop($stack);
    }
    
    $output .= '</ul>';
    
    return $output;
}
