<?php
	require "inc/lib.inc.php";
	require "inc/config.inc.php";

    //Получаем идентификатор товара, добавляемого в корзину
    $id = filterDataPost($_GET['id']);
    //Назначаем количество добавляемого товара
    $quantity = 1;

    add2Basket($id,$quantity);

    //Переадресовываем пользователя на страницу каталога товаров
    header("Location: catalog.php");
    exit;



