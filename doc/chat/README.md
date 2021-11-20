# simple websocket chat for kyselo

based on [this tutorial](https://medium.com/@martin.sikora/node-js-websocket-simple-chat-tutorial-2def3a841b61) by Martin Sikora

## instalation

1) upload `chat-server.js` to server
2) install nodejs and websocket here (`npm install websocket`)
3) start `chat-server.js`
4) set `chat_websocket_url` config value for Kyselo
5) start chatting

## protocol

```
CLIENT
{act: login, user: alice}

SERVER
{act: welcome, history: [...], present: [bob] }

CLIENT
{act: message, user: alice, message: 'ahoj!'}

SERVER
{act: message, from: bob, message: 'taky ahoj!'}

SERVER
{act: entered, who: jack}


```

## design

- jednoduchý IRC-like hromadný chat s jednou místností
- DMka
