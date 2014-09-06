A simple benchmark was created to try and determine the performance
differences between PHP and NodeJS.  This test does the following:
* Creates an HTTP Server
* makes a mysql query on the same data set
* json encode the data
* returns data to the client


To benchmark nodejs run the following commends:
```

cd nodejs
sudo node http_server.js

```

To benchmark reactphp run the following commands:
```

cd reactphp/react-examples
sudo php mysql.php

```