// CIRCULARS
function openSendCircularDialog() {
    $("#circularSubjectInput").val("");
    $("#circularContentTextArea").val("");
    $("#attachFileInput").val(null);
    resetTreeview("#sendCircularModal");
    $("#sendCircularModal").show();
}

function openViewCircularDialog(circularId) {
    getCall("/circulars/" + circularId, openViewCircularDialogCallback);
}


function openViewCircularDialogCallback(response) {
    if (!response.success) {
        alert(response.error);
        return;
    }

    $("#viewCircularModal .modalTitle").text(response.content.subject);
    $("#viewCircularModalSendingDate").text(dateToString(response.content.sendingDate.date));
    $("#viewCircularModal .messageTextArea").text(response.content.message);
    $("#viewCircularModal #attachedFiles").html(response.content.attachmentId === null ? "No hay archivos adjuntos" :
        "<a href='http://" + window.location.host + "/Hermerest_attachments/" + response.content.attachmentId + "' download='" + response.content.attachmentName + "'>" +
        response.content.attachmentName +
        "</a>");

    $("#viewCircularModal").show();
}

function filterCirculars() {
    var circularSubject = $("#circularSubjectInputFilter").val().toLowerCase();
    var circularMonth = $("#circularMonthDropdown :selected").val();
    $("#circularsTable > tbody > tr").each(function () {
        var rowCircularSubject = $(this).children().eq(0).text().toLowerCase();
        var rowCircularMonth = $(this).children().eq(1).text().substring(3, 5);
        if (rowCircularSubject.includes(circularSubject) && (rowCircularMonth === circularMonth || circularMonth === "")) $(this).show();
        else $(this).hide();
    });
}

function sendCircular() {
    var file = document.getElementById('attachFileInput').files[0];

    if ($("#circularSubjectInput").val().trim() === "") alert("El asunto de la circular no debe estar vacío");
    else if ($("#sendCircularModal .recipientStudentLi input:checkbox:checked").length === 0) alert("Marque algún destinatario");
    else if (file !== undefined && file.size > 10485760) alert("El archivo no debe superar los 10MB");
    else {
        var studentsIds = [];
        $(".treeview .recipientStudentLi").each(function () {
            if ($(this).children().first().is(':checked')) studentsIds.push(this.id);
        });

        if (file !== undefined) {
            var reader = new FileReader();
            reader.onload = function (event) {
                sendCircularPost(studentsIds, file.name, event.target.result.replace(/^[^,]*,/, ''));
            };
            reader.readAsDataURL(file);
        } else sendCircularPost(studentsIds, file.name, "");

    }
}

function sendCircularPost(studentsIds, fileName, fileContent) {
    postCall("/circulars",
        {
            "subject": $("#circularSubjectInput").val(),
            "message": $("#circularContentTextArea").val(),
            "studentsIds": studentsIds,
            "fileName": fileName,
            "fileContent": fileContent,
        },
        sendCircularCallback
    );
}

function sendCircularCallback(response) {
    if (!response.success) {
        alert(response.error);
        return;
    }

    $("#circularsTable tbody").prepend(
        "<tr>" +
        "<td>" + response.content.subject + "</td>" +
        "<td>" + getTodaysDate() + "</td>" +
        "<td class='tableButton'><button class='infoButton' onclick='openViewCircularDialog(" + response.content.id + ")'>Ver</button></td>" +
        "</tr>"
    );
    closeModal();
}

// AUTHORIZATIONS
function openSendAuthorizationDialog() {
    $("#authorizationSubjectInput").val("");
    $("#authorizationDateInput").val("");
    $("#authorizationContentTextArea").val("");
    $("#attachFileInput").val(null);
    resetTreeview("#sendAuthorizationModal");
    $("#sendAuthorizationModal").show();
}

function openViewAuthorizationDialog(authorizationId) {
    $("#viewAuthorizationModal #authorizedStudentsList").empty();
    $("#viewAuthorizationModal #unauthorizedStudentsList").empty();
    getCall("/authorizations/" + authorizationId, openViewAuthorizationDialogCallback);
}

function openViewAuthorizationDialogCallback(response) {
    if (!response.success) {
        alert(response.error);
        return;
    }

    $("#viewAuthorizationModal .modalTitle").text(response.content.subject);
    $("#viewAuthorizationModalSendingDate").text(dateToString(response.content.sendingDate.date));
    $("#viewAuthorizationModalLimitDate").text(dateToString(response.content.limitDate.date));
    $("#viewAuthorizationModal .messageTextArea").text(response.content.message);
    $("#viewAuthorizationModal #attachedFiles").html(response.content.attachmentId === null ? "No hay archivos adjuntos" :
        "<a href='http://" + window.location.host + "/Hermerest_attachments/" + response.content.attachmentId + "' download='" + response.content.attachmentName + "'>" +
        response.content.attachmentName +
        "</a>");

    if (dateComparator($("#viewAuthorizationModalLimitDate").text(), getTodaysDate()) < 0)
        $("#viewAuthorizationModalLimitDate").addClass('pastDate');
    else
        $("#viewAuthorizationModalLimitDate").removeClass('pastDate');

    response.content.students.forEach(function (student) {
        $(student[1] === 1 ? "#authorizedStudentsList" : "#unauthorizedStudentsList").append(
            "<li>" + student[0] + "</li>"
        )
    });

    $("#viewAuthorizationModal").show();
}

