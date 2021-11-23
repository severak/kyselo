"use strict";
// CONFIG
process.title = 'kyselo-chat-server';// Port where we'll run the websocket server
var webSocketsServerPort = 1337;// websocket and http servers

// DEPENDENCIES
var webSocketServer = require('websocket').server;
var http = require('http');

// GLOBALS
// chat history
var history = [];
var historyMaxLength = 30;
// list of currently connected clients (users)
var clients = [];
var usersOnline = [];
var icons = {};

/**
 * HTTP server
 */
var server = http.createServer(function (request, response) {
    // Not important for us. We're writing WebSocket server,
    // not HTTP server
});
server.listen(webSocketsServerPort, function () {
    console.log((new Date()) + " Server is listening on port " + webSocketsServerPort);
});

/**
 * WebSocket server
 */
var wsServer = new webSocketServer({
    // WebSocket server is tied to a HTTP server. WebSocket
    // request is just an enhanced HTTP request. For more info
    // http://tools.ietf.org/html/rfc6455#page-6
    httpServer: server
});

// This callback function is called every time someone
// tries to connect to the WebSocket server
wsServer.on('request', function (request) {
    console.log((new Date()) + ' Connection from origin ' + request.origin + '.');

    // accept connection - you should check 'request.origin' to
    // make sure that client is connecting from your website
    // (http://en.wikipedia.org/wiki/Same_origin_policy)
    // TODO - zde kontrolovat zda je to z Kysela
    var connection = request.accept(null, request.origin);
    clients.push(connection);
    var userName = false;

    console.log((new Date()) + ' Connection accepted.');

    connection.on('message', function (message) {
        if (message.type === 'utf8') { // accept only text
            try {
                var recieved = JSON.parse(message.utf8Data);
            } catch (e) {
                return; // we don't understand, we ignore
            }

            var act = recieved.act || '?';
            console.log(recieved);

            if (act=='login' && recieved.user) {
                // somebody entered room
                userName = recieved.user;
                icons[userName] = recieved.icon;
                usersOnline.push(userName);

                // send them welcome message with chat history
                connection.send(JSON.stringify({
                    act: 'welcome',
                    history: history,
                    present: usersOnline,
                    icons: icons
                }));

                // and notify everyone about user going online
                for (var i = 0; i < clients.length; i++) {
                    clients[i].sendUTF(JSON.stringify({
                        act: 'online',
                        user: userName,
                        icon: recieved.icon
                    }));
                }
            }

            if (act=='message' && recieved.message) {
                // an message was received

                // adding date to message (clients can have different dates and times)
                recieved.date = Date.now().toString();
                recieved.icon = icons[recieved.user];

                // adding message to history
                if (history.length > historyMaxLength) {
                    history.shift();
                }
                history.push(recieved);

                // forwarding message to others
                for (var i = 0; i < clients.length; i++) {
                    clients[i].sendUTF(JSON.stringify(recieved));
                }
            }

            // TODO - implement direct messages somehow
        }
    });

    connection.on('close', function (connection) {
        if (userName !== false) {
            // TODO - ověřit si zda předělané tohle je OK
            console.log((new Date()) + " Peer " + connection + " disconnected.");      // remove user from the list of connected clients
            console.log(connection);
            console.log(userName + ' goes offline');

            var thisConnectionIndex = -1;
            for (var i = 0; i < clients.length; i++) {
                if (clients[i]==connection) {
                    thisConnectionIndex = i;
                }
            }
            clients.splice(thisConnectionIndex, 1);

            var userIndex = -1;
            for (var i = 0; i < usersOnline.length; i++) {
                if (usersOnline[i]==userName) {
                    userIndex = i;
                }
            }
            usersOnline.splice(userIndex, 1);

            console.log(usersOnline);

            // notify others user went offline
            for (var i = 0; i < clients.length; i++) {
                clients[i].sendUTF(JSON.stringify({
                    act: 'offline',
                    user: userName
                }));
            }

        }
    });

});
