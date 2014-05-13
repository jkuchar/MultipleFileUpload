$(document).on('submit', 'form', function(e){
	var form = $(this);
	var multipleFileUploadFields = $(".MultipleFileUpload", this);
	var uploadersInQueue = multipleFileUploadFields.length;

	if(uploadersInQueue>0){
		multipleFileUploadFields.each(function(){
			var uploadify = $(".uploadify[id]",this),
			queueSize = 0;

			try{
				queueSize = uploadify.data('uploadify').queueData.queueLength;
			}catch(ex) {}

			if(queueSize>0){
				e.stopImmediatePropagation();
				e.preventDefault();
				uploadify.uploadify('upload', '*');
				uploadify.uploadify('settings', "onQueueComplete", function(queueData){
					uploadersInQueue--;
					if(uploadersInQueue===0){
						form.submit();
					}
				});
			} else {
				uploadersInQueue--;
			}
		});
	}
});