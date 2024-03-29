<?php
require_once 'config.php';
try {
    $pdo = new pdo('mysql:host='.$config['mysql_host'].';dbname='.$config['mysql_datebase'], $config['mysql_user'], $config['mysql_password']);
} catch (PDOException $e){
    echo 'Ошибка при подключении к базе данных';
    exit();
}
$auth = false;
$gdz_link = '';
$gdz_text = '';
$gdz_img = '';
$dz = '';
if(isset($_POST['pass']) && $_POST['pass'] == $config['admin_password'])
{
    setcookie('admin', $config['admin_password'], time() + 3600 * 8760);
    $auth = true;
}
if(isset($_GET['logout']) && isset($_COOKIE['admin']))
{
    setcookie("admin","0",time()-1, "/");
    header('Location: /');
}
if(isset($_COOKIE['admin']) && $_COOKIE['admin'] == $config['admin_password'] || $auth == true)
{
    $r_time = time() + 3600 * 24;
    $t_date = date('20y-m-d', $r_time);
    $smtp = $pdo->prepare("SELECT * FROM `users`");
    $smtp->execute();
    $users_template = '';
    $name_user = 'Неизвестный';
    $open = 0;
    $click = 0;
    while ($row = $smtp->fetch(PDO::FETCH_ASSOC))
    {
        $open += $row['open'];
        $click += $row['click'];
        if($row['name'] != NULL) $name_user = $row['name'];
        $users_template .= '<li>Имя: '.$name_user.' | IP: '.$row['ip'].' | Переходы: '.$row['open'].' | Клики: '.$row['click'].'</li>';
    }
    if(isset($_POST['leasson']))
    {
        $id_leasson = $_POST['leasson'][0];
        $gdz_topic = $_POST['topic'];
        $dz = $_POST['dz'];
        if(isset($_POST['gdz_link']) && !empty($_POST['gdz_link'])) $gdz_link = $_POST['gdz_link'];
        if(isset($_POST['gdz_text']) && !empty($_POST['gdz_text'])) $gdz_text = nl2br($_POST['gdz_text']);;
        if(isset($_POST['gdz_img']) && isset($_FILES['gdz_img']))
        {
        $file_name = 'img/'.$_FILES['gdz_img']['name'];
        move_uploaded_file($_FILES['gdz_img']['tmp_name'], $file_name);
        $gdz_img = 'img/'.$_FILES['gdz_img']['name'];
        }
        if(isset($_POST['gdz_link']) && !empty($_POST['gdz_link'])) $gdz_link = $_POST['gdz_link'];
        $date =  explode('-',$_POST['date']);
        $mouth = $date[1];
        $day = $date[2];
        $timestamp = strtotime($date[2].'-'.$date[1].'-'.$date[0]);
            $smtp = $pdo->prepare("INSERT INTO `gdz` (id_leasson, gdz_text, gdz_topic, gdz_gdz, gdz_img, gdz_link, date, date_D, date_M) VALUES (:id_leasson, :gdz_text, :gdz_topic, :gdz_gdz, :gdz_img, :gdz_link, :gdz_date, :date_D, :date_M)");
            $smtp->execute([':id_leasson' => $id_leasson, ':gdz_text' => $gdz_text, ':gdz_topic' => $gdz_topic, ':gdz_gdz' => $dz, ':gdz_img' => $gdz_img, ':gdz_link' => $gdz_link, ':gdz_date' => $timestamp, ':date_D' => $day, ':date_M' => $mouth]);
        echo 'Готово!';
    }
exit(str_replace(['{%DATE%}', '{%OPEN%}', '{%CKILS%}', '{%USERS%}'],[$t_date, $open, $click, $users_template],file_get_contents('template/admin.tpl')));
}
elseif(!isset($_COOKIE['admin']))
{
    exit(file_get_contents('template/admin_auth.tpl'));
}