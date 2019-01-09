survey = { questions: undefined,
           firstQuestionDisplayed: -1,
           lastQuestionDisplayed: -1};

(function (survey, $) {

    survey.setup_survey = function(questions) {
        var self = this;
        this.questions = questions;
        this.started_at = (new Date()).toISOString();

        if(answerData){
            this.previousAnswers = JSON.parse(answerData.answers);
        }
        this.questions.forEach(function(question) {

            self.generateQuestionElement( question );
        });

        $('#step2Btn').hide();


        $('#step2Btn').click(function() {
            if ( !$('#step2Btn').hasClass('disabled') ) {
                self.goToNextStep();
            }
        });
      
        $('#backBtn').click(function() {
            if ( !$('#backBtn').hasClass('disabled') ) {
                self.showPreviousQuestionSet();
            }
        });
      
        $('#nextBtn').click(function() {
            var ok = true;
            for (i = self.firstQuestionDisplayed; i <= self.lastQuestionDisplayed; i++) {
                if (self.questions[i]['required'] === true && !self.getQuestionAnswer(questions[i])) {
                    $('.question-container > div.question:nth-child(' + (i+1) + ') > .required-message').show();
                    ok = false;
                }
            }
            if (!ok)
                return

            if ( $('#nextBtn').text().indexOf('Continue') === 0 ) {
                self.showNextQuestionSet();
            }
            else {
                var answers = {};
            
                for (i = 0; i < self.questions.length; i++) {
                    answers[self.questions[i].id] = self.getQuestionAnswer(self.questions[i]);
                }

                $.ajax({type: 'POST',
                        url: 'http://localhost/wiki/survey/answers.php',
                        dataType: "JSON",
                        data: {started_at: self.started_at, ended_at: (new Date()).toISOString(),answers: answers},
                        processData: true,
                        success: function(response) {
                       // response = JSON.parse(response);
                            self.hideAllQuestions();
                            $('#nextBtn').hide();
                            $('#backBtn').hide();
                            $('.completed-message').html('Thank you for participating in this survey!<br><br>'+response['success']);
                            $('#step2Btn').show();
                            
                        },
                        error: function(response) {
                            self.hideAllQuestions();
                            $('#nextBtn').hide();
                            $('#backBtn').hide();
                            $('.completed-message').text('An error occurred: could not send data to server');
                        }
                });
            }
        });
      
        this.showNextQuestionSet();
         
    }

    survey.getQuestionAnswer = function(question) {
        var result;
        if ( question.type === 'single-select' ) {
            result = $('input[type="radio"][name="' + question.id + '"]:checked').val();
        }
        else if ( question.type === 'single-select-oneline' ) {
            result = $('input[type="radio"][name="' + question.id + '"]:checked').val();
        }
        else if ( question.type === 'text-field-small' ) {
            result = $('input[name=' + question.id + ']').val();
        }
        else if ( question.type === 'text-field-large' ) {
            result = $('textarea[name=' + question.id + ']').val();
        }
        return result ? result : undefined;
    }

    survey.generateQuestionElement = function(question) {
        var questionElement = $('<div id="' + question.id + '" class="question"></div>');
        var questionTextElement = $('<div class="question-text"></div>');
        var questionAnswerElement = $('<div class="answer"></div>');
        var questionCommentElement = $('<div class="comment"></div>');
        questionElement.appendTo($('.question-container'));
        questionElement.append(questionTextElement);
        questionElement.append(questionAnswerElement);
        questionElement.append(questionCommentElement);
        questionTextElement.html(question.text);
        questionCommentElement.html(question.comment);

        if(answerData){
                var selectedOption = this.previousAnswers[question.id];
        }

        if ( question.type === 'single-select' ) {
            questionElement.addClass('single-select');
            question.options.forEach(function(option) {
                if(selectedOption && option == selectedOption){
                questionAnswerElement.append('<label class="radio"><input type="radio" checked value="' + option + '" name="' + question.id + '"/>' + option + '</label>');
            }else{
                   questionAnswerElement.append('<label class="radio"><input type="radio" value="' + option + '" name="' + question.id + '"/>' + option + '</label>');
            }
            });
        }
        else if ( question.type === 'text-field-small' ) {
            questionElement.addClass('text-field-small');
            questionAnswerElement.append('<input type="text" value="" class="text" name="' + question.id + '">');
        }
        else if ( question.type === 'text-field-large' ) {
            questionElement.addClass('text-field-large');
            questionAnswerElement.append('<textarea rows="8" cols="0" class="text" name="' + question.id + '">');
        }
        if ( question.required === true ) {
            var last = questionTextElement.find(':last');
            (last.length ? last : questionTextElement).append('<span class="required-asterisk" aria-hidden="true">*</span>');
        }


        questionAnswerElement.after('<div class="required-message">This is a required question</div>');
        questionElement.hide();
    }

    survey.hideAllQuestions = function() {
        $('.question:visible').each(function(index, element){
            $(element).hide();
        });
        $('.required-message').each(function(index, element){
            $(element).hide();
        });
    }

    survey.showNextQuestionSet = function() {
        this.hideAllQuestions();
        this.firstQuestionDisplayed = this.lastQuestionDisplayed+1;
      
        do {
            this.lastQuestionDisplayed++;  
            $('.question-container > div.question:nth-child(' + (this.lastQuestionDisplayed+1) + ')').show();
            if ( this.questions[this.lastQuestionDisplayed]['break_after'] === true)
                break;
        } while ( this.lastQuestionDisplayed < this.questions.length-1 );
      
        this.doButtonStates();
    }


    survey.goToNextStep = function(){
       $(location).attr('href','http://localhost/wiki');
    }

    survey.showPreviousQuestionSet = function() {
        this.hideAllQuestions();
        this.lastQuestionDisplayed = this.firstQuestionDisplayed-1;
      
        do {
            this.firstQuestionDisplayed--;  
            $('.question-container > div.question:nth-child(' + (this.firstQuestionDisplayed+1) + ')').show();
            if ( this.firstQuestionDisplayed > 0 && this.questions[this.firstQuestionDisplayed-1]['break_after'] === true)
                break;
        } while ( this.firstQuestionDisplayed > 0 );
      
        this.doButtonStates();
    }

    survey.doButtonStates = function() {
        if ( this.firstQuestionDisplayed == 0 ) {
            $('#backBtn').addClass('invisible');  
        }
        else if ( $('#backBtn' ).hasClass('invisible') ) {
            $('#backBtn').removeClass('invisible');
        }
        
        if ( this.lastQuestionDisplayed == this.questions.length-1 ) {
            $('#nextBtn').text('Finish');
            $('#nextBtn').addClass('blue');  
        }
        else if ( $('#nextBtn').text() === 'Finish' ) {
            $('#nextBtn').text('Continue »'); 
            $('#nextBtn').removeClass('blue');
        }
    }
})(survey, jQuery);


$(document).ready(function(){
    $.getJSON('questions.json', function(json) {
        survey.setup_survey(json);        
    });
});

window.onbeforeunload = function() {
    return "This will reset all answers that you've already filled in!";
}