

$("form").livequery("submit",function(e){
	var form = $(this);
	var multipleFileUploadFields = $(".MultipleFileUpload", this);
	var uploadersInQueue = multipleFileUploadFields.length;

	if(uploadersInQueue>0){
		multipleFileUploadFields.each(function(){
			var uploadify = $(".uploadify[id]",this),
			queueSize = 0;
			
			try{
				queueSize = uploadify.uploadifySettings("queueSize");
			}catch(ex) {}
			
			if(queueSize>0){
				e.stopImmediatePropagation();
				e.preventDefault();
				uploadify.uploadifyUpload();
				uploadify.bind("uploadifyAllComplete",function(){
					uploadersInQueue--;
					if(uploadersInQueue===0){
						form.submit();
					}
				})
			}else uploadersInQueue--;
		})
	}
})


