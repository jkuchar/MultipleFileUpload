$(document).on('submit', 'form', function(e){
	var form = $(this);
	if(form[0].finito) return;
	var multipleFileUploadFields = $("div.mfuplupload[id]", this);
	var uploadersInQueue = multipleFileUploadFields.length;
	if(uploadersInQueue>0){
		multipleFileUploadFields.each(function(){
			var uploader = jQuery(this).pluploadQueue();

			if(uploader.state == plupload.STARTED) {
				e.preventDefault();
				e.stopImmediatePropagation();
				return; // continue;
			}

			var queueSize = uploader.files.length;

			var status = uploader.total;
			if(uploader.state == plupload.STOPPED && status.uploaded == queueSize && status.queued == 0) {
				// Upload completed :-)
				uploadersInQueue--;
				return;
			}

			var fn = function(){
				if(uploadersInQueue===0){
					form.each(function(){
						this.finito = true;
					});
					form.submit();
				}
			};

			e.stopImmediatePropagation();
			e.preventDefault();
			uploader.bind("UploadComplete",function(uploader,files){
				uploadersInQueue--;
				fn();
			});
			uploader.start();
			fn();
		});
	}
});