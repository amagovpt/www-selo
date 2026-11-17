# Consentimento de cookies

Implementado no tema filho (`www-selo-theme-child`) na sequência da issue #1, com a **mesma implementação do acessibilidade.gov.pt** (repositório `amagovpt/www-acessibilidade`, `docs/cookies.md`).

**Princípio:** nada de terceiros é carregado sem uma ação explícita do visitante.

## Ficheiros

| Ficheiro | Função |
|---|---|
| `www-selo-theme-child/inc/cookie-consent.php` | Lista de serviços, bloqueio dos vídeos e do assistente, HTML do aviso, botão do rodapé e shortcode `[ama_cookies]` |
| `www-selo-theme-child/assets/js/cookie-consent.js` | Lê e grava a escolha, ativa os conteúdos e controla o aviso |
| `www-selo-theme-child/assets/css/cookie-consent.css` | Estilos do aviso e dos marcadores de vídeo (inclui modo escuro) |
| `www-selo-theme/header.php` | Chama `wp_body_open()`, onde o aviso é inserido, e imprime o assistente com `ama_ai_agent_markup()` |
| `www-selo-theme/functions.php` | `ama_ai_agent_markup()`: só scripts HTTPS de domínios autorizados + filtro para o bloqueio |
| `www-selo-theme/footer.php` | Botão "Definições de cookies" |

## Como funciona

1. **Vídeos do YouTube.** No filtro `the_content` (e nos widgets), cada `<iframe>` do YouTube é trocado por um marcador `.ama-embed` com:
   - um botão "Ver vídeo", que só fica visível com JavaScript;
   - uma ligação "Abrir no YouTube", que funciona sempre.

   O vídeo carrega a partir de `youtube-nocookie.com` e sem o parâmetro de rastreio `si`. Os excertos não são alterados.
2. **Assistente virtual.** Antes, o conteúdo da opção `ai_agent` era impresso tal e qual no `<head>`. Agora:
   - só passam tags `<script src="https://…">` de domínios autorizados (filtro `ama_ai_agent_allowed_hosts`, por omissão `virtualbrain.ai`); HTML e JavaScript inline são descartados;
   - o script sai com `type="text/plain"` e `data-ama-src`; depois do consentimento, o JS recria-o com os atributos originais.
3. **Aviso.**
   - Só é mostrado quando há serviços com `autoload => true`, ou seja, que carregariam em todas as páginas (hoje, apenas o assistente, se a opção `ai_agent` estiver preenchida).
   - Os vídeos pedem consentimento no próprio local.
   - Os botões "Aceitar todos", "Rejeitar todos" e "Escolher" têm o mesmo destaque.
   - Fica antes da ligação "Saltar para o conteúdo" (padrão do GOV.UK Design System).
   - A mensagem de confirmação recebe o foco.
4. **Escolha.** Fica no cookie primário `ama_consent`, com o valor `{"v":1,"youtube":true,"assistente":false}`. Dura 6 meses, com `SameSite=Lax` e `Secure` em HTTPS. Este cookie é estritamente necessário, por isso não precisa de consentimento.
5. **Retirar o consentimento.** Faz-se no botão "Definições de cookies" do rodapé. Se um serviço já carregado deixar de ser permitido, a página é recarregada.

## Cookies e armazenamento

| Nome | Origem | Finalidade | Duração | Tipo |
|---|---|---|---|---|
| `ama_consent` | 1.ª parte | Guarda as escolhas sobre cookies | 6 meses | Necessário |
| `darkMode` (localStorage) | 1.ª parte | Lembra o modo escuro | Até ser apagado | Necessário |
| YouTube | Google Ireland Ltd. | Reprodução de vídeos | Definida pelo fornecedor | Só com consentimento |
| Assistente virtual | Fornecedor do assistente | Assistente de apoio | Definida pelo fornecedor | Só com consentimento |
| Google reCAPTCHA | Google Ireland Ltd. | Protege os formulários (Contactos) contra envios automáticos | Definida pelo fornecedor | Necessário para enviar o formulário |

Esta tabela é gerada na página com o shortcode `[ama_cookies]`.

**reCAPTCHA:** é carregado pelo plugin Formidable só nas páginas com formulário (hoje, Contactos). Não foi bloqueado, porque sem ele o formulário não pode ser enviado. Se a ARTE preferir pedir consentimento também para isto, a alternativa é trocar o reCAPTCHA pela proteção anti-spam própria do Formidable (honeypot/"Spam protection"), que não usa terceiros.

## Política de privacidade

Como no acessibilidade.gov.pt, o aviso liga para a [política de privacidade da ARTE](https://www.arte.gov.pt/politica-de-privacidade/) (filtro `ama_cookie_policy_url`). O menu do rodapé do selo.usabilidade.gov.pt ainda liga para `https://www.arte.gov.pt/web/arte/politica-de-privacidade`: convém atualizar a ligação no menu (conteúdo, não código).

### Para acrescentar à tabela de cookies da ARTE

| Cookie | Domínio | Propósito | Data de expiração |
|---|---|---|---|
| `ama_consent` | selo.usabilidade.gov.pt | Guarda as escolhas do visitante sobre conteúdos de terceiros (vídeos do YouTube e, quando existe, assistente virtual). Estritamente necessário. | 6 meses |
| `darkMode` (localStorage) | selo.usabilidade.gov.pt | Lembra se o visitante escolheu o modo escuro | Até o visitante o apagar |
| `ama_consent_message` (sessionStorage) | selo.usabilidade.gov.pt | Mostra a confirmação da escolha de cookies depois de a página recarregar | Sessão |

## Configuração (filtros)

| Filtro | Por omissão | Uso |
|---|---|---|
| `ama_cookie_services` | `youtube` e, se houver `ai_agent`, `assistente` | Acrescentar ou alterar serviços |
| `ama_cookie_consent_version` | `1` | Incrementar quando a política mudar, para pedir consentimento de novo |
| `ama_cookie_policy_url` | `https://www.arte.gov.pt/politica-de-privacidade/` | Ligação "Saiba mais" do aviso |
| `ama_ai_agent_allowed_hosts` | `['virtualbrain.ai']` | Domínios aceites para o script do assistente |
| `ama_ai_agent_markup` | — | HTML do assistente; o tema filho usa-o para o bloquear |

## Testes manuais

1. Com o browser limpo, abrir o artigo com vídeo (`/jornadas-reforcam-compromisso-com-acessibilidade-e-usabilidade-digital/`). Confirmar que não há pedidos a `youtube.com`, não há `Set-Cookie` e o marcador do vídeo aparece.
2. "Ver vídeo" carrega só esse vídeo, a partir de `youtube-nocookie.com`, e o foco passa para o vídeo.
3. "Definições de cookies" (no rodapé) ➜ "Escolher" ➜ marcar "Vídeos do YouTube" ➜ "Guardar escolhas". Os vídeos carregam e aparece a confirmação.
4. Rodapé ➜ "Rejeitar todos". A página recarrega sem vídeos e mostra a confirmação.
5. Com a opção `ai_agent` definida, o aviso aparece em todas as páginas até haver uma escolha.
