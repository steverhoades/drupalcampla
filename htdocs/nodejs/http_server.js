/**
 * This example is used for performance benchmarking against a
 * php equivalent located in ../reactphp/react-examples/http_mysql.php
 * 
 * To run type: 
 * node http_server.js
 *
 * Then open a connection to http://localhost:8080
 */
var http = require('http');
var fs = require('fs');
var mysql      = require('mysql');
var connection = mysql.createConnection({
  host     : 'localhost',
  user     : 'root',
  password : '123',
  database : 'test'
});

connection.connect(function(err) {
  // connected! (unless `err` is set)
});

var server = http.createServer();
server.on('request', function(req, res) {
	res.writeHead(200, {'Content-Type': 'text/plain'});
	var query = connection.query("select * from country", function(err, result) {
	  	res.end(JSON.stringify(result));
	});  	
});
server.listen(80)
