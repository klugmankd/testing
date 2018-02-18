var initQuestion = function (questions, index) {
    var mainTemplate = document.createElement('div');
    mainTemplate.classList.add('question-container');
    var titleTemplate = document.createElement('h4');
    titleTemplate.innerText = 'Question #' + (index + 1);
    mainTemplate.appendChild(titleTemplate);
    var bodyTemplate = document.createElement('p');
    bodyTemplate.innerText = questions[index].text;
    mainTemplate.appendChild(bodyTemplate);

    return mainTemplate;
};

var initAnswers = function (question) {
    var answers = question.answers;
    var mainTemplate = document.createElement('div');
    mainTemplate.classList.add('answers-container');
    for (var index = 0; index < answers.length; index++) {
        var answer = answers[index];
        var answerContainer = document.createElement('div');
        answerContainer.classList.add('answer-container');
        var answerNode = document.createElement('input');
        answerNode.type = 'checkbox';
        answerNode.classList.add('input');
        answerNode.id = 'answer' + (index + 1);
        answerNode.name = 'answer' + (index + 1);
        answerNode.value = answer.id;
        var answerTextNode = document.createElement('label');
        answerTextNode.setAttribute("for", 'answer' + (index + 1));
        answerTextNode.innerText = answer.text;
        answerContainer.appendChild(answerNode);
        answerContainer.appendChild(answerTextNode);
        mainTemplate.appendChild(answerContainer);
    }
    return mainTemplate;
};

var searchFields = function () {
    var fields = document.querySelectorAll(".answer-container .input");
    var answers = [];
    for (var index = 0; index < fields.length; index++) {
        var field = fields[index];
        if (field.checked) {
            answers.push(field.value);
        }
    }
    return answers
};

(function () {
    $.ajax({
        type: "GET",
        url: "http://127.0.0.1:8000/tests/random/9/2",
        success: function (data) {
            data = (typeof data === 'string') ? JSON.parse(data) : data;
            var testId = data.id;
            for (var index = 0; index < data.questions.length; index++) {
                var question = data.questions[index];
                for (var inIndex = 0; inIndex < question.answers.length; inIndex++) {
                    var answer = question.answers[inIndex];
                    delete answer['is_correct'];
                }
            }
            var questionBlock = document.querySelector(".question-block");
            index = 0;
            questionBlock.appendChild(initQuestion(data.questions, index));
            questionBlock.appendChild(initAnswers(data.questions[index]));
            var userAnswers = [];

            $(document).on("click", ".reply", function () {
                var answers = searchFields();
                var questionId = data.questions[index].id;
                userAnswers.push({question: questionId, answers: answers});
                index++;
                if (data.questions[index] !== undefined) {
                    questionBlock.innerHTML = '';
                    questionBlock.appendChild(initQuestion(data.questions, index));
                    questionBlock.appendChild(initAnswers(data.questions[index]));
                } else {
                    console.log(userAnswers);
                    $.ajax({
                        type: "POST",
                        url: "http://127.0.0.1:8000/tests/check",
                        data: {test: testId, answers: userAnswers},
                        success: function (data) {
                            console.log(data);
                        }
                    });
                }
            });
        }
    });
})();
