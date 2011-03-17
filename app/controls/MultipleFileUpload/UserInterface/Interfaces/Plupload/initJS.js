(function($){
// API: http://www.plupload.com/plupload/docs/api/index.html

// Convert divs to queue widgets when the DOM is ready
	$("#"+{!$id|escapeJs}).pluploadQueue({
		// General settings
		runtimes : 'gears,html5,browserplus,flash,silverlight',
		url : {!$backLink|escapeJs},
		/*max_file_size : {!$sizeLimit},*/
		chunk_size : '5mb',
		//unique_names : true,

		headers: {
			"X-Uploader": "plupload",
			"token"     : {!$token|escapeJs}
		},

		// Resize images on clientside if we can
		/*resize : { width : 320, height : 240, quality : 90},*/

		// Specify what files to browse for
		/*filters : [
			{ title : "Image files", extensions : "jpg,gif,png"},
			{ title : "Zip files", extensions : "zip"}
		],*/

		// Flash settings
		flash_swf_url : {!$baseUri|escapeJs}+'swf/MultipleFileUpload/plupload.flash.swf',

		// Silverlight settings
		silverlight_xap_url : {!$baseUri|escapeJs}+'xap/MultipleFileUpload/plupload.silverlight.xap'
	});

	// Client side form validation
	/*$('form').submit(function(e) {
		var uploader = $('#uploader').pluploadQueue();

		// Validate number of uploaded files
		if (uploader.total.uploaded == 0) {
			// Files in queue upload them first
			if (uploader.files.length > 0) {
				// When all files are uploaded submit form
				uploader.bind('UploadProgress', function() {
					if (uploader.total.uploaded == uploader.files.length)
						$('form').submit();
				});

				uploader.start();
			} else
				alert('You must at least upload one file.');

			e.preventDefault();
		}
	});*/

	return true; // OK

})(jQuery);