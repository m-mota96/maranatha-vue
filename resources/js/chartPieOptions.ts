export const chartPieOptions = () :object => {
    return {
        chart: {
            type: 'pie',
            zooming: {
                type: 'xy'
            },
            panning: {
                enabled: true,
                type: 'xy'
            },
            panKey: 'shift'
        },
        title: {
            text: ''
        },
        tooltip: {
            formatter: function(this: any) {
                const value = this.point.realValue ?? this.y;

                const sign = value < 0 ? '-' : '';
                
                return `
                <span style="font-size:10px">${this.point.key}</span><br>
                <b>${sign}${this.point.y.toLocaleString('es-MX', {
                    style: 'currency',
                    currency: 'MXN'
                })}</b>
                `;
            },
            shared: true,
            useHTML: true
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: [{
                    enabled: true,
                    distance: 20,
                    // formatter: function() {
                    //     return this.point.y ? formatCurrency(this.point.y) : '';
                    // }
                }, {
                    enabled: true,
                    distance: -50,
                    formatter: function(this: any) {
                        const value = this.point.realValue ?? this.y;

                        const formatted = new Intl.NumberFormat('es-MX', {
                            style: 'currency',
                            currency: 'MXN'
                        }).format(Math.abs(value));

                        return value < 0 ? `-${formatted}` : formatted;
                    },
                    style: {
                        fontSize: '1.2em',
                        textOutline: 'none',
                        opacity: 0.7
                    },
                    filter: {
                        operator: '>',
                        property: 'y',
                        value: 10
                    }
                }]
            }
        },
        credits: {
            enabled: false
        },
        accessibility: {
            enabled: false
        },
        series: [
            {
                name: 'Monto',
                colorByPoint: true,
                data: []
            }
        ]
    };
}