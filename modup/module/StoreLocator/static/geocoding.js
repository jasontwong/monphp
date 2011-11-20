var google_map;
// {{{ function create_gmap(el_id)
function create_gmap(el_id)
{
    if (GBrowserIsCompatible())
    {
        google_map = new GMap2(document.getElementById(el_id));
        adjust_background();
        google_map.addControl(new GSmallMapControl());
        google_map.addControl(new GMapTypeControl());
        google_map.setCenter(new GLatLng(40, -100), 4);
    }
}

// }}}
