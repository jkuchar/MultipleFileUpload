(function($){

	var queue = $("#{!$uploadifyId}-queue");
	var uploadify = $("#{!$uploadifyId}");
	var box = uploadify.parents("div.withJS");

	// TODO: add this to stylesheet
	$("span.button",box)
	.addClass("ui-state-default ui-corner-all")
	.css("cursor","pointer")
	.css("padding","3px")
	.css("font-size","12px")
	.css("font-family","Constantia,Palatino,'palatino linotype',Georgia,'New York CE',utopia,serif");

	uploadify.uploadify({
		width: 70,
		height: 22,
		wmode: "transparent",
		auto: false,
		multi: true,
		queueID: {!$uploadifyId|escapeJS}+"-queue",
		buttonImg: {!=Environment::expand("%baseUri%images/MultipleFileUpload/uploadify/uploadifyButton.png")|escapeJS},
		uploader: {!=Environment::expand("%baseUri%swf/MultipleFileUpload/uploadify/uploadify.allglyphs.swf")|escapeJS},
		cancelImg: {!=Environment::expand("%baseUri%images/MultipleFileUpload/uploadify/cancel.png")|escapeJS},
		queueSizeLimit: {!$maxFiles|escapeJS},
		sizeLimit: {!$sizeLimit|escapeJS},
		script: {!$backLink|escapeJS},
		simUploadLimit: {!$simUploadFiles|escapeJS},
		scriptData: {
			token: {!$token|escapeJS},
			sender: "MFU-Uploadify"
		},
		onInit: function(){
			//box.parent().parent().find(".withoutJS").hide();
		},
		onComplete: function(event, ID, fileObj, response, data){
			jQuery("#" + {!$uploadifyId|escapeJS} + ID).hide();
			return false;
		},
		onSelect: function(event,queueID,fileObj){
			if(fileObj.size > uploadify.uploadifySettings("sizeLimit")) {
				uploadify.trigger({
					type: "sizeLimitExcessed",
					fileObj: fileObj,
					queueID: queueID
				});
				uploadify.uploadifyCancel(queueID);
				return false;
			}
			if(fileObj.size == 0) {
				uploadify.trigger({
					type: "emptyFile",
					fileObj: fileObj,
					queueID: queueID
				});
				uploadify.uploadifyCancel(queueID);
				return false;
			}
		},
		onSelectOnce: function(){
			var queue = $("#"+$(this).attr("id")+"-queue");
			$("#"+$(this).attr("id")+"ClearQueue").fadeIn(500);
			queue.slideDown(1000,function(){
				$("div.uploadifyQueueItem:first",queue).livequery(function(){
					$(this).animate({ marginTop:"0px"},200);
				});
			});
			$("#"+$(this).attr("id")+"Count").text(uploadify.uploadifySettings("queueSize")+" vybraných souborů");
			return false;
		}
	});

	uploadify.bind("sizeLimitExcessed",function(event){
		alert("soubor '"+event.fileObj.name+"' je moc velký! Bude přeskočen.");
	});

	uploadify.bind("emptyFile",function(event){
		alert("soubor '"+event.fileObj.name+"' je prázdný! Bude přeskočen.");
	});

	return true; // OK

})(jQuery);