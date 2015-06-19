<?php
	// подключение библиотек
	require "secure/session.inc.php";
	require "../inc/lib.inc.php";
	require "../inc/config.inc.php";

    //Обработка полученных данных из веб-формы
    if($_SERVER['REQUEST_METHOD']=='POST'){//Проверка,была ли форма отправлена?

        //Фильтруем данные из формы
        $title = filterDataPost($_POST['title']);
        $author = filterDataPost($_POST['author']);
        $pubyear =(int)filterDataPost($_POST['pubyear']);
        $price = (int)filterDataPost($_POST['price']);

    }

    //Добавление товара в бд
    if(!addItemToCatalog($title, $author, $pubyear, $price)){
        echo 'Произошла ошибка при добавлении товара в каталог';
    }else{
        header("Location: add2cat.php");//Переход на страницу формы добавления
        exit;
    }