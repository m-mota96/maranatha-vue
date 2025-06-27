export const dateEs = (date, usage = 0, separator = null)=> {
    const months = [
        ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12']
    ];
    separator = (!separator) ? '/' : ` ${separator} `;
    return date.substring(8, 10)+separator+months[usage][parseInt(date.substring(5, 7)) - 1]+separator+date.substring(0, 4);
};

export const time = (time)=> {
    let hour = parseInt(time.substring(0, 2));
    const txt = (hour > 12) ? 'PM' : 'AM';
    hour = (hour > 12) ? (hour - 12) : hour;
    hour = (hour < 10) ? '0'+hour : hour;
    return hour+time.substring(2, 5)+' '+txt;
};