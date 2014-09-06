<?php
require_once(__DIR__ .'/../vendor/autoload.php');

$loop = React\EventLoop\Factory::create();

$makeConnection = function () {
    return mysqli_connect('localhost', 'root', '123', 'test');
};

$mysql = new Atrox\AsyncMysql($loop, $makeConnection);
$mysql->query('select * from unity_user_token')->then(
    function ($result) {
        var_dump($result->fetch_all(MYSQLI_ASSOC));
        $result->close();
    },
    function ($error)  {
        var_dump($error);
    }
);

$loop->run();