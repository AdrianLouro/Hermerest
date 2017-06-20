// DATES
function getTodaysDate() {
    today = new Date()
    day = today.getDate();
    month = today.getMonth() + 1;
    year = today.getFullYear();

    return ((day < 10) ? "0" + day : day) +
        "/" +
        ((month < 10) ? "0" + month : month) +
        "/" +
        year;
}

function dateToString(date) {
    return date.substring(8, 10) + "/" + date.substring(5, 7) + "/" + date.substring(0, 4);
}

function dateComparator(date1, date2) {
    if (date1 === date2) return 0;
    if (date1.substring(6, 10) > date2.substring(6, 10)) return 1;
    if (date1.substring(6, 10) < date2.substring(6, 10)) return -1;
    if (date1.substring(3, 5) > date2.substring(3, 5)) return 1;
    if (date1.substring(3, 5) < date2.substring(3, 5)) return -1;
    if (date1.substring(0, 2) > date2.substring(0, 2)) return 1;
    else return -1;
}

function checkAndSetPastDates(table) {
    $("#" + table + " > tbody > tr").each(function () {
        var td = $(this).children().eq(2);
        if (dateComparator($(td).text(), getTodaysDate()) === -1)
            $(td).addClass('pastDate');
    });
}

// MODALS
function warningAlert(message){
    $("#warning_modal .modal-body_content").text(message);
    $("#warning_modal").fadeIn(100);
}

function confirmAlert(message){
    $("#confirm_modal .modal-body_content").text(message);
    $("#confirm_modal").fadeIn(100);
}

function closeModal() {
    $(".modal").fadeOut(100);
}

function closeAlertModal() {
    $("#confirmModalAcceptButton").off();
    $("#warning_modal, #confirm_modal").fadeOut(100);
}