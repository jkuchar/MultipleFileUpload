$("form").livequery("submit",function(e){
	var form = $(this);
	var multipleFileUploadFields = $(".MultipleFileUpload", this);
	var uploadersInQueue = multipleFileUploadFields.length;

	if(uploadersInQueue>0){
		multipleFileUploadFields.each(function(){

                        var swfu = $('.swfuflashupload', this).swfuInstance();

                        var queueSize = 0;

			try{
                            queueSize = swfu.getStats().files_queued;
			}catch(ex) {
                        }
                        
                        if(queueSize>0){
				e.stopImmediatePropagation();
				e.preventDefault();
				swfu.startUpload();
				$('.swfuflashupload', this).bind('queueComplete',function(){
					uploadersInQueue--;

                                        if(uploadersInQueue==0){

                                                form.submit()
					}
				})
			} else uploadersInQueue--;
		})
	}
})