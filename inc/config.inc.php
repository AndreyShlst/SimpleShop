<?php
    #Объявление констант, переменных и установка соединения с сервером базы данных

    define("DB_HOST","localhost");//Хост
    define("DB_USER","root");//Пользователь
    define("DB_PASSWORD","");//Пароль
    define("DB_NAME","eshop");//Имя БД
    define("ORDERS_LOG","orders.log");//Файл для хранения имени файла с личными данными пользователей

    $baskets = array();//Массив для хранения корзины пользователя
    $count = 0;//Переменная для хранения количества товаров в корзине пользователя

    //Соединение с БД,отслеживаем возможные ошибки.
   $link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME)
                            or die(mysqli_connect_error());

    //Проверка корзины
    basketInit();