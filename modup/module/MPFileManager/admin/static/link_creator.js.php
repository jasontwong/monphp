$(document).ready(function(){
    $('a.filemanager').click(function(e){
        e.preventDefault();
        window.open($(this).attr('href') + '?jquery=true', '_blank', 'width=800,height=300,toolbar=no,menubar=no,location=no');
    });
});

function test_sendback(data)
{
    alert(data);
}
