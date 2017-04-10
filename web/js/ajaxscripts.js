function postCall(url, postData, callback) {
    $.ajax({
        url: encodeURI(generateUrl(url)),
        type: 'POST',
        // data: JSON.stringify(postData),
        data: postData,
        //dataType: "json",

        success: function (response) {
            callback(response);
        },

        error: function (response, textStatus, errorThrown) {
            // $("#divError").text("Error al realizar la petición");
            alert("Error al realizar la petición");
        }

    });
}

function getCall(url, callback) {
    $.ajax({
        url: encodeURI(generateUrl(url)),
        type: 'GET',
        //dataType: "json",

        success: function (response) {
            callback(response);
        },

        error: function (response, textStatus, errorThrown) {
            // $("#divError").text("Error al realizar la petición");
            alert("Error al realizar la petición");
        }

    });
}

function generateUrl(url) {
    return "/Hermerest/web/app_dev.php" + url;
}

// function generateUrl(url) {
//     return url.substring(0, getPosition(url) + 1) +
//             "Hermerest/web/app_dev.php" +
//         url.substring(getPosition(url), url.length + 1);
// }
//
// function getPosition(url) {
//     return url.split("/", 3).join("/").length;
// }