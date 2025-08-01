export const addMinutesToTime = (
    time12h: string,
    minutesToAdd: number
) :string => {
    const [time, modifier] = time12h.split(' ');
    let [hours, minutes] = time.split(':').map(Number);

    if (modifier.toUpperCase() === 'PM' && hours < 12) hours += 12;
    if (modifier.toUpperCase() === 'AM' && hours === 12) hours = 0;

    const date = new Date();
    date.setHours(hours);
    date.setMinutes(minutes + minutesToAdd);
    date.setSeconds(0);

    let finalHours = date.getHours();
    const finalMinutes = date.getMinutes().toString().padStart(2, '0');
    const isPM = finalHours >= 12;
    const ampm = isPM ? 'PM' : 'AM';

    finalHours = finalHours % 12;
    finalHours = finalHours === 0 ? 12 : finalHours;

    return `${finalHours}:${finalMinutes} ${ampm}`;
}