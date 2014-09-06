<?php
/**
 * This is an async server example.  It uses the socket functions in PHP to create
 * a server which can handle multiple persistent connections.
 *
 * When any input is received it fires an http request and adds a callback to be executed
 * once the http request receives a response.  When the callback is executed the client connection
 * will be notified with the response.
 *
 * This example is using socket_select to muliplex.  The HTTP request will take 5 seconds per request
 * you can make multiple requests and should see a hello world HTML response.
 *
 * Connect via telnet:
 * telnet localhost 9050
 *
 * After you can enter any text as many times as you want from as many clients as you wish.
 */
define ('SOCK_NONBLOCK', 1073741824);

$port = 9050;

// create a streaming socket, of type TCP/IP
$sock = socket_create(AF_INET, SOCK_STREAM | SOCK_NONBLOCK, SOL_TCP);

// non-blocking
socket_set_blocking($sock, 0);

// set the option to reuse the port
socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);

// "bind" the socket to the address to "localhost", on port $port
// so this means that all connections on this port are now our resposibility to 
// send/recv data, disconnect, etc..
socket_bind($sock, 0, $port);

// start listen for connections
socket_listen($sock);


// create a list of all the clients that will be connected to us..
// add the listening socket to this list
$clients    = array((int) $sock => $sock);
$requests   = array();
$callbacks  = array();

while (true) {
    // create a copy, so $clients doesn't get modified by socket_select()
    $read   = array_merge($clients, $requests);
    $write  = $except = NULL;

    // get a list of all the clients that have data to be read from
    // if there are no clients with data, go to next iteration
    // marking null as the tv_sec argument will cause socket_select to return
    // immediately.  Since we want socket_select to block we are using null here.
    $ready = socket_select($read, $write, $except, null);
    if ($ready < 1) {
        continue;
    }
    
    // check if there is a client trying to connect
    if (in_array($sock, $read)) {
        // accept the client, and add him to the $clients array
        $newsock = socket_accept($sock);
        // non-blocking
        socket_set_blocking($newsock, 0);
        $clients[(int) $newsock] = $newsock;
        
        // send the client a welcome message
        socket_write($newsock, "Submit any input to start HTTP call.\n".
        "There are ".(count($clients) - 1)." client(s) connected to the server\n");
        
        socket_getpeername($newsock, $ip);
        echo "New client connected: {$ip}\n";
        
        // remove the listening socket from the clients-with-data array
        $key = array_search($sock, $read);
        unset($read[$key]);
    }
    
    // loop through all the clients that have data to read from
    foreach ($read as $read_sock) {
        $key = (int) $read_sock;

        // read until newline or 1024 bytes
        // socket_read while show errors when the client is disconnected, so silence the 
        // error messages
        $data = @socket_read($read_sock, 1024);

        // check if the client is disconnected
        if ($data === false || $data === '') {
            // remove client for $clients array
            unset($clients[$key]);
            unset($callbacks[$key]);
            unset($requests[$key]);
            echo "client disconnected.\n";
            // continue to the next client to read from, if any
            continue;
        }
        
        // trim off the trailing/beginning white spaces
        $data = trim(chop($data));
        
        if(!empty($callbacks[$key])) {
            call_user_func_array($callbacks[$key], array($data, $read_sock));
            unset($requests[(int) $read_sock]);
            continue;
        }

        // check if there is any data after trimming off the spaces
        if (!empty($data)) {
            // add callback for when new request is finished.
            $clientSocket = socket_create(AF_INET, SOCK_STREAM, 0);

            $requests[(int) $clientSocket] = $clientSocket;
            if(false === socket_connect($clientSocket, '127.0.0.1', 80)) {
                echo "Error Could not connect\n";
            }

            echo "Connection established\n";

            $message = "GET / HTTP/1.1\r\n";
            $message .= "HOST: local.dev\r\n\r\n";

            if(! socket_send($clientSocket, $message, strlen($message), 0)) {
                echo "Could not send data";
            }
            echo "Message send successfully\n";

            $callbacks[(int)$clientSocket] = function($data, $clientSocket) use ($read_sock) {
                $key = (int) $read_sock;
                socket_close($clientSocket);   
                socket_send($read_sock, $data, strlen($data), 0);
            };
        }
        
    } // end of reading foreach
}

// close the listening socket
socket_close($sock);
