/* **********************
   Event Handlers
   These are my custom event handlers to make my
   web application behave the way I went when SWFUpload
   completes different tasks.  These aren't part of the SWFUpload
   package.  They are part of my application.  Without these none
   of the actions SWFUpload makes will show up in my application.
   ********************** */

jQuery.fn.bindAll = function(options) {
	var $this = this;
	jQuery.each(options, function(key, val){
		$this.livequery(key, val);
	});
	return this;
}

jQuery.fn.swfuInstance = function(options) {
        return $.swfupload.getInstance(this);
}

$(function(){

        var listeners = {
            preLoad: function(event) {
           
                    if (!$(this).swfuInstance().support.loading) {
                            alert("You need the Flash Player to use SWFUpload.");
                            return false;
                    } else if (!$(this).swfuInstance().support.imageResize) {
                            alert("You need Flash Player 10 to upload resized images.");
                            return false;
                    }
            },

            loadFailed: function(event) {
                    alert("Something went wrong while loading SWFUpload. If this were a real application we'd clean up and then give you an alternative");
            },

            fileQueued: function(event, file) {

                    try {
                            var progress = new FileProgress(file, $(this).swfuInstance().customSettings.progressTarget);
                            progress.setStatus("Pending...");
                            progress.toggleCancel(true, $(this).swfuInstance());
                    } catch (ex) {
                            $(this).swfuInstance().debug(ex);
                    }

            },

            fileQueueError: function(event, file, errorCode, message) {
                    try {
                            if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
                                    alert("You have attempted to queue too many files.\n" + (message === 0 ? "You have reached the upload limit." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
                                    return;
                            }

                            var progress = new FileProgress(file, $(this).swfuInstance().customSettings.progressTarget);
                            progress.setError();
                            progress.toggleCancel(false);

                            switch (errorCode) {
                            case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
                                    progress.setStatus("File is too big.");
                                    $(this).swfuInstance().debug("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                                    break;
                            case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
                                    progress.setStatus("Cannot upload Zero Byte files.");
                                    $(this).swfuInstance().debug("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                                    break;
                            case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
                                    progress.setStatus("Invalid File Type.");
                                    $(this).swfuInstance().debug("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                                    break;
                            default:
                                    if (file !== null) {
                                            progress.setStatus("Unhandled Error");
                                    }
                                    $(this).swfuInstance().debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                                    break;
                            }
                    } catch (ex) {
                    $(this).swfuInstance().debug(ex);
                }
            },

            fileDialogComplete: function(event, numFilesSelected, numFilesQueued) {
                    try {
                            if (numFilesSelected > 0) {
                                    var buttonId = $(this).swfuInstance().customSettings.cancelButtonId
                                    $('#'+buttonId).attr("disabled", false);
                            }

                    } catch (ex)  {
                        $(this).swfuInstance().debug(ex);
                    }
            },

            uploadStart: function(event, file) {
                    try {
                            /* I don't want to do any file validation or anything,  I'll just update the UI and
                            return true to indicate that the upload should start.
                            It's important to update the UI here because in Linux no uploadProgress events are called. The best
                            we can do is say we are uploading.
                             */
                            var progress = new FileProgress(file, $(this).swfuInstance().customSettings.progressTarget);
                            progress.setStatus("Uploading...");
                            progress.toggleCancel(true, $(this).swfuInstance());
                    }
                    catch (ex) {}

                    return true;
            },

            uploadProgress: function(event, file, bytesLoaded, bytesTotal) {
                    try {
                            var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

                            var progress = new FileProgress(file, $(this).swfuInstance().customSettings.progressTarget);
                            progress.setProgress(percent);
                            progress.setStatus("Uploading...");
                    } catch (ex) {
                            $(this).swfuInstance().debug(ex);
                    }
            },

            uploadSuccess: function(event, file, serverData) {
                    try {
                            var progress = new FileProgress(file, $(this).swfuInstance().customSettings.progressTarget);
                            progress.setComplete();
                            progress.setStatus("Complete.");
                            progress.toggleCancel(false);

                    } catch (ex) {
                            $(this).swfuInstance().debug(ex);
                    }
            },

            uploadError: function(event, file, errorCode, message) {
                    try {
                            var progress = new FileProgress(file, $(this).swfuInstance().customSettings.progressTarget);
                            progress.setError();
                            progress.toggleCancel(false);

                            switch (errorCode) {
                            case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
                                    progress.setStatus("Upload Error: " + message);
                                    $(this).swfuInstance().debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
                                    break;
                            case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
                                    progress.setStatus("Upload Failed.");
                                    $(this).swfuInstance().debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                                    break;
                            case SWFUpload.UPLOAD_ERROR.IO_ERROR:
                                    progress.setStatus("Server (IO) Error");
                                    $(this).swfuInstance().debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
                                    break;
                            case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
                                    progress.setStatus("Security Error");
                                    $(this).swfuInstance().debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
                                    break;
                            case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
                                    progress.setStatus("Upload limit exceeded.");
                                    $(this).swfuInstance().debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                                    break;
                            case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
                                    progress.setStatus("Failed Validation.  Upload skipped.");
                                    $(this).swfuInstance().debug("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                                    break;
                            case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
                                    // If there aren't any files left (they were all cancelled) disable the cancel button
                                    if ($(this).swfuInstance().getStats().files_queued === 0) {
                                            var buttonId = $(this).swfuInstance().customSettings.cancelButtonId
                                            $('#'+buttonId).attr("disabled", true);
                                    }
                                    progress.setStatus("Cancelled");
                                    progress.setCancelled();
                                    break;
                            case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
                                    progress.setStatus("Stopped");
                                    break;
                            default:
                                    progress.setStatus("Unhandled Error: " + errorCode);
                                    $(this).swfuInstance().debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                                    break;
                            }
                    } catch (ex) {
                    $(this).swfuInstance().debug(ex);
                }
            },

            uploadComplete: function(event, file) {
                    try {
                            if ($(this).swfuInstance().getStats().files_queued === 0) {
                                var buttonId = $(this).swfuInstance().customSettings.cancelButtonId;
                                $('#'+buttonId).attr("disabled", true);
                            }

                    } catch (ex) {
                            $(this).swfuInstance().debug(ex);
                    }
            },


            // This event comes from the Queue Plugin
            queueComplete: function(event, numFilesUploaded) {
                    $(".divStatus", this).text(numFilesUploaded + " file" + (numFilesUploaded === 1 ? "" : "s") + " uploaded.")
            }
        };

        $('.swfuflashupload').bindAll(listeners);
});