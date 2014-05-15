/**
 * Form submitted callback.
 * @param {DOM Element} form
 * @param {Object} e
 * @param {jQuery Element} $submitBtn
 * @returns {Boolean}
 */
function formSubmitted(form, e, $submitBtn) {

	/**
	 * 	COMMON CODEBASE FOR ALL INTERFACES (matter of refactoring?)
	 */

	if (!$submitBtn && form['nette-submittedBy']) {
		$submitBtn = $(form['nette-submittedBy']);
	}

	// Do not upload files if
	//	a) submit button not recognized
	//	b) 'cancel' button clicked
	if (!$submitBtn || $submitBtn.attr('formnovalidate') === '') {
		return true;
	}

	var abortXhr = false;
	function preventSubmission() {
		e.preventDefault();
		e.stopImmediatePropagation();
		abortXhr = true;
	}

	/**
	 * 	COMMON CODEBASE FOR ALL INTERFACES END
	 */


	var multipleFileUploadFields = $('.MultipleFileUpload', form);
	var uploadersInQueue = multipleFileUploadFields.length;
	if (uploadersInQueue > 0) {
		multipleFileUploadFields.each(function() {
			var swfu = $('.swfuflashupload', this).swfuInstance(),
				queueSize = 0;

			try {
				queueSize = swfu.getStats().files_queued;
			} catch (ex) {
			}

			if (queueSize > 0) {
				preventSubmission();
				swfu.startUpload();
				$('.swfuflashupload', this).bind('queueComplete', function() {
					uploadersInQueue--;
					if (uploadersInQueue === 0) {
						// send form using submit button so it is included in request
						// so form['submitButtonName']->isSubmittedBy() can be used in Nette form processing
						$submitBtn.click();
					}
				});
			} else {
				uploadersInQueue--;
			}
		});
	}
	return !abortXhr;
}