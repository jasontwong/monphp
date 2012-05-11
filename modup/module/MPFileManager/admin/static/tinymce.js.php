function MPFileManager_browser(field_name, url, type, win) 
{
    var cmsURL = 'http://<?php echo $_SERVER['HTTP_HOST']; ?>/admin/mod/MPFileManager/browse/tinymce/';    // script URL - use an absolute path!
    if (cmsURL.indexOf("?") < 0) {
        //add the type as the only query parameter
        cmsURL = cmsURL + "?type=" + type;
    }
    else {
        //add the type as an additional query parameter
        // (PHP session ID is now included if there is one at all)
        cmsURL = cmsURL + "&type=" + type;
    }
    
    /*
    var cmsURL = '/admin/mod/MPFileManager/browse/tinymce/';      // script URL
    var searchString = window.location.search;  // possible parameters
    if (searchString.length < 1) 
    {
        // add "?" to the URL to include parameters (in other words: create a search string because there wasn't one before)
        searchString = "?";
    }
    */

    tinyMCE.activeEditor.windowManager.open({
        file : cmsURL,
        // file : cmsURL + searchString + "&type=" + type,
        title : "File Browser",
        width : 850,
        height : 400,
        resizable : "yes",
        inline : "yes",
        close_previous : "no"
    }, {
        window : win,
        input : field_name,
    });

    return false;
}
