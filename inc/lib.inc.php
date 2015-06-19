<?php
    //Функция фильтр данных из формы добавления
    function filterDataPost($data){
        global $link;
        return mysqli_real_escape_string($link,
                strip_tags(trim($data))
            );
    }

    //Функция добавления товара в каталог
    function addItemToCatalog($title, $author,$pubyear, $price){
        global $link;

        //Сформируем подготовленный SQL-запрос на вставку данных в таблицу catalog.
        $sql = 'INSERT INTO catalog (title, author, pubyear,price)
                  VALUES (?, ?, ?, ?)';

        if (!$stmt = mysqli_prepare($link, $sql)){
            return false;
        }
        mysqli_stmt_bind_param($stmt, "ssii", $title, $author,$pubyear, $price);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return true;

    }

    //Функция возвращает все содержимое каталога товаров (в виде ассоциативного массива)
    function selectAllItems(){
        global $link;

        //Сформируем SQL-запрос
        $sql = 'SELECT id, title, author, pubyear, price
                  FROM catalog';

        if(!$result = mysqli_query($link, $sql)){
            return false;
        }
        $items = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_free_result($result);
        return $items;
    }

    //Функция сохраняет корзину с товарами в куки
    function saveBasket(){
        global $basket;

        //Конвертируем массив в строку и используем base64_encode для сохранения целостности данных
        $basket = base64_encode(serialize($basket));
        //Отправляем куки
        setcookie('basket', $basket, 0x7FFFFFFF);
    }

    /*Функция создает либо загружает в переменную $basket корзину с товарами, либо создает новую
      корзину с идентификатором заказа*/
    function basketInit(){
        global $basket, $count;
        if(!isset($_COOKIE['basket'])){//Если куки нет
            $basket = array('orderid' => uniqid());//Уникальный id
            saveBasket();
        }else{
            $basket = unserialize(base64_decode($_COOKIE['basket']));
            $count = count($basket) - 1;//Вычитаем orderid,который также хранится в массиве корзины.(у нас может быть 2 товара но count()==3)
        }
    }

    // Функция сохранения товара в корзину (в качестве аргумента идентификатор товара и его количество)
    function add2Basket($id,$q){
        global $basket;
        $basket[$id] = $q;
        saveBasket();
    }

    //Функция возвращает всю пользовательскую корзину в виде ассоциативного массива
    function myBasket(){
        global $link,$basket;
        $goods = array_keys($basket);//Получаем ключи массива
        array_shift($goods);//извлекаем первый елемент(order_id)
        if(!$goods){
            return false;
        }
        $ids = implode(",", $goods);
        $sql = "SELECT id, author, title, pubyear, price
                  FROM catalog
                    WHERE id IN ($ids)";
        if(!$result = mysqli_query($link, $sql)){
            return false;
        }

        $items = result2Array($result);
        mysqli_free_result($result);
        return $items;
    }

    /*Функция принимает результат выполнения функции myBasketи возвращает
      ассоциативный массив товаров, дополненный их количеством */
    function result2Array($data){
        global $basket;
        $arr = array();
        while($row = mysqli_fetch_assoc($data)){
            $row['quantity'] = $basket[$row['id']];
            $arr[] = $row;
        }
        return $arr;
    }

    //Функция удаления товара из корзины
    function deletetemFromBasket($id){
        global $basket;
        unset($basket[$id]);//Удаляем елемент с указаным id
        saveBasket();
    }

    //Функция сохранения заказа в БД
    function saveOrder($dt){
        global $link,$basket;
        $goods = myBasket();//Получаем содержимое корзины
        $stmt = mysqli_stmt_init($link);
        $sql = "INSERT INTO orders(title,
                                   author,
                                   pubyear,
                                   price,
                                   quantity,
                                   orderid,
                                   datetime)
                  VALUES(?,?,?,?,?,?,?)";
        if(!mysqli_stmt_prepare($stmt,$sql)){
            return false;
        }
        foreach($goods as $item){
            mysqli_stmt_bind_param($stmt,'ssiiisi',
                                                    $item['title'],
                                                    $item['author'],
                                                    $item['pubyear'],
                                                    $item['price'],
                                                    $item['quantity'],
                                                    $basket['orderid'],
                                                    $dt);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
        setcookie('basket','',time()-3600);//Удаляем куку заказа
        return true;
    }

    //функция для выборки всех заказов
    function getOrders(){
        global $link;
        if(!is_file(ORDERS_LOG)) {
            return false;
        }
        // Получаем в виде массива персональные данные пользователей из файла
        $orders = file(ORDERS_LOG);
        // Массив, который будет возвращен функцией
        $allorders = array();
        foreach ($orders as $order) {
            list($name, $email, $phone, $address, $orderid, $date) = explode("|",$order);
            //Промежуточный массив для хранения информации о конкретном заказе
            $orderinfo = array();
            //Сохранение информацию о конкретном пользователе */
            $orderinfo["name"] = $name;
            $orderinfo["email"] = $email;
            $orderinfo["phone"] = $phone;
            $orderinfo["address"] = $address;
            $orderinfo["orderid"] = $orderid;
            $orderinfo["date"] = $date;
            /* SQL-запрос на выборку из таблицы orders всех товаров для конкретного
            покупателя */
            $sql = "SELECT title, author, pubyear, price, quantity
                    FROM orders
                    WHERE orderid = '$orderid' AND datetime = $date";
            // Получение результата выборки
            if(!$result = mysqli_query($link, $sql)) {
                return false;
            }
            $items = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_free_result($result);
            // Сохранение результата в промежуточном массиве
            $orderinfo["goods"] = $items;
            // Добавление промежуточного массива в возвращаемый массив
            $allorders[] = $orderinfo;
        }
        return $allorders;
    }