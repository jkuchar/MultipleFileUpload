(function($){

    var uploadifyId = {!$uploadifyId|escapeJS};
	var queue = $('#' + uploadifyId + '-queue');
    var clearQueueButton = $('#' + uploadifyId + 'ClearQueue');
	var uploadify = $('#' + uploadifyId);
    var uploadedCount = 0;

	uploadify.uploadify({
		auto: false,
		buttonImage: {!=\Nette\Environment::expand("{$baseModulePath}/images/uploadify/uploadifyButton.png")|escapeJS},
		fileSizeLimit: {!$sizeLimit|escapeJS},
        formData: {
			token: {!$token|escapeJS},
			sender: 'MFU-Uploadify'
		},
		height: 22,
        method: 'post',
		multi: true,
        overrideEvents: ['onQueueComplete'],
		queueID: uploadifyId + '-queue',
		queueSizeLimit: {!$maxFiles|escapeJS},
        removeCompleted: true,
		swf: {!=\Nette\Environment::expand("{$baseModulePath}/swf/uploadify/uploadify.swf")|escapeJS},
		uploader: {!$backLink|escapeJS},
		width: 70,
        
        /**
         * Triggered when all files in the queue have been processed.
		onQueueComplete: function(queueData){
            if (queueData.uploadsErrored > 0) {
                showMessage('Niekoľko súborov sa nepodarilo nahrať (' + queueData.uploadsErrored + ')');
            } else {
                showMessage('Všetky súbory boli úspešne nahrané');
            }
            
            queue.fadeOut(500);
            clearQueueButton.fadeOut(500);
        },
        */
        
        onClearQueue: function(queueItemCount) {
            queue.fadeOut(500);
            clearQueueButton.fadeOut(500);
        },
        
        /**
         * Triggered for each file that successfully uploads.
         */
		onUploadSuccess: function(fileObj, data, response){
            uploadedCount++;
			$('#' + uploadifyId + 'Count').text('Počet nahraných súborov:' + uploadedCount);
		},
        
        /**
         * Triggered for each file that is selected from the browse files dialog and added to the queue.
         */
		onSelect: function(fileObj){
            
            if(fileObj.size > uploadify.uploadify('settings' , 'sizeLimit')) {
				uploadify.trigger({
					type: "sizeLimitExceeded",
					fileObj: fileObj,
				});
				uploadify.uploadify('cancel');
				return false;
			}
			if(fileObj.size == 0) {
				uploadify.trigger({
					type: "emptyFile",
					fileObj: fileObj,
				});
				uploadify.uploadify('cancel');
				return false;
			}
            
            queue.fadeIn(500);
            clearQueueButton.fadeIn(500);
//			$("#"+uploadifyId+"Count").text(uploadify.uploadify('settings', 'queueSize')+" vybraných souborů");
//			return false;
		}
	});

	uploadify.bind("sizeLimitExceeded",function(event){
		showMessage("soubor '" + event.fileObj.name + "' je moc velký! Bude přeskočen.");
	});

	uploadify.bind("emptyFile",function(event){
		showMessage("soubor '" + event.fileObj.name + "' je prázdný! Bude přeskočen.");
	});
    
    /**
     * Show info message to user.
     * @param string
     */
    function showMessage(msg)
    {
        $.Nette.showInfo(msg);
    }

	return true; // OK

})(jQuery);