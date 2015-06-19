<?php
	// подключение библиотек
	require "inc/lib.inc.php";
	require "inc/config.inc.php";

    $id = (int)filterDataPost($_GET['id']);
    if($id){
        deletetemFromBasket($id);
        header("Location: basket.php");
        exit;
    }
