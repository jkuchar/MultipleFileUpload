var fallbackController = this;
(function($){
// API: http://www.plupload.com/plupload/docs/api/index.html

// Convert divs to queue widgets when the DOM is ready
	$(function(){
		//setTimeout(function(){
			var uploader = $("#"+{!$id|escapeJs}).pluploadQueue({
				// General settings
				//runtimes : 'gears,html5,browserplus,silverlight,flash,html4',
				//runtimes : 'gears,html5,browserplus,silverlight,html4',
				runtimes : 'flash',
				url : {!$backLink|escapeJs},
				max_file_size : {!$sizeLimit},
				chunk_size : '5mb',
				/*rename: true,*/
				//unique_names : true,

				headers: {
					"X-Uploader": "plupload",
					"token"     : {!$token|escapeJs}
				},

				// Flash settings
				flash_swf_url : {!$baseUri|escapeJs}+'swf/MultipleFileUpload/plupload/plupload.flash.swf',

				// Silverlight settings
				silverlight_xap_url : {!$baseUri|escapeJs}+'xap/MultipleFileUpload/plupload/plupload.silverlight.xap'
			});
			uploader = $(uploader).pluploadQueue();
			uploader.bind("Error",function(){
				fallbackController.fallback();
			})
		//},1000);
	});

	return true; // OK

})(jQuery);