function sendAuthorization() {
    var file = document.getElementById('attachFileInput').files[0];

    if ($("#authorizationSubjectInput").val().trim() === "" || $("#authorizationDateInput").val().trim() === "") alert("El asunto y la fecha límite de la autorización no deben estar vacíos");
    else if (dateComparator(dateToString($("#authorizationDateInput").val()), getTodaysDate()) === -1) alert("La fecha límite es anterior a la actual");
    else if (file !== undefined && file.size > 10485760) alert("El archivo no debe superar los 10MB");
    else if ($("#sendAuthorizationModal .recipientStudentLi input:checkbox:checked").length === 0) alert("Marque algún destinatario");
    else {
        var studentsIds = [];
        $(".treeview .recipientStudentLi").each(function () {
            if ($(this).children().first().is(':checked')) studentsIds.push(this.id);
        });

        if (file !== undefined) {
            var reader = new FileReader();
            reader.onload = function (event) {
                sendAuthorizationPost(studentsIds, file.name, event.target.result.replace(/^[^,]*,/, ''));
            };
            reader.readAsDataURL(file);
        } else sendAuthorizationPost(studentsIds, file.name, "");
    }
}

function sendAuthorizationPost(studentsIds, fileName, fileContent) {
    postCall("/authorizations",
        {
            "subject": $("#authorizationSubjectInput").val(),
            "limitDate": $("#authorizationDateInput").val(),
            "message": $("#authorizationContentTextArea").val(),
            "studentsIds": studentsIds,
            "fileName": fileName,
            "fileContent": fileContent,
        },
        sendAuthorizationCallback
    );
}

function sendAuthorizationCallback(response) {
    if (!response.success) {
        alert(response.error);
        return;
    }

    $("#authorizationsTable tbody").prepend(
        "<tr>" +
        "<td>" + response.content.subject + "</td>" +
        "<td>" + getTodaysDate() + "</td>" +
        "<td>" + dateToString(response.content.limitDate.date) + "</td>" +
        "<td class='tableButton'><button class='infoButton' onclick='openViewAuthorizationDialog(" + response.content.id + ")'>Ver</button></td>" +
        "</tr>"
    );
    closeModal();
}

function filterAuthorizations() {
    var authorizationSubject = $("#authorizationSubjectInputFilter").val().toLowerCase();
    var authorizationMonth = $("#authorizationMonthDropdownFilter :selected").val();
    var authorizationState = parseInt($("#authorizationStateDropdownFilter :selected").val());
    $("#authorizationsTable > tbody > tr").each(function () {
        var rowAuthorizationState = dateComparator($(this).children().eq(2).text(), getTodaysDate());
        var rowAuthorizationSubject = $(this).children().eq(0).text().toLowerCase();
        var rowAuthorizationMonth = $(this).children().eq(1).text().substring(3, 5);
        if (rowAuthorizationSubject.includes(authorizationSubject) &&
            (rowAuthorizationMonth === authorizationMonth || authorizationMonth === "") &&
            (rowAuthorizationState === authorizationState || authorizationState === 0)) $(this).show();
        else $(this).hide();
    });
}

// POLLS
function openSendPollDialog() {
    $("#pollSubjectInput").val("");
    $("#pollDateInput").val("");
    $("#pollContentTextArea").val("");
    $("#newPollOptionInput").val("");
    $("#multipleChoiceCheckbox").prop('checked', false);
    $("#addedPollOptionsList").empty();
    $("#attachFileInput").val(null);
    resetTreeview("#sendPollModal");
    $("#sendPollModal").show();
}

function openViewPollDialog(pollId) {
    $("#viewPollModal #pollResultList").empty();
    getCall("/polls/" + pollId, openViewPollDialogCallback);
}

