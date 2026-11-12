jQuery(document).ready(function() {
    resizeChart();

    if(chartData) {
        console.log(chartData);
        console.log(chartData.selosOuro);

        let fontName = 'Noto Sans';
        let selosOuro = Number(chartData.selosOuro);
        let selosPrata = Number(chartData.selosPrata);
        let selosBronze =  Number(chartData.selosBronze);

        let totalSelos = selosOuro + selosPrata + selosBronze;

        google.charts.load("current", {packages:["corechart"]});
        google.charts.setOnLoadCallback(drawChart);
        function drawChart() {
            let data = google.visualization.arrayToDataTable([
                ['Tipo de Selo', '', {role: 'style'}, {role: 'annotation'}],
                ['Selos Ouro', selosOuro, '#f0a502', selosOuro],
                ['Selos Prata', selosPrata, '#ADADAD', selosPrata],
                ['Selos Bronze', selosBronze, '#A56140', selosBronze]
            ]);

            let view = new google.visualization.DataView(data);
            view.setColumns([0, 1,
                { calc: "stringify",
                    sourceColumn: 1,
                    type: "string",
                    role: "annotation" },
                2]);

            let options = {
                title: totalSelos.toString() + ' sítios web com Selo',
                titleTextStyle: {fontName: fontName, fontSize: 20},
                textStyle: {
                    fontName: fontName,
                    fontSize: 18
                },
                legend: { position: 'top', textStyle: { fontName: fontName, fontSize: 16 } },
                tooltip: {textStyle: {fontName: fontName, fontSize: 16}},
                chartArea: {
                    left: 100,
                    top: 40,
                    width: '70%',
                    height: '70%'
                },
                hAxis: {
                    title: 'Número de sítios web com Selo',
                    titleTextStyle: {fontName: fontName, fontSize: 16},
                    textStyle: {fontName: fontName, fontSize: 14}
                },
                vAxis: {
                    title: '',
                    titleTextStyle: {fontName: fontName, fontSize: 16},
                    textStyle: {fontName: fontName, fontSize: 14}
                },
                backgroundColor: 'transparent',
            };

            let chart = new google.visualization.BarChart(document.getElementById('barchart'));
            google.visualization.events.addListener(chart, 'ready', function() {
                // Remove the aria-label attribute
                document.querySelector('#barchart svg[aria-label="A chart."]').removeAttribute('aria-label');

                // Add caption to the table
                let table = document.querySelector('#barchart table');
                if (table) {
                    let caption = document.createElement('caption');
                    caption.textContent = 'Número de sítios web com Selo';
                    table.prepend(caption);
                }
            });
            chart.draw(data, options);
        }
    }
});

function resizeChart() {
    // Get the width of the screen
    let screenWidth = jQuery(window).width();

    // Define the chart width based on Bootstrap media queries
    let chartWidth;

    if (screenWidth >= 1200) {
        chartWidth = 1200 / 2;
    } else if (screenWidth >= 992) {
        chartWidth = 992 / 2;
    } else if (screenWidth >= 768) {
        chartWidth = 400;
    } else {
        chartWidth = 300;
    }

    // Set the width of the containing div
    jQuery('#barchart').css('width', chartWidth + 'px');
    jQuery('#barchart').css('height', chartWidth + 'px');
}