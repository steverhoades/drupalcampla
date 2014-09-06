var http 	= require('http');
var Promise = require('promise');

var urls = [
	'http://www.reddit.com',
	'http://www.hackernews.com',
	'http://www.google.com',
	'http://www.yahoo.com',
	'http://www.amazon.com',
];

function makeHttpRequest(host)
{
	var promise = new Promise(function(resolve, reject) { 
		var start = process.hrtime();
		var callback = function(response) {
		  var str = '';
		  //another chunk of data has been recieved, so append it to `str`
		  response.on('data', function (chunk) {
		    str += chunk;
		  });

		  //the whole response has been recieved, so we just print it out here
		  response.on('end', function () {		  	
		  	console.log(host +": "+ process.hrtime(start));
		    resolve(str);
		  });
		};
		http.get(host, callback);			
	});	

	return promise;
}

var start = process.hrtime();
var promises = [];
for(var i in urls) {
	var promise = makeHttpRequest(urls[i]);
	promises.push(promise);
}

Promise.all(promises).then(function (res) { 
	console.log("Total time: "+ process.hrtime(start));
});


var urls = ['http://www.amazon.com', ...];

for(var i in urls) {
	http.get(urls[i], function(response) {
		var str = '';
		response.on('data', function (chunk) {
			str += chunk;
		});

		response.on('end', function () {		  	
			// do something with data
		});
	});
}

Promise.all(promises).then(function (res) { 
	console.log("Total time: "+ process.hrtime(start));
});