function openViewPollDialogCallback(response) {
    if (!response.success) {
        alert(response.error);
        return;
    }

    $("#viewPollModal .modalTitle").text(response.content.subject);
    $("#viewPollModalSendingDate").text(dateToString(response.content.sendingDate.date));
    $("#viewPollModalLimitDate").text(dateToString(response.content.limitDate.date));
    $("#viewPollModal .messageTextArea").text(response.content.message);
    $("#viewPollModal #attachedFiles").html(response.content.attachmentId === null ? "No hay archivos adjuntos" :
        "<a href='http://" + window.location.host + "/Hermerest_attachments/" + response.content.attachmentId + "' download='" + response.content.attachmentName + "'>" +
        response.content.attachmentName +
        "</a>");

    if (dateComparator($("#viewPollModalLimitDate").text(), getTodaysDate()) < 0)
        $("#viewPollModalLimitDate").addClass('pastDate');
    else
        $("#viewPollModalLimitDate").removeClass('pastDate');

    response.content.options.forEach(function (option) {
        $("#pollResultList").append(
            "<li>" + option[0] + ": <span class='pollResult'>" + option[1] + "</span></li>"
        )
    });

    var pollResults = [];
    $("#pollResultList .pollResult ").each(function () {
        pollResults.push($(this).text());
    });

    var mostVotedOptionValue = Math.max.apply(Math, pollResults);
    $("#pollResultList .pollResult ").each(function () {
        if (parseInt($(this).text()) === mostVotedOptionValue) {
            $(this).parent().css('color', '#4CAF50');
            $(this).parent().css("font-weight", "bold");
        }
    });

    $("#viewPollModal").show();
}

function addPollOption() {
    if ($("#newPollOptionInput").val().trim() === "") alert("La opción no debe estar vacía");
    else if ($("#addedPollOptionsList li:contains('" + $("#newPollOptionInput").val() + "')").length > 0) alert("La opción ya ha sido añadida")
    else {
        $("#addedPollOptionsList").append("<li>" + $("#newPollOptionInput").val() + "<a  class='deleteCross' onclick='deletePollOption(this)'>&times;</a></li>");
    }
}

function deletePollOption(button) {
    button.parentNode.remove();
}

function sendPoll() {
    var file = document.getElementById('attachFileInput').files[0];

    if ($("#pollSubjectInput").val().trim() === "" || $("#pollDateInput").val().trim() === "") alert("El asunto y la fecha límite de la circular no deben estar vacíos");
    else if (dateComparator(dateToString($("#pollDateInput").val()), getTodaysDate()) === -1) alert("La fecha límite es anterior a la actual");
    else if (file !== undefined && file.size > 10485760) alert("El archivo no debe superar los 10MB");
    else if ($("#sendPollModal .recipientStudentLi input:checkbox:checked").length === 0) alert("Marque algún destinatario");
    else if ($("#addedPollOptionsList").children().length < 2) alert("Indique, al menos, 2 opciones para la encuesta");
    else {
        var studentsIds = [];
        $(".treeview .recipientStudentLi").each(function () {
            if ($(this).children().first().is(':checked')) studentsIds.push(this.id);
        });

        if (file !== undefined) {
            var reader = new FileReader();
            reader.onload = function (event) {
                sendPollPost(studentsIds, file.name, event.target.result.replace(/^[^,]*,/, ''));
            };
            reader.readAsDataURL(file);
        } else sendPollPost(studentsIds, file.name, "");
    }
}

function sendPollPost(studentsIds, fileName, fileContent) {
    postCall("/polls",
        {
            "subject": $("#pollSubjectInput").val(),
            "limitDate": $("#pollDateInput").val(),
            "message": $("#pollContentTextArea").val(),
            "multipleChoice": $("#multipleChoiceCheckbox").is(':checked'),
            "studentsIds": studentsIds,
            "fileName": fileName,
            "fileContent": fileContent,
            "options": $("#addedPollOptionsList li").map(function () {
                return this.innerText.slice(0, -1)
            }).get()
        },
        sendPollCallback
    );
}

function sendPollCallback(response) {
    if (!response.success) {
        alert(response.error);
        return;
    }

    $("#pollsTable tbody").prepend(
        "<tr>" +
        "<td>" + response.content.subject + "</td>" +
        "<td>" + getTodaysDate() + "</td>" +
        "<td>" + dateToString(response.content.limitDate.date) + "</td>" +
        "<td class='tableButton'><button class='infoButton' onclick='openViewPollDialog(" + response.content.id + ")'>Ver</button></td>" +
        "</tr>"
    );
    closeModal();
}

function filterPolls() {
    var pollSubject = $("#pollSubjectInputFilter").val().toLowerCase();
    var pollMonth = $("#pollMonthDropdownFilter :selected").val();
    var pollState = parseInt($("#pollStateDropdownFilter :selected").val());
    $("#pollsTable > tbody > tr").each(function () {
        var rowPollState = dateComparator($(this).children().eq(2).text(), getTodaysDate());
        var rowPollSubject = $(this).children().eq(0).text().toLowerCase();
        var rowPollMonth = $(this).children().eq(1).text().substring(3, 5);
        if (rowPollSubject.includes(pollSubject) &&
            (rowPollMonth === pollMonth || pollMonth === "") &&
            (rowPollState === pollState || pollState === 0)) $(this).show();
        else $(this).hide();
    });
}