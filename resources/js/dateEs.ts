export const dateEs = (
    date: string,
    separator: string = '/',
    usage: number = 0,
    includeDay: boolean = false
) :string => {
    const months = [
        ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12']
    ];
    var days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    let day  = '';
    if (includeDay) {
        const dateParse = new Date(date.replace(/-+/g, '/'));
        day             = days[dateParse.getDay()]+' ';
    }
    return day+date.substring(8, 10)+separator+months[usage][parseInt(date.substring(5, 7)) - 1]+separator+date.substring(0, 4);
}