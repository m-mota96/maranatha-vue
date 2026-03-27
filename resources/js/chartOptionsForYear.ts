export const chartForYear = () :object => {
    return {
        chart: {
            type: 'column',
        },
        title: {
            text: ''
        },
        xAxis: {
            type: 'date',
            categories: '',
            title: {
                text: 'Mes'
            },
            crosshair: true
        },
        yAxis: {
            min: 0,
            title: {
                text: 'Monto vendido'
            },
        },
        tooltip: {
            formatter: function(this: any) {
                return `
                <span style="font-size:10px">${this.point.key}</span><br>
                <b>${this.point.y.toLocaleString('es-MX', {
                    style: 'currency',
                    currency: 'MXN'
                })}</b>
                `;
            },
            shared: true,
            useHTML: true
        },
        plotOptions: {
            column: {
                pointPadding: 0.2,
                groupPadding: 0.2,
                borderWidth: 0
            },
            series: {
                dataLabels: {
                    enabled: true,
                    formatter: function(this: any) {
                        return this.y ? formatCurrency(this.y) : '';
                    }
                }
            }
        },
        credits: {
            enabled: false
        },
        accessibility: {
            enabled: false
        },
        series: []
    }
};

const formatCurrency = (value: number) :string => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};