# simple websocket chat for kyselo

based on [this tutorial](https://medium.com/@martin.sikora/node-js-websocket-simple-chat-tutorial-2def3a841b61) by Martin Sikora

## instalation

1) upload `chat-server.js` to server
2) install nodejs and websocket here (`npm install websocket`)
3) setup certificates if you Kyselo runs on HTTPS
4) start `chat-server.js`
5) set `chat_websocket_url` config value for Kyselo
6) start chatting

## protocol

- simple IRC-like groupchat (with just one group)
- DM later
- both clients and server are somewhat dumb and trust each other

protocol example:

```
CLIENT
{act: login, user: alice}

SERVER
{act: welcome, history: [...], present: [bob] }
{act: online, user: alice}

CLIENT
{act: message, user: alice, message: 'ahoj!'}

SERVER
{act: message, from: bob, message: 'taky ahoj!'}

SERVER
{act: offline, user: jack}
```

## todo

- better UI (showing dates etc)
- some glitches with duplicated users
- DMs
- some authentification (to prevent spoofing usernames etc)
