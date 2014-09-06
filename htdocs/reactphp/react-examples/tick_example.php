<?php
/*
This example shows how nextTick and futureTick events are scheduled for
execution.

The expected output is:

    next-tick #1
    next-tick #2
    future-tick #1
    timer
    future-tick #2

Note that both nextTick and futureTick events are executed before timer and I/O
events on each tick.

nextTick events registered inside an existing nextTick handler are guaranteed
to be executed before timer and I/O handlers are processed, whereas futureTick
handlers are always deferred.
*/
require __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();


$makeConnection = function () {
    return mysqli_connect('localhost', 'root', '123', 'test');
};

$mysql = new Atrox\AsyncMysql($loop, $makeConnection);


echo get_class($loop) . PHP_EOL;
$loop->addTimer(
    0,
    function () {
        echo 'timer' . PHP_EOL;
    }
);
    $mysql->query('select * from country')->then(
        function ($result) {
            $data = json_encode($result->fetch_all(MYSQLI_ASSOC));
            $result->close();

            echo "MySQL Result Returned". PHP_EOL;
        },
        function ($error)  {
            var_dump($error);
        }
    );

$loop->nextTick(
    function ($loop) {
        echo 'next-tick #1' . PHP_EOL;

        $loop->nextTick(
            function () {
                echo 'next-tick #2' . PHP_EOL;
            }
        );
    }
);

$loop->futureTick(
    function ($loop) {
        echo 'future-tick #1' . PHP_EOL;

        $loop->futureTick(
            function () {
                echo 'future-tick #2' . PHP_EOL;
            }
        );
    }
);

$loop->run();