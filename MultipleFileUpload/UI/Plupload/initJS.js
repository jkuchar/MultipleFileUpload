var fallbackController = this;
(function($){
	// API: http://www.plupload.com/plupload/docs/api/index.html

	// Convert divs to queue widgets when the DOM is ready
	$(function(){
		// TODO: auto fallback
		var uploader = $("#"+{$id|escapeJs|noescape}).pluploadQueue({
			// General settings
			runtimes : 'html5,flash,silverlight,html4',
			url : {$uploadLink|escapeJs|noescape},
			max_file_size : {$sizeLimit|noescape},
			chunk_size : '5mb',

			// Intentionally do not use headers, because not all interfaces allows you to send them.
			// insted using parameters in URL or POST

			// Flash settings
			flash_swf_url : {$interface->baseUrl|escapeJs|noescape}+'/js/Moxie.swf',

			// Silverlight settings
			silverlight_xap_url : {$interface->baseUrl|escapeJs|noescape}+'/js/Moxie.xap'
		});
		uploader = $(uploader).pluploadQueue();
		var refreshFn = function(){ // if plupload moves around page, good to recompute position of uploader
			uploader.refresh();
		};
		setInterval(refreshFn,1000);
		refreshFn();

		uploader.bind("Error",function(){
			fallbackController.fallback();
		})
	});

	return true; // OK

})(jQuery);