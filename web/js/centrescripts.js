// CLASSES
function handleAutoimportFile(files) {
    if (files[0] === undefined) return;


    $("#confirmModalAcceptButton").click(function () {
        var selectedFile = files[0];
        var reader = new FileReader();
        reader.onload = function (event) {
            autoimportClasses(event.target.result);
        };
        reader.readAsText(selectedFile);
        $("#files").val("");

        closeAlertModal();
    });

    $("#confirmModalCancelButton").click(function () {$("#files").val("");});
    $("#confirm_modal .close").click(function () {$("#files").val("");});

    confirmAlert("¿Está seguro de que desea autoimportar los datos desde el fichero \"" + files[0].name + "\"?");
}

function autoimportClasses(data) {
    var lines = data.split("\r\n");
    var currentClass = "";
    var currentStudents = "";

    lines.forEach(
        function (line) {
            if (!line.includes(",")) {
                autoimportClass(currentClass, currentStudents);
                currentClass = line;
                currentStudents = "";
            } else currentStudents += line + "\n";

        });

    autoimportClass(currentClass, currentStudents);
}

function autoimportClass(className, students) {
    if (className.trim().length === 0) return;

    console.log("autoimportando la clase: " + className + "\n");
    console.log(students + "\n");

    postCall("/classes/autoimport",
        {"className": className, "students": students.split("\n")},
        autoimportClassCallback
    );
}

function autoimportClassCallback(response) {
    if (!response.success) {
        warningAlert(response.error);
        return;
    }

    $("#classesTable").append(
        "<tr>" +
        "<td>" + response.content.name + "</td>" +
        "<td>" + response.content.students + "</td>" +
        "<td class='tableButton'><button class='infoButton'><a href='classes/" + response.content.id + "'>Ver</a></button></td>" +
        "</tr>"
    );
}


function openNewClassDialog() {
    $("#classNameInput").val("");
    $("#studentNameFilterInput").val("");
    $("#addedStudentsList li").each(function () {
        $("#studentsDropdown").append("<option value='" + $(this).attr('id') + "'>" + $(this).text().slice(0, -1) + "</option>");
    });
    $("#addedStudentsList").empty();
    hideNotMatchingStudentsFromStudentsDropdown();
    $("#newClassModal").fadeIn(100);
}

function addNewClass(centreId) {
    var className = $("#classNameInput").val();
    if (className.trim().length === 0) warningAlert("Rellene todos los campos");
    else {
        postCall("/classes",
            {"className": className, "centreId": centreId},
            addNewClassCallback
        );
    }
}

function addNewClassCallback(response) {
    if (!response.success) {
        warningAlert(response.error);
        return;
    }
    $("#classesTable").append(
        "<tr>" +
        "<td>" + response.content.name + "</td>" +
        "<td>0</td>" +
        "<td class='tableButton'><button class='infoButton' onclick='window.location.href=\"classes/" + response.content.id + "\"'>Ver</button></td>" +
        "</tr>"
    );

    addStudentsToClass(response.content.id, addStudentsToClassCallback);
}


// CLASS
function openEditClassDialog() {
    $("#classNameInput").val($("#className").text());
    $("#editClassModal").fadeIn(100);
}

function openAddStudentDialog() {
    $("#studentNameFilterInput").val("");
    hideNotMatchingStudentsFromStudentsDropdown();
    $("#addedStudentsList").empty();
    $("#addStudentModal").fadeIn(100);
}

function editClass(classId) {
    var className = $("#classNameInput").val();
    if (className.trim().length === 0) warningAlert("Rellene todos los campos");
    else {
        patchCall("/classes/" + classId,
            {"className": className},
            editClassCallback
        );
    }
}

function editClassCallback(response) {
    if (!response.success) {
        warningAlert(response.error);
        return;
    }
    $("#className").text(response.content.name);
    closeModal();
}

function deleteStudentFromClass(studentId, classId) {
    deleteCall("/classes/" + classId + "/students/" + studentId, {}, deleteStudentFromClassCallback);
}

function deleteStudentFromClassCallback(response) {
    if (!response.success) {
        warningAlert(response.error);
        return;
    }

    $("#studentsTable #" + response.content.id).remove();
    $("#studentsDropdown").append(
        "<option value='" + response.content.id + "'>" +
        response.content.surname + ", " + response.content.name +
        "</option>");
    $("#numberOfStudents").text($("#numberOfStudents").text() - 1);
}

function addStudentToStudentsList() {
    var studentId = $("#studentsDropdown :selected").val();
    var studentFullname = $("#studentsDropdown :selected").text();
    if (studentId === '-1') {
        warningAlert("Seleccione un alumno");
        return;
    }

    $("#addedStudentsList").append("<li id='" + studentId + "'>" + studentFullname + "<a class='deleteCross' onclick='deleteStudentFromStudentsList(this)'>&times;</a></li>");
    $("#studentsDropdown [value='" + studentId + "']").remove();
}

