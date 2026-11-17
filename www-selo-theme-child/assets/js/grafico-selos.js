/**
 * Gráficos do número de selos (shortcode [grafico-selos]).
 *
 * Desenha um gráfico por cada canvas com dados em atributos data-*,
 * por isso podem existir vários gráficos na mesma página (sítios web e apps).
 * Os separadores Gráfico/Tabela são tratados por tabs.js.
 */
(function () {
    'use strict';

    const body = document.body;
    const getTextColor = () => body.classList.contains('dark-theme') ? '#FFF' : '#000';
    const charts = [];

    function desenhar(canvas) {
        const d = canvas.dataset;

        const chart = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Ouro', 'Prata', 'Bronze'],
                datasets: [{
                    label: d.eixo,
                    data: [Number(d.ouro), Number(d.prata), Number(d.bronze)],
                    backgroundColor: ['#f0a502', '#ADADAD', '#A56140'],
                    maxBarThickness: 70
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    title: {
                        display: true,
                        text: d.titulo,
                        color: getTextColor(),
                        font: { family: 'Noto Sans', size: 22 },
                        align: 'center'
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: d.eixo,
                            color: getTextColor(),
                            font: { family: 'Noto Sans', size: 14 }
                        },
                        ticks: {
                            color: getTextColor(),
                            precision: 0,
                            font: { family: 'Noto Sans', size: 14 }
                        }
                    },
                    y: {
                        ticks: {
                            color: getTextColor(),
                            font: { family: 'Noto Sans', size: 14 }
                        }
                    }
                }
            }
        });

        charts.push(chart);
    }

    function init() {
        if (typeof Chart === 'undefined') {
            return;
        }

        const canvases = document.querySelectorAll('.grafico-selos-canvas canvas[data-titulo]');

        if (!canvases.length) {
            return;
        }

        // Espera pela Noto Sans: o Chart.js mede os rótulos ao desenhar.
        const fontes = document.fonts && document.fonts.ready ? document.fonts.ready : Promise.resolve();
        fontes.then(function () {
            canvases.forEach(desenhar);
        });

        // Modo escuro: atualiza a cor dos textos.
        new MutationObserver(function () {
            const cor = getTextColor();
            charts.forEach(function (chart) {
                chart.options.plugins.title.color = cor;
                chart.options.scales.x.title.color = cor;
                chart.options.scales.x.ticks.color = cor;
                chart.options.scales.y.ticks.color = cor;
                chart.update();
            });
        }).observe(body, { attributes: true, attributeFilter: ['class'] });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
