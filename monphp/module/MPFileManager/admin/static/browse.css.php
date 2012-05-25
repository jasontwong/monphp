html, body { height: 100%; }
#container { height: 100%; margin: 0px; width: 800px; }
#content { margin: 10px; width: 100%; }

#files { float: right; width: 650px; }

.items:after { content: "."; clear: both; display: block; height: 0; margin: 0; visibility: hidden; }

#files .list_view td { border-bottom: 1px dotted #000; padding: 3px; }
#files .list_view th { border-bottom: 1px dotted #000; padding: 3px; }
#files .list_view .checkbox { width: 25px; }
#files .list_view .mtime { width: 100px; }
#files .list_view .type { width: 35px; }
#files .list_view .size { width: 45px; }
#files .list_view .file { width: 445px; }

#files .grid_view .items { margin-top: 20px; }
#files .grid_view .item { background-color: #EEE; cursor: pointer; float: left; height: 115px; margin: 0px 5px 5px 0px; overflow: hidden; padding: 5px; text-align: center; width: 115px; }
#files .grid_view .item span { display: block; white-space: nowrap; }
#files .grid_view .image { height: 90px; margin: 0px auto; width: 90px; }
#files .grid_view img { max-height: 90px; max-width: 90px; }
#files .grid_view .selected { background-color: yellow; }

#files .window-tools { clear: both; margin-top: 15px; }

#tools { height: 50px; }

#nav { float: left; width: 130px; }
