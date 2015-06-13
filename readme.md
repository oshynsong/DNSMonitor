#Immediate Visualization of DNS lookup

##1.Description
Using the DNS lookup logging data for visualization, it is easy to
monitor the DNS lookup of a given network, analysis the load and
frequency path which can help to optimize the DNS server.

##2.Tech.
The given project just use one DNS lookup logging data for test.
+ Data process: using python script preprocess the logging data, and implements the aprior algorithm to find the frequent path. This can be done by the crontab on *nix server.
+ Server process: using php construct the dynamic website with the websocket tect. to communicate with the web browser immediately
+ Front process: using the D3.js to drawing the geo figure and dynamic path of a given DNS lookup
