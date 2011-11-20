$(document).ready(function() {

$('form').submit(function() {
    $('input[type="password"][class="hash_sha1"]').each(function() {
        var el = $(this);
        el.val(hex_sha1(el.val()));
    });
    var f_hash = $('input[name="form[hashed][data]"]', this);
    if (f_hash.length)
    {
        f_hash.val(1);
    }
});

});
