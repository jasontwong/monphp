<?php

$comment['module_name'] = array_rand(array_combine(Module::active_names(),Module::active_names()));
$comment['module_entry_id'] = rand(1,10);
$comment['approved_by'] = $_SESSION['user']['name'];
if (rand(0,100) % 2 === 0)
{
    $comment['entry'] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer scelerisque imperdiet ligula. Aliquam vitae diam. Fusce non mauris quis lorem pulvinar porta. Nunc mi mi, aliquet non, porttitor sit amet, molestie sit amet, diam. In tempor. Nullam egestas dui et erat. Nulla massa mi, volutpat in, laoreet in, tincidunt vel, augue. Etiam tempor rhoncus risus. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Integer elementum gravida lacus.";
    $comment['status'] = Comment::APPROVED;
    $comment['user_data'] = array('name' => 'John Doe', 'email' => 'john@kratedesign.com');
}
else
{
    $comment['entry'] = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam et odio auctor lorem dapibus elementum. Pellentesque ac sem. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Etiam mauris velit, sollicitudin cursus, mattis eget, suscipit non, nisi. Praesent lobortis ornare mi. Suspendisse potenti. Ut dui. Etiam sit amet nibh. Duis aliquet dignissim purus. Aenean arcu nunc, lacinia viverra, sollicitudin eu, venenatis ut, nulla. Curabitur justo massa, lobortis nec, aliquet eu, venenatis non, ligula. Suspendisse hendrerit, odio vel interdum rutrum, ante elit laoreet nisi, sed rutrum lectus sapien eu turpis. Quisque auctor. Suspendisse euismod quam quis libero. Nulla tincidunt. Curabitur libero arcu, malesuada quis, egestas sit amet, porta ac, est. Aenean at justo. Nunc tortor sapien, ultrices id, porta in, venenatis adipiscing, augue.";
    $comment['user_data'] = array('name' => 'Jane Doe', 'email' => 'jane@kratedesign.com');
}

$comment['create_date'] = time();

$check = Comment::add_comment($comment);
if (ake('success',$check) && $check['success'] === TRUE)
{
    header('Location: /admin/module/Comment/manage/');
    exit;
}
else
{
    echo 'Some kind of error! AHH!';
}

?>
