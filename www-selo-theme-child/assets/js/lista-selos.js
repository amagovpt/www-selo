/**
 * Ordenação da lista de selos sem recarregar a página (shortcode [lista-sitios-selo]).
 *
 * Os cabeçalhos ordenáveis continuam a ser ligações (?ordenar=&sentido=), que funcionam
 * sem JavaScript. Com JavaScript, o clique pede a mesma página em segundo plano e atualiza
 * só a tabela e a paginação. Os cabeçalhos são atualizados no próprio elemento (não são
 * substituídos), por isso o foco do teclado / leitor de ecrã / varrimento fica no título
 * da coluna e o utilizador pode descer logo pela coluna para ver a nova ordenação.
 */
(function () {
    'use strict';

    const SELETOR = '[data-lista-selos]';

    function contentor(el) {
        return el.closest(SELETOR);
    }

    function anunciar(lista, texto) {
        const regiao = lista.querySelector('.lista-selos-estado');
        if (!regiao) return;
        // Esvaziar primeiro para que a mesma frase volte a ser anunciada.
        regiao.textContent = '';
        window.setTimeout(function () { regiao.textContent = texto; }, 100);
    }

    /**
     * Copia para a lista atual o conteúdo da lista devolvida pelo servidor.
     * Devolve false se a estrutura não corresponder (nesse caso recarrega-se a página).
     */
    function aplicar(lista, nova) {
        const tabela = lista.querySelector('table');
        const novaTabela = nova.querySelector('table');
        if (!tabela || !novaTabela) return false;

        const ths = tabela.querySelectorAll('thead th');
        const novosThs = novaTabela.querySelectorAll('thead th');
        if (ths.length !== novosThs.length) return false;

        // Cabeçalhos: atualizar atributos e conteúdo, mantendo os mesmos elementos (e o foco).
        ths.forEach(function (th, i) {
            const novo = novosThs[i];
            if (novo.hasAttribute('aria-sort')) {
                th.setAttribute('aria-sort', novo.getAttribute('aria-sort'));
            } else {
                th.removeAttribute('aria-sort');
            }

            const a = th.querySelector('a.ordenar');
            const novoA = novo.querySelector('a.ordenar');
            if (a && novoA) {
                a.href = novoA.getAttribute('href');
                a.className = novoA.className;
                a.innerHTML = novoA.innerHTML;
            }
        });

        const caption = tabela.querySelector('caption');
        const novoCaption = novaTabela.querySelector('caption');
        if (caption && novoCaption) caption.innerHTML = novoCaption.innerHTML;

        const tbody = tabela.querySelector('tbody');
        const novoTbody = novaTabela.querySelector('tbody');
        if (!tbody || !novoTbody) return false;
        tbody.replaceWith(document.importNode(novoTbody, true));

        // Paginação: pode aparecer, desaparecer ou mudar (a ordenação recomeça na página 1).
        const nav = lista.querySelector('.lista-selos-paginacao');
        const novaNav = nova.querySelector('.lista-selos-paginacao');
        if (nav && novaNav) {
            nav.innerHTML = novaNav.innerHTML;
        } else if (nav) {
            nav.innerHTML = '';
        }

        return true;
    }

    function carregar(lista, url, historico) {
        const tabela = lista.querySelector('table');
        if (tabela) tabela.setAttribute('aria-busy', 'true');

        return fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'fetch' } })
            .then(function (resposta) {
                if (!resposta.ok) throw new Error(resposta.status);
                return resposta.text();
            })
            .then(function (html) {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nova = doc.querySelector(SELETOR);
                if (!nova || !aplicar(lista, nova)) throw new Error('estrutura');

                if ('push' === historico) {
                    window.history.pushState({ listaSelos: true }, '', url);
                }

                const estado = nova.querySelector('.lista-selos-estado');
                anunciar(lista, estado ? estado.dataset.anuncio || '' : '');
            })
            .catch(function () {
                // Sem resposta válida: navegação normal (o servidor ordena).
                window.location.href = url;
            })
            .finally(function () {
                if (tabela) tabela.removeAttribute('aria-busy');
            });
    }

    document.addEventListener('click', function (e) {
        const a = e.target.closest(SELETOR + ' thead a.ordenar');
        if (!a) return;

        // Abrir noutro separador / janela continua a funcionar.
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        if (!window.fetch || !window.DOMParser || !window.history.pushState) return;

        e.preventDefault();
        carregar(contentor(a), a.href, 'push');
    });

    // Botões Recuar / Avançar do navegador: repor a ordenação desse endereço.
    window.addEventListener('popstate', function () {
        const lista = document.querySelector(SELETOR);
        if (lista) carregar(lista, window.location.href, 'nenhum');
    });
})();
