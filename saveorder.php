<?php
	require "inc/lib.inc.php";
	require "inc/config.inc.php";

    //Обрабатываем данные из формы
    $name = filterDataPost($_POST['name']);
    $phone = filterDataPost($_POST['phone']);
    $email = filterDataPost($_POST['email']);
    $address = filterDataPost($_POST['address']);

    //Получаем номер заказа и время
    $ordr_id = $basket['orderid'];
    $dt = time();

    //Формируем строку для записи в файл
    $order = "$name|$email|$phone|$address|$ordr_id|$dt\n";

    //Записываем наш заказ в файл
    file_put_contents('admin/'.ORDERS_LOG,$order,FILE_APPEND);
    $res = saveOrder ($dt);
    if(!$res){
        echo "Произошла ошибка";
    }

?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Сохранение данных заказа</title>
</head>
<body>
	<p>Ваш заказ принят.</p>
	<p><a href="catalog.php">Вернуться в каталог товаров</a></p>
</body>
</html>