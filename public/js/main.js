var route = $('.controller').data('route');

var searchFields = function (object) {
    var action = $(object).parent().parent().data('action');
    var fields = document.querySelectorAll("." + action + 'Form .input');
    var data = {};

    for (var index = 0; index < fields.length; index++) {
        var field = fields[index];
        switch (field.type) {
            case 'checkbox':
                field.value = field.checked;
                break;
            default:
                break;
        }

        data[field.name] = field.value;
    }

    return {
        data: data,
        action: action
    };
};


$(document).on('click', '.createForm .form-group button', function () {
    /*var second = 0;
    var data = {question: 1, text: "true", isCorrect: true};
    var interval = setInterval(function () {
        second++;
        if (second === 41) {
            clearInterval(interval);
        }
        console.log(second);
        $.ajax({
            type: "POST",
            url: route,
            data: data,//data.data,
            success: function(data) {
                console.log(data);
            }
        });
        if (second % 2 === 0) {
            data = {question: data.question++, text: "false", isCorrect: false}
        } else {
            data = {question: data.question, text: "true", isCorrect: true}
        }
    }, 500);*/
    var data = searchFields(this);
    $.ajax({
        type: "POST",
        url: route,
        data: data.data,
        success: function(data) {
            console.log(data);
        }
    });
});

$(document).on('click', '.readAllForm .form-group button', function () {
    $.ajax({
        type: "GET",
        url: route,
        success: function(data) {
            data = (typeof data === 'string') ? JSON.parse(data) : data;
            console.log(data);
        }
    })
});

$(document).on('click', '.readForm .form-group button', function () {
    var data = searchFields(this);
    $.ajax({
        type: "GET",
        url: route + "/" + data.data.id,
        success: function(data) {
            data = (typeof data === 'string') ? JSON.parse(data) : data;
            console.log(data);
        }
    })
});

$(document).on('click', '.updateForm .form-group button', function () {
    /*var second = 0;
    var data = {id: 176, question: 47, text: "true", isCorrect: true};
    var interval = setInterval(function () {
        second++;
        if (data.question === 232) {
            clearInterval(interval);
        }
        console.log(second);
        $.ajax({
            type: "PUT",
            url: route + "/" + data.id,
            data: data,
            success: function(data) {
                console.log(data);
            }
        });
        data['id'] = data['id'] + 1;
        if (second % 2 === 0) {
            data['text'] = "false";
            data['isCorrect'] = false;
            data['question'] = data.question + 1;
        } else {
            data['text'] = "true";
            data['isCorrect'] = true;
        }
    }, 500);*/

    var data = searchFields(this);
    $.ajax({
        type: "PUT",
        url: route + "/" + data.data.id,
        data: data.data,
        success: function(data) {
            console.log(data);
        }
    })
});

$(document).on('click', '.deleteForm .form-group button', function () {
    var data = searchFields(this);
    $.ajax({
        type: "DELETE",
        url: route + "/" + data.data.id,
        success: function(data) {
            console.log(data);
        }
    })
});


$(document).on('click', '.readRandomForm .form-group button', function () {
    var data = searchFields(this);
    $.ajax({
        type: "GET",
        url: route + '/random/' + data.data.direction + '/' + data.data.difficulty,
        success: function(data) {
            data = (typeof data === 'string') ? JSON.parse(data) : data;
            console.log(data);
        }
    })
});