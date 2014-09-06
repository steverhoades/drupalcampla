<?php
/**
 * Example of making an asynchronous MySQL call on each http request.
 *
 * This server will return a JSON encoded string with a list of countries.
 *
 * IMPORTANT NOTE:
 * The Async mysql class provided in this example is for demonstration purposes only!
 * Use at your own risk. You have been warned =)
 *
 * Also, there are some known limitations with the example class that is provided.
 * For instance, it is not possible to get values such as insert_id on INSERT.
 */
require_once (__DIR__  . '/../vendor/autoload.php');

$loop           = React\EventLoop\Factory::create();
$socket         = new React\Socket\Server($loop);
$httpServer     = new React\Http\Server($socket);
$makeConnection = function () {
    return mysqli_connect('localhost', 'root', '123', 'test');
};

$mysql = new Atrox\AsyncMysql($loop, $makeConnection);

$httpServer->on('request', function($req, $res) use ($mysql) {
    $mysql->query('select * from country')->then(
        function ($result) use ($res, $req) {
            $data = json_encode($result->fetch_all(MYSQLI_ASSOC));
            $result->close();

            $res->writeHead(200, array('Content-Type' => 'application/json'));
            $res->end($data);
        },
        function ($error) use ($res)  {
            $res->writeHead(500, array());
        }
    );
});

$socket->listen(8080, '192.168.56.101');
$loop->run();
