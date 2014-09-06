<?php
namespace ExGame;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class Server implements WampServerInterface {
    protected $playerData = array();

    public function onSubscribe(ConnectionInterface $conn, $topic) 
    {
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic) 
    {
    }

    public function onOpen(ConnectionInterface $conn) 
    {
    }

    public function onClose(ConnectionInterface $conn) 
    {
        $sessid = $conn->WAMP->sessionId;
        if(isset($this->playerData[$sessid])) {
            unset($this->playerData[$sessid]);
        }
    }

    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) 
    {
        switch($topic->getId()) {
            case "synchronize":
                $conn->callResult($id,$this->playerData);
                break;
            default:
                $conn->callError($id, $topic, 'You are not allowed to make calls')->close();        
        }
        // In this application if clients send data it's because the user hacked around in console
        
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) 
    {
        $sessid = $conn->WAMP->sessionId;
        switch($topic->getId()) {
            case "char_remove":
                if(isset($this->playerData[$sessid])) {
                    unset($this->playerData[$sessid]);
                }

                break;
            
            case "char_add":
            case "char_move":
                $this->playerData[$sessid] = $event;
                break;
            case "char_msg":
                if($event['msg'][0] == "/") {
                    $event['heroType'] = substr($event['msg'], 1);
                    $this->playerData[$sessid]['heroType'] = $event['heroType'];
                    $event['msg'] = "";
                    break;                    
                }
                break;

        }

        // In this application if clients send data it's because the user hacked around in console
        //$conn->close();
        $topic->broadcast($event, array($conn->WAMP->sessionId));
    }

    public function onError(ConnectionInterface $conn, \Exception $e) 
    {
    }
}