# Sincronização dos dados dos selos

Os dados dos sítios web e aplicações móveis com Selo de Usabilidade e Acessibilidade são importados para a tabela `wp_sitios_web_com_selo` a partir de **duas fontes JSON com a mesma estrutura**:

- **sítios web**: endpoint do Observatório ou JSON (no Git, enquanto o Observatório não tiver os dados atualizados);
- **aplicações móveis**: JSON (no Git, enquanto o Observatório não tiver esta informação). Opcional.

 A lista, as fichas, o gráfico e a data "Última atualização" usam essa tabela; **as visitas ao site não fazem pedidos à fonte de dados**.

Código: `www-selo-theme-child/inc/base-dados.php` (importação) e `www-selo-theme-child/inc/cron/cron-sites-selo.php` (agendamento).

## Como funciona

1. Uma vez por dia, à hora definida em **Definições > Definições | AMA** (por omissão 02:30, hora de Lisboa), o WP-Cron corre a tarefa `atualizar_dados_sites_selo`.
2. A tarefa pede o JSON a cada fonte configurada e valida a resposta:
   - só HTTPS (HTTP só é aceite em ambiente `local`);
   - código HTTP 200, corpo não vazio, no máximo 5 MB;
   - uma lista JSON; cada registo tem de ter `id`, `name` e `selo` (`Ouro`, `Prata` ou `Bronze`). Os registos inválidos são ignorados e contados;
   - token e assinatura, se estiverem configurados (ver abaixo).
3. Cada fonte é tratada à parte. Se estiver tudo bem, os registos desse tipo são substituídos numa transação (tudo ou nada). Se algo falhar, **os dados publicados desse tipo ficam como estavam** (a outra fonte é atualizada na mesma), o erro fica registado em Definições | AMA e no `debug.log` e é enviado um e-mail para os endereços configurados (no máximo um por dia para o mesmo erro).
4. A data "Última atualização" mostrada no site é a do último carregamento com sucesso (de pelo menos uma fonte).

O botão **Sincronizar Dados** (Definições | AMA) faz a mesma coisa de imediato. Em Definições | AMA também se vê a próxima execução, a última sincronização com sucesso e o último erro.

## Formato do JSON

Igual nas duas fontes. Lista de objetos:

```json
[
  {
    "id": 2,
    "name": "Câmara Municipal de Valongo (sítio Web institucional)",
    "entidade": "Câmara Municipal de Valongo",
    "selo": "Ouro",
    "data_selo": "24/06/2026",
    "declaracao": "Conforme",
    "data_declaracao": "02/07/2026",
    "url_observatorio": "",
    "url": "https://www.cm-valongo.pt/"
  }
]
```

| Campo | Obrigatório | Notas |
|---|---|---|
| `id` | sim | inteiro, único **dentro da fonte** (o mesmo id pode existir nas duas fontes) |
| `name` | sim | nome do sítio web ou da aplicação (também aceita `Name`) |
| `selo` | sim | `Ouro`, `Prata` ou `Bronze` |
| `entidade` | não | |
| `data_selo` | não | `DD/MM/AAAA` ou `AAAA-MM-DD`; outro valor (ex.: `nd`) = sem data |
| `declaracao` | não | ex.: `Conforme`, `Plenamente conforme` |
| `data_declaracao` | não | como `data_selo` |
| `url_observatorio` | não | vazio nas aplicações móveis |
| `url` | não | sítio web ou página da aplicação na loja |

O tipo (sítio web ou aplicação móvel) é dado pela fonte de onde o registo vem.

## Proteger a ligação à fonte

Configura-se no `wp-config.php` de cada servidor (nunca no repositório nem na base de dados). Todas são opcionais e podem combinar-se:

```php
// Endereços das fontes (têm prioridade sobre os campos em Definições | AMA).
define( 'AMA_SELO_FONTE_URL', 'https://…' );       // sítios web
define( 'AMA_SELO_FONTE_APPS_URL', 'https://…' );  // aplicações móveis

// Só aceita fontes nestes anfitriões (separados por vírgulas). Vale para as duas fontes.
define( 'AMA_SELO_FONTE_HOSTS', 'exemplo.gov.pt' );

// Enviado como "Authorization: Bearer <token>" (a fonte tem de o validar).
define( 'AMA_SELO_FONTE_TOKEN', '…' );

// Certifica a origem: o site só aceita o JSON se a assinatura HMAC-SHA256 do corpo,
// feita com este segredo, vier no cabeçalho "X-Selo-Assinatura" (ex.: "sha256=<hex>")
// ou, para um ficheiro estático, no ficheiro "<url>.sig" ao lado do JSON.
define( 'AMA_SELO_FONTE_SEGREDO', '…' );
```

- **Token**: serve quando a fonte é uma API (o Observatório) que valida o cabeçalho `Authorization`.
- **Assinatura**: serve também para um ficheiro estático (ex.: JSON num repositório), em que não se controlam os cabeçalhos. Para gerar o `.sig`:

  ```sh
  openssl dgst -sha256 -hmac "$SEGREDO" -r ficheiro.json | cut -d' ' -f1 > ficheiro.json.sig
  ```

## Cron do servidor (DEV/PRD)

O WP-Cron só corre quando há visitas e precisa que o servidor consiga fazer um pedido HTTP a si próprio (*loopback*). Em servidores internos, atrás de *proxy*, com autenticação básica ou sem DNS para o próprio domínio, isso falha e a tarefa nunca corre. A forma fiável é desligar o disparo pelas visitas e usar o cron do sistema:

1. No `wp-config.php`:

   ```php
   define( 'DISABLE_WP_CRON', true );
   ```

2. No crontab do utilizador do servidor web (`crontab -e -u www-data`), uma das opções:

   ```cron
   # Com WP-CLI (recomendado): corre as tarefas em atraso a cada 15 minutos
   */15 * * * * cd /caminho/do/site && wp cron event run --due-now --quiet

   # Sem WP-CLI
   */15 * * * * php /caminho/do/site/wp-cron.php >/dev/null 2>&1
   ```

   A tarefa dos selos continua a correr só uma vez por dia (à hora definida); o cron do sistema só "acorda" o WP-Cron.

3. Para testar à mão: `wp selo sincronizar` (WP-CLI) ou o botão **Sincronizar Dados**.

Verificar: `wp cron event list | grep selo` deve mostrar `atualizar_dados_sites_selo` com a próxima execução à hora definida.