function deleteStudentFromStudentsList(button) {
    button.parentNode.remove();
    $("#studentsDropdown").append("<option value='" + $(button).parent().attr('id') + "'>" + $(button).parent().text().slice(0, -1) + "</option>");
    hideNotMatchingStudentsFromStudentsDropdown();
}

function addStudentsToClass(classId, callback) {
    var studentsIds = [];
    $("#addedStudentsList li").each(function () {
        studentsIds.push($(this).attr('id'));
    });


    if (studentsIds.length === 0 && callback !== addStudentsToClassCallback) {
        warningAlert("Seleccione algún alumno");
        return;
    }

    if (studentsIds.length === 0 && callback === addStudentsToClassCallback) {
        closeModal();
        return;
    }

    for (var i = 0; i < studentsIds.length; i++)
        postCall("/classes/" + classId + "/students/" + studentsIds[i], {}, callback);
}

function addStudentsToClassAndShowItCallback(response) {
    if (!response.success) {
        warningAlert(response.error);
        return;
    }

    $("#studentsTable").append(
        "<tr id='" + response.content.id + "'>" +
        "<td>" + response.content.surname + ", " + response.content.name + "</td>" +
        "<td class='tableButton'><button class='infoButton' onclick='window.location.href=\"../students/" + response.content.id + "\"'>Ver</button></td>" +
        "<td class='tableButton'><button class='warningButton' onclick='deleteStudentFromClass(" + response.content.id + ")'>Eliminar</button></td>" +
        "</tr>"
    );

    $("#studentsDropdown [value='" + response.content.id + "']").remove();
    $("#numberOfStudents").text(parseInt($("#numberOfStudents").text()) + 1);
    closeModal();
}

function addStudentsToClassCallback(response) {
    if (!response.success) {
        warningAlert(response.error);
        return;
    }
    var numberOfStudentsOfCurrentClass = parseInt($("#classesTable tr").last().children().eq(1).text());
    $("#classesTable tr td:first-child").each(function () {
        if ($(this).text() === response.content.oldClassName) {
            $(this).next().text(parseInt($(this).next().text()) - 1);
            return;
        }
    });
    $("#classesTable tr").last().children().eq(1).text(numberOfStudentsOfCurrentClass + 1);
    closeModal();
}

function hideNotMatchingStudentsFromStudentsDropdown() {
    if ($("#studentNameFilterInput").val().trim() === "") {
        $("#studentsDropdown").val('-1');
        $("#studentsDropdown > option").show();
        return;
    }

    var numberOfVisibleOptions = 0;
    $("#studentsDropdown > option").each(function () {
        if ($(this).text().toLowerCase().includes($("#studentNameFilterInput").val().toLowerCase())) {
            $(this).show();
            $(this).prop('selected', true);
            numberOfVisibleOptions++;
        } else $(this).hide();
    });

    if (numberOfVisibleOptions === 0) $("#studentsDropdown").val('-1');
}

function deleteClass(classId) {
    $("#confirmModalAcceptButton").click(function () {
        deleteCall("/classes/" + classId, {}, deleteClassCallback);
        closeAlertModal();
    });
    confirmAlert("¿Está seguro de que desea eliminar el curso?");
}

function deleteClassCallback(response) {
    if (!response.success) {
        warningAlert(response.error);
        return;
    }

    window.location.replace("../classes");
}


// STUDENTS
function openRegisterStudentDialog() {
    $("#studentNameInput").val("");
    $("#studentSurnameInput").val("");
    $("#parentTelephoneInput").val("");
    $("#parentFullnameInput").val("");
    $("#registerStudentModal #parentFullnameInput").prop("disabled", true);
    $("#addedParentsList").empty();
    $("#registerStudentModal").fadeIn(100);
}

function filterStudents() {
    var className = $("#classFilterDropdown :selected").val();
    var studentName = $("#studentNameFilterInput").val().toLowerCase();
    $("#studentsTable > tbody > tr").each(function () {
        var rowClassName = $(this).children().eq(1).text();
        var rowStudentName = $(this).children().eq(0).text().toLowerCase();
        if ((rowClassName === className || className === " " || (className === "" && rowClassName === "-"))
            && rowStudentName.includes(studentName))
            $(this).show();
        else $(this).hide();
    });
}

function addParentToParentsList() {
    if ($("#parentFullnameInput").is(':disabled')) {
        warningAlert("No se ha encontrado un padre con ese número de teléfono");
        return;
    }

    if ($("#addedParentsList #" + $("#parentTelephoneInput").val()).length > 0 ||
        $("#parentsTable td:first-child:contains('" + $("#parentTelephoneInput").val() + "')").length > 0) {
        warningAlert("El padre ya ha sido añadido");
        return;
    }

    $("#addedParentsList").append("<li id='" + $("#parentTelephoneInput").val() + "'>" + $("#parentFullnameInput").val() + "<a class='deleteCross' onclick='deleteParentFromParentsList(this)'>&times;</a></li>");
}

function deleteParentFromParentsList(button) {
    button.parentNode.remove();
}

function registerStudent() {
    var studentName = $("#studentNameInput").val()
    var studentSurname = $("#studentSurnameInput").val()
    var studentClass = $("#classDropdown :selected").val();

    if (studentName.trim() === "" || studentSurname.trim() === "") {
        warningAlert("Rellene todos los campos");
        return;
    }

    postCall("/students",
        {"studentName": studentName, "studentSurname": studentSurname, "studentClass": studentClass},
        registerStudentCallback
    );

}

function registerStudentCallback(response) {
    if (!response.success) {
        warningAlert(response.error);
        return;
    }

    $("#studentsTable").append(
        "<tr>" +
        "<td>" + response.content.surname + ", " + response.content.name + "</td>" +
        "<td>" + response.content.class + "</td>" +
        "<td class='tableButton'><button class='infoButton' onclick='window.location.href=\"students/" + response.content.id + "\"'>Ver</button></td>" +
        "</tr>"
    );

    addParents(response.content.id);
    closeModal();
}

$(".modal-body_content #parentTelephoneInput").on('input', function () {
    var parentTelephone = $(this).val();

    if (isNaN(parentTelephone) || parentTelephone.length !== 9) {
        $("#parentFullnameInput").prop("disabled", true);
        $("#parentFullnameInput").val("");
        return;
    }

    getCall("/parents?telephone=" + parentTelephone,
        searchParentByTelephoneCallback
    );
});

function searchParentByTelephoneCallback(response) {
    if (!response.success) {
        warningAlert(response.error);
        return;
    }

    if (response.content.found) {
        $("#parentTelephoneInput").val(response.content.telephone);
        $("#parentFullnameInput").val(response.content.fullname);
        $("#parentFullnameInput").prop("disabled", false);
    } else {
        $("#parentFullnameInput").prop("disabled", true);
        $("#parentFullnameInput").val("");
    }
}

// STUDENT
function openEditStudentDialog() {
    $("#studentNameInput").val($("#studentName").text());
    $("#studentSurnameInput").val($("#studentSurname").text());

    $("#classDropdown option").each(function () {
        if ($(this).text() === $("#studentClass").text()) $(this).prop('selected', true);
        // else $(this).prop('selected', false);
    });

    $("#editStudentModal").fadeIn(100);
}

function openAddParentDialog() {
    $("#parentTelephoneInput").val("");
    $("#parentFullnameInput").val("");
    $("#addParentModal #parentFullnameInput").prop("disabled", true);
    $("#addedParentsList").empty();
    $("#addParentModal").fadeIn(100);
}

function editStudent(studentId) {
    var studentName = $("#studentNameInput").val()
    var studentSurname = $("#studentSurnameInput").val()
    var studentClass = $("#classDropdown :selected").val();

    if (studentName.trim() === "" || studentSurname.trim() === "") {
        warningAlert("Rellene todos los campos");
        return;
    }

    patchCall("/students/" + studentId,
        {
            "studentName": studentName,
            "studentSurname": studentSurname,
            "studentClass": studentClass
        },
        editStudentCallback
    );

}

function editStudentCallback(response) {
    if (!response.success) {
        warningAlert(response.error);
        return;
    }

    $("#studentFullName").text(response.content.name + " " + response.content.surname);
    $("#studentName").text(response.content.name);
    $("#studentSurname").text(response.content.surname);
    $("#studentClass").text(response.content.class);
    closeModal();
}

function deleteStudent(studentId) {
    $("#confirmModalAcceptButton").click(function () {
        deleteCall("/students/" + studentId, {}, deleteStudentCallback);
        closeAlertModal();
    });
    confirmAlert("¿Está seguro de que desea eliminar al alumno?");
}

function deleteStudentCallback(response) {
    if (!response.success) {
        warningAlert(response.error);
        return;
    }

    window.location.replace("../students");
}

function addParents(studentId) {
    var parentsTelephones = [];

    $("#addedParentsList li").each(function () {
        parentsTelephones.push($(this).attr('id'));
    });

    for (var i = 0; i < parentsTelephones.length; i++)
        postCall("/students/" + studentId + "/parents/" + parentsTelephones[i], {}, addParentsCallback);

}

function addParentsCallback(response) {
    if (!response.success) {
        warningAlert(response.error);
        return;
    }

    $("#parentsTable").append(
        "<tr id='" + response.content.id + "'>" +
        "<td>" + response.content.telephone + "</td>" +
        "<td>" + response.content.fullname + "</td>" +
        "<td class='tableButton'><button class='warningButton' onclick='deleteParent(" + response.content.studentId + "," + response.content.id + ")'>Eliminar</button></td>" +
        "</tr>"
    );

    closeModal();
}

function deleteParent(studentId, parentId) {
    $("#confirmModalAcceptButton").click(function () {
        deleteCall("/students/" + studentId + "/parents/" + parentId, {}, deleteParentCallback);
        closeAlertModal();
    });
    confirmAlert("¿Está seguro de que desea eliminar al padre?");
}

function deleteParentCallback(response) {
    if (!response.success) {
        warningAlert(response.error);
        return;
    }

    $("#parentsTable #" + response.content.id).remove();
}