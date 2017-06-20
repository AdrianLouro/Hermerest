function getCall(url, callback) {
    $.ajax({
        url: encodeURI(generateUrl(url)),
        type: 'GET',
        async: false,

        success: function (response) {
            callback(response);
        },

        error: function (response, textStatus, errorThrown) {
            alert("Error al realizar la petición");
        }

    });
}

function postCall(url, postData, callback) {
    $.ajax({
        url: encodeURI(generateUrl(url)),
        type: 'POST',
        data: postData,
        async: false,

        success: function (response) {
            callback(response);
        },

        error: function (response, textStatus, errorThrown) {
            alert("Error al realizar la petición");
        }

    });
}

function putCall(url, postData, callback) {
    $.ajax({
        url: encodeURI(generateUrl(url)),
        type: 'PUT',
        data: postData,
        async: false,

        success: function (response) {
            callback(response);
        },

        error: function (response, textStatus, errorThrown) {
            alert("Error al realizar la petición");
        }

    });
}

function patchCall(url, postData, callback) {
    $.ajax({
        url: encodeURI(generateUrl(url)),
        type: 'PATCH',
        data: postData,
        async: false,

        success: function (response) {
            callback(response);
        },

        error: function (response, textStatus, errorThrown) {
            alert("Error al realizar la petición");
        }

    });
}

function deleteCall(url, postData, callback) {
    $.ajax({
        url: encodeURI(generateUrl(url)),
        type: 'DELETE',
        data: postData,
        async: false,

        success: function (response) {
            callback(response);
        },

        error: function (response, textStatus, errorThrown) {
            alert("Error al realizar la petición");
        }

    });
}

function generateUrl(url) {
    return "/Hermerest/web/app_dev.php" + url;
}