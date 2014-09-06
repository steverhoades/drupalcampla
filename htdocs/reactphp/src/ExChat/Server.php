<?php
namespace ExChat;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Server implements MessageComponentInterface
{
	protected $clients;

	/**
	 * Initialize $clients to SplObjectStorage.  This will be used to
	 * store all connections.
	 */
	public function __construct()
	{
		$this->clients = new \SplObjectStorage();
	}

	/**
	 * Handle the new connection when it's received.
	 * 
	 * @param  ConnectionInterface $conn 
	 * @return void                    
	 */
	public function onOpen(ConnectionInterface $conn) 
	{
		$this->clients->attach($conn);		
	}

	/**
	 * A new message was received from a connection.  Dispatch
	 * that message to all other connected clients.
	 * 	
	 * @param  ConnectionInterface $from 
	 * @param  String              $msg  
	 * @return void                    
	 */
	public function onMessage(ConnectionInterface $from, $msg)
	{
		foreach($this->clients as $client) {
			if($from !== $client) {
				$client->send($msg);
			}
		}
	}

	/**
	 * The connection has closed, remove it from the clients list.
	 * @param  ConnectionInterface $conn 
	 * @return void                    
	 */
	public function onClose(ConnectionInterface $conn)
	{
		$this->clients->detach($conn);
	}	

	/**
	 * An error on the connection has occured, this is likely due to the connection
	 * going away.  Close the connection.
	 * @param  ConnectionInterface $conn 
	 * @param  Exception           $e    
	 * @return void                    
	 */
	public function onError(ConnectionInterface $conn, \Exception $e)
	{
		$conn->close();
	}
}