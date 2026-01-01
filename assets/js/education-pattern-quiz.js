/**
 * TradePress Pattern Quiz JavaScript
 *
 * Handles the interactive functionality for the candlestick pattern identification quiz.
 *
 * @package TradePress\Education
 * @since 1.0.0
 */

(function($) {
    'use strict';
    
    /**
     * Pattern Quiz functionality
     */
    const TradePress_PatternQuiz = {
        /**
         * Initialize the quiz functionality
         */
        init: function() {
            // Only run on pages with the quiz container
            if (!$('#tradepress-pattern-quiz').length) {
                return;
            }
            
            this.container = $('#tradepress-pattern-quiz');
            this.quizId = this.container.data('quiz-id');
            this.currentQuestion = 0;
            this.score = 0;
            this.totalQuestions = this.container.data('total-questions');
            
            this.bindEvents();
            this.showQuestion(0); // Show first question
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            const self = this;
            
            // Answer selection handling
            this.container.on('click', '.pattern-option', function(e) {
                e.preventDefault();
                
                const $option = $(this);
                const questionId = $option.closest('.pattern-question').data('question-id');
                const answerIndex = $option.data('option-index');
                
                self.submitAnswer(questionId, answerIndex, $option);
            });
            
            // Next question button
            this.container.on('click', '.next-question', function(e) {
                e.preventDefault();
                self.nextQuestion();
            });
        },
        
        /**
         * Show a specific question
         * 
         * @param {int} index Question index to show
         */
        showQuestion: function(index) {
            const $questions = this.container.find('.pattern-question');
            
            if (index >= $questions.length) {
                this.showResults();
                return;
            }
            
            // Hide all questions and show the current one
            $questions.hide();
            $questions.eq(index).fadeIn();
            
            // Update progress indicator
            this.updateProgress(index);
        },
        
        /**
         * Submit an answer via AJAX
         * 
         * @param {int} questionId   Question ID
         * @param {int} answerIndex  Index of the selected answer
         * @param {jQuery} $option   jQuery object of the selected option
         */
        submitAnswer: function(questionId, answerIndex, $option) {
            const self = this;
            const $question = $option.closest('.pattern-question');
            
            // Disable further selections
            $question.find('.pattern-option').addClass('disabled');
            $option.addClass('selected');
            
            // Send AJAX request
            $.ajax({
                url: tradepress_vars.ajax_url,
                type: 'POST',
                data: {
                    action: 'tradepress_submit_pattern_answer',
                    nonce: tradepress_vars.pattern_quiz_nonce,
                    question_id: questionId,
                    answer_index: answerIndex
                },
                success: function(response) {
                    if (response.success) {
                        self.handleAnswerResult(response.data, $question, answerIndex);
                    } else {
                        console.error('Error submitting answer:', response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', error);
                }
            });
        },
        
        /**
         * Handle answer submission result
         * 
         * @param {Object} data      Response data
         * @param {jQuery} $question Question container
         * @param {int} answerIndex  Index of the selected answer
         */
        handleAnswerResult: function(data, $question, answerIndex) {
            const $options = $question.find('.pattern-option');
            const $explanation = $question.find('.pattern-explanation');
            const $nextButton = $question.find('.next-question');
            
            // Update score if correct
            if (data.is_correct) {
                this.score++;
                $options.eq(answerIndex).addClass('correct');
            } else {
                $options.eq(answerIndex).addClass('incorrect');
                $options.eq(data.correct_index).addClass('correct');
            }
            
            // Show explanation and next button
            $explanation.html(data.explanation).slideDown();
            $nextButton.show();
        },
        
        /**
         * Go to next question
         */
        nextQuestion: function() {
            this.currentQuestion++;
            this.showQuestion(this.currentQuestion);
        },
        
        /**
         * Show quiz results
         */
        showResults: function() {
            const percentage = Math.round((this.score / this.totalQuestions) * 100);
            const $results = this.container.find('.quiz-results');
            
            // Update result display
            $results.find('.score-value').text(this.score);
            $results.find('.total-value').text(this.totalQuestions);
            $results.find('.percentage-value').text(percentage + '%');
            
            // Show appropriate message based on score
            $results.find('.result-message').hide();
            
            if (percentage >= 80) {
                $results.find('.result-excellent').show();
            } else if (percentage >= 60) {
                $results.find('.result-good').show();
            } else {
                $results.find('.result-needs-practice').show();
            }
            
            // Hide questions, show results
            this.container.find('.pattern-questions').hide();
            $results.fadeIn();
        },
        
        /**
         * Update progress indicator
         * 
         * @param {int} currentIndex Current question index
         */
        updateProgress: function(currentIndex) {
            const $progress = this.container.find('.quiz-progress');
            const percentage = Math.round(((currentIndex + 1) / this.totalQuestions) * 100);
            
            $progress.find('.progress-text').text(`Question ${currentIndex + 1} of ${this.totalQuestions}`);
            $progress.find('.progress-bar').css('width', percentage + '%');
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        TradePress_PatternQuiz.init();
    });
    
})(jQuery);
