var FileBrowserDialogue = {
    init : function () {
        // patch TinyMCEPopup.close
        tinyMCEPopup.close_original = tinyMCEPopup.close;
        tinyMCEPopup.close = function () {
            // remove blocking of opening another file browser window
            tinyMCE.selectedInstance.fileBrowserAlreadyOpen = false;

            // call original function to close the file browser window
            tinyMCEPopup.close_original();
        };
    },
    mySubmit : function () {
        var URL = document.browser.file.value;
        var win = tinyMCEPopup.getWindowArg("window");

        // insert information now
        win.document.getElementById(tinyMCEPopup.getWindowArg("input")).value = URL;

        // are we an image browser
        if (typeof(win.ImageDialog) != "undefined")
        {
            // we are, so update image dimensions and preview if necessary
            if (win.ImageDialog.getImageData) win.ImageDialog.getImageData();
            if (win.ImageDialog.showPreviewImage) win.ImageDialog.showPreviewImage(URL);
        }

        // close popup window
        tinyMCEPopup.close();
    }
}

tinyMCEPopup.onInit.add(FileBrowserDialogue.init, FileBrowserDialogue);
