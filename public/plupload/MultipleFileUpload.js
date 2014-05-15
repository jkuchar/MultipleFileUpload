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


	// automatically submit form after all files have been uploaded
	if (form.finito) {
		return true;
	}

	var multipleFileUploadFields = $('div.mfuplupload[id]', form);
	var uploadersInQueue = multipleFileUploadFields.length;
	if (uploadersInQueue > 0) {
		multipleFileUploadFields.each(function() {
			var uploader = jQuery(this).pluploadQueue(),
				queueSize = uploader.files.length,
				status = uploader.total;

			// prevent form submission while files are being uploaded
			if (uploader.state === plupload.STARTED) {
				preventSubmission();
				return; // continue;
			}

			// Upload already completed (triggered manually in Queue widget prior to this form submission)
			if (uploader.state === plupload.STOPPED && status.uploaded === queueSize && status.queued === 0) {
				uploadersInQueue--;
				return; // continue;
			}

			// start upload & submit form on completion
			var uploadCompleted = function() {
				if (uploadersInQueue === 0) {
					form.finito = true;
					// send form using submit button so it is included in request
					// so form['submitButtonName']->isSubmittedBy() can be used in Nette form processing
					$submitBtn.click();
				}
			};
			preventSubmission();
			uploader.bind('UploadComplete', function(uploader, files) {
				uploadersInQueue--;
				uploadCompleted();
			});
			uploader.start();
		});
	}
	return !abortXhr;
}