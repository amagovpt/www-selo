jQuery(document).ready(function() {
    const ctx = document.getElementById('graficoSelos').getContext('2d');
    const body = document.querySelector('body');

    const getTextColor = () => body.classList.contains('dark-theme') ? '#FFF' : '#000';

    const data = {
        labels: ['Selos Ouro', 'Selos Prata', 'Selos Bronze'],
        datasets: [{
            label: 'Número de sítios web com Selo',
            data: [chartData.selosOuro, chartData.selosPrata, chartData.selosBronze],
            backgroundColor: ['#f0a502', '#ADADAD', '#A56140'],
            maxBarThickness: 70
        }]
    };

    const options = {
        indexAxis: 'y',
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            title: {
                display: true,
                text: chartData.totalSelos + ' sítios web com Selo',
                color: getTextColor(),
                font: {
                    family: 'Noto Sans',
                    size: 28
                },
                align: 'center'
            }
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Número de sítios web com Selo',
                    color: getTextColor(),
                    font: {
                        family: 'Noto Sans',
                        size: 16
                    }
                },
                ticks: {
                    color: getTextColor(),
                    font: {
                        family: 'Noto Sans',
                        size: 14
                    }
                }
            },
            y: {
                title: {
                    display: true,
                    color: getTextColor(),
                },
                ticks: {
                    color: getTextColor(),
                    font: {
                        family: 'Noto Sans',
                        size: 14
                    }
                },
            }
        }
    };

    const chart = new Chart(ctx, {
        type: 'bar',
        data: data,
        options: options
    });

    const observer = new MutationObserver(() => {
        const textColor = getTextColor();
        chart.options.plugins.title.color = textColor;
        chart.options.scales.x.title.color = textColor;
        chart.options.scales.x.ticks.color = textColor;
        chart.options.scales.y.ticks.color = textColor;
        chart.options.scales.y.title.color = textColor;
        chart.update();
    });

    observer.observe(body, { attributes: true, attributeFilter: ['class'] });

    // Accessible Tabs Implementation
    const tabs = document.querySelectorAll('[role="tab"]');
    const tabPanels = document.querySelectorAll('[role="tabpanel"]');

    // Function to switch tabs
    function switchTab(newTab) {
        const newTabId = newTab.getAttribute('id');
        const newPanelId = newTab.getAttribute('aria-controls');
        const newPanel = document.getElementById(newPanelId);

        // Deactivate all tabs
        tabs.forEach(tab => {
            tab.setAttribute('aria-selected', 'false');
            tab.setAttribute('tabindex', '-1');
            tab.classList.remove('active');
        });

        // Hide all panels
        tabPanels.forEach(panel => {
            panel.setAttribute('hidden', '');
        });

        // Activate new tab
        newTab.setAttribute('aria-selected', 'true');
        newTab.setAttribute('tabindex', '0');
        newTab.classList.add('active');
        newTab.focus();

        // Show new panel
        if (newPanel) {
            newPanel.removeAttribute('hidden');
        }
    }

    // Click event for tabs
    tabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            switchTab(this);
        });

        // Keyboard navigation
        tab.addEventListener('keydown', function(e) {
            let targetTab = null;

            // Arrow keys navigation
            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                const tabsArray = Array.from(tabs);
                const currentIndex = tabsArray.indexOf(this);

                if (e.key === 'ArrowRight') {
                    targetTab = tabsArray[(currentIndex + 1) % tabsArray.length];
                } else {
                    targetTab = tabsArray[(currentIndex - 1 + tabsArray.length) % tabsArray.length];
                }

                e.preventDefault();
                switchTab(targetTab);
            }

            // Home/End keys
            if (e.key === 'Home') {
                e.preventDefault();
                switchTab(tabs[0]);
            }
            if (e.key === 'End') {
                e.preventDefault();
                switchTab(tabs[tabs.length - 1]);
            }
        });
    });
});