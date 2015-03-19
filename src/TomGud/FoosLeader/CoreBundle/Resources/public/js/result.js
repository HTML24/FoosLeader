(function ($) {
	fbd = window.fbd || {};
	fbd.result = fbd.result || {};
	fbd.result.disabledChoices = {};

	$(document).ready(function() {
		$(".player-choice").each(function() {
			var id = $(this).attr('id');
			 $(this).selectize({
			 	sortField: "id",
			 	hideSelected: true,
				onItemAdd: function(value, $item) {
					fbd.result.disabledChoices[id] = {
						value: value,
						item: $item.get(0).innerHTML
					}
					fbd.result.disableOption(id);
				},
				onClear: function(value, $item) {
					fbd.result.restoreOption(id);
				}
			});
			// var curSelectize = $(this).get(0).selectize;
			// fbd.result.disableOption(curSelectize.getValue());
		});
		$(document).on('click', '#create_result', fbd.result.validateNewResultForm);
		$(document).on('click', '.confirm-single-result', fbd.result.confirmResult);
		$(document).on('click', '.invalidate-single-result', fbd.result.invalidateResult);
	});

	/**
	 * Restore an option to the available players list
	 */
	fbd.result.restoreOption = function(controllingId) {
		var curDisabledChoice = fbd.result.disabledChoices[controllingId];
		$("select.player-choice").each(function() {
			var curSelectize = $(this).get(0).selectize;
			if (curDisabledChoice) {
				curSelectize.addOption({value: curDisabledChoice.value, text: curDisabledChoice.item });
			}
		});
		delete fbd.result.disabledChoices[controllingId];
	};

	/**
	 * Removing option from other selectizes if a player has been selected
	 *
	 */
	fbd.result.disableOption = function(controllingId) {
		$("select.player-choice").each(function() {
			var curSelectize = $(this).get(0).selectize,
				curDisabledChoice = fbd.result.disabledChoices[controllingId];
			if ($(this).attr('id') === controllingId) {
				// Have the selectize that should have this value
			} else {
				// Have some other selectize that we should disable a choice in
				if (curSelectize.getValue() === curDisabledChoice.value) {
					curSelectize.clear();
				}

				curSelectize.removeOption(curDisabledChoice.value);
			}
		});
	};

	/**
	 * Javascript validation for new result submission
	 */
	fbd.result.validateNewResultForm = function() {
		var $scoreOne = $("#score_team_1"),
			$scoreTwo = $("#score_team_2");
		if ($scoreOne.val() === '' || $scoreTwo.val() === '') {
			alert("Need to input scores for both teams.");
			return false;
		}

		var scoreOne = parseInt($scoreOne.val(), 10),
			scoreTwo = parseInt($scoreTwo.val(), 10);

		if (isNaN(scoreOne) || isNaN(scoreTwo)) {
			alert("Score must be numbers");
			return false;
		}

		if (scoreOne > 10 || scoreTwo > 10) {
			alert("Scores can not be greater than 10");
			return false;
		}

		if (scoreOne !== 10 && scoreTwo !== 10) {
			alert("At least one team should have won the game!");
			return false;
		}
	};

	fbd.result.confirmResult = function(e) {
		var action = $(this).data('action'),
			$this = $(this);
		$.ajax({
			url: Routing.generate('confirm_result', {id: $(this).data('result-id')}),
			type: 'post',
			success: function(data) {
				if (action === 'close-alert') {
					$this.parents('.alert').remove();
				} else if (action === 'close-result') {
					$this.parents('.single-result').remove();
                } else if (action === 'reload') {
                    location.reload();
				} else {
					alert('Successfully confirmed your result');
				}
			},
			error: function(data) {
				alert('Could not confirm your result at this time. Please try again later');
			}
		});
	};

    fbd.result.invalidateResult = function() {
        var $this = $(this),
            action = $this.data('action');
        $.ajax({
            url: Routing.generate('invalidate_result', {id: $this.data('result-id')}),
            type: 'post',
            success: function() {
                if (action === 'close-alert') {
                    $this.parents('.alert').remove();
                } else if (action === 'close-result') {
                    $this.parents('.single-result').remove();
                } else if (action === 'reload') {
                    location.reload();
                } else {
                    alert('Successfully invalidated your result');
                }
            },
            error: function() {
                alert('Could not invalidate your result at this time. Please try again later');
            }
        });

    };
})(jQuery);
