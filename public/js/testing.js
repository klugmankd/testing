var initQuestion = function (questions, index) {
    var mainTemplate = document.createElement('div');
    mainTemplate.classList.add('question-container');
    var titleTemplate = document.createElement('h4');
    titleTemplate.innerText = 'Question #' + (index + 1);
    mainTemplate.appendChild(titleTemplate);
    var bodyTemplate = document.createElement('p');
    bodyTemplate.innerText = questions[index].question.text;
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
            answers.push(parseInt(field.value));
        }
    }
    return answers
};

(function () {
    var testingBlock = $(".testing-block");
    var difficulty = testingBlock.data('difficulty');
    var direction = testingBlock.data('direction');
    var testPause = {};
    $.ajax({
        type: "GET",
        url: "http://127.0.0.1:8000/directions/" + direction+ "/" + difficulty,
        success: function (data) {
            data = (typeof data === 'string') ? JSON.parse(data) : data;
            console.log(data);
            var testId = data.id;
            for (var index = 0; index < data.length; index++) {
                var question = data[index].question;
                for (var inIndex = 0; inIndex < question.answers.length; inIndex++) {
                    var answer = question.answers[inIndex];
                    delete answer['is_correct'];
                }
            }
            console.log(data);

            var questionBlock = document.querySelector(".question-block");
            index = 0;
            questionBlock.appendChild(initQuestion(data, index));
            questionBlock.appendChild(initAnswers(data[index].question));
            var userAnswers = [];

            // for pause functional
            testPause = {id: data.id};
            testPause['questions'] = [];

            var question = {}, points = 0, testData = data;
            $(document).on("click", ".reply", function () {
                var answers = searchFields();
                var questionId = data[index].question.id;

                // for pause functional
                testPause['questions'].push(data[index].question);
                delete testPause['questions'][index]['answers'];
                testPause['questions'][index]['answers'] = [];
                for (var answerIndex in answers) {
                    testPause['questions'][index]['answers'].push(answers[answerIndex]);
                }

                $.ajax({
                    type: "POST",
                    url: "http://127.0.0.1:8000/answers/check",
                    data: {
                        test: testId,
                        question: questionId,
                        answers: answers
                    },
                    success: function (data) {
                        data = (typeof data === 'string') ? JSON.parse(data) : data;
                        console.log(data);
                        // points += data.points;
                        index++;
                        if (testData[index].question !== undefined) {
                            questionBlock.innerHTML = '';
                            questionBlock.appendChild(initQuestion(testData, index));
                            questionBlock.appendChild(initAnswers(testData[index].question));
                        } /*else {
                            console.log(points);
                        }*/
                    }
                });

                // userAnswers.push(question);
                // question[questionId] = answers;
                // console.log(question);
                // index++;
                // if (data[index] !== undefined) {
                //     questionBlock.innerHTML = '';
                //     questionBlock.appendChild(initQuestion(data, index));
                //     questionBlock.appendChild(initAnswers(data[index].question));
                // } else {
                //     $(".reply").remove();
                //     console.log(question)
                    // $.ajax({
                    //     type: "POST",
                    //     url: "http://127.0.0.1:8000/tests/check",
                    //     data: {test: testId, answers: question},
                    //     success: function (data) {
                    //         data = (typeof data === 'string') ? JSON.parse(data) : data;
                    //         $(".testing-block").append("<h4>Your score in the direction: " + data.userResult.result + "</h4>");
                    //         console.log(data);
                    //     }
                    // });
                // }
            });

            $(document).on("click", ".pause-test", function () {
                $.ajax({
                    type: "PUT",
                    url: "http://127.0.0.1:8000/save-test",
                    data: {test: JSON.stringify(testPause)},
                    success: function (data) {
                        $(".message").text("Test on pause");
                        console.log(data);
                    }
                })
            })
        }
    });
})();
