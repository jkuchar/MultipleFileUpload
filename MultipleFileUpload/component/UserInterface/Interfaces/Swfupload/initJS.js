(function($){

    $("#{!$swfuId}").swfupload({
            flash_url : {!=\Nette\Environment::expand("{$interface->baseUrl}/swf/swfupload.swf")|escapeJS},
            flash9_url : {!=\Nette\Environment::expand("{$interface->baseUrl}/swf/swfupload_fp9.swf")|escapeJS},
            upload_url: {!$backLink|escapeJS},
            post_params: {
                token : {!$token|escapeJS},
                sender: "MFU-Swfupload"
            },

            file_size_limit : {!$sizeLimit|escapeJS},
            file_types : "*.*",
            file_types_description : "All Files",
            file_upload_limit : {!$maxFiles|escapeJS},

            custom_settings : {
                    progressTarget : "{!$swfuId}progress",
                    cancelButtonId : "{!$swfuId}btnCancel"
            },
            debug: false,

            // Button settings
            button_image_url: {!=\Nette\Environment::expand("{$interface->baseUrl}/imgs/XPButtonUploadText_89x88.png")|escapeJS},
            button_width: "89",
            button_height: "22",
            button_placeholder_id : "{!$swfuId}placeHolder",
    });

    return true;

})(jQuery);