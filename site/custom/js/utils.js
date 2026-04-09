function fecha_actual() {
    let current_datetime = new Date()
    let formatted_date = current_datetime.getFullYear() + "-" + zfill((current_datetime.getMonth() + 1), 2) + "-" + zfill(current_datetime.getDate(), 2);
    return formatted_date;
}


function fecha_hora() {
    let current_datetime = new Date()
    let formatted_date = current_datetime.getFullYear() + "-" + (current_datetime.getMonth() + 1) + "-" + current_datetime.getDate() + " " + current_datetime.getHours() + ":" + current_datetime.getMinutes() + ":" + current_datetime.getSeconds();
    return formatted_date;
}

function fecha_hora_crm() {
    let current_datetime = new Date();

    let year = current_datetime.getFullYear();
    let month = String(current_datetime.getMonth() + 1).padStart(2, '0');
    let day = String(current_datetime.getDate()).padStart(2, '0');
    let hours = String(current_datetime.getHours()).padStart(2, '0');
    let minutes = String(current_datetime.getMinutes()).padStart(2, '0');
    let seconds = String(current_datetime.getSeconds()).padStart(2, '0');

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

function devolver_fecha(fecha) {
    let fec = fecha.split(' ');
    return fec[0];
}

function zfill(value, length) {
    return (value.toString().length < length) ? zfill("0" + value, length) : value;
}