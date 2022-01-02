
<div class="columns chat">
    <div class="column">
        <h1 class="title"><i class="fa fa-comments"></i> chat room</h1>
        <div id="history">
            <p class="has-text-grey">loading chat history...</p>
        </div>
        <hr>
        <div class="media">
            <div class="media-content">
                <form id="frm">
                    <input id="input" class="input is-medium" placeholder="write your message here..." value="*** loading ***" autocomplete="off">
                </form>
            </div>
        </div>
    </div>
    <div class="column is-one-third">
        <div class="menu">
            <p class="menu-label">online users</p>
            <ul class="menu-list roster" id="roster">
            </ul>
            <!-- there will be room selection -->
            <!--
            <p class="menu-label">rooms</p>
            <ul class="menu-list">
                <li><span><span class="fa fa-globe"></span> everyone</span></li>
            </ul>
            -->
        </div>
    </div>
</div>

<style>
    .chat .media-left {
        width: 10em;
    }

    #history {
        height: 60vh;
        overflow: auto;
    }

    .chat .roster img, .chat .media-left img {
        width: 1em;
    }
</style>

<audio src="/st/ping.mp3" id="ping" preload="true" hidden></audio>
<script src="/st/js/bluescreen.js"></script>
<script src="/st/js/uboot.js"></script>
<script>
    // in case that somebody reads this: yes, this is mess
    // it will be better in later updates
    // but for now this have to work
    ub.whenReady(function () {
        // used DOM elements
        var input = ub.gebi('input');
        var history= ub.gebi('history');
        var roster= ub.gebi('roster');

        // variables and status
        var usersOnline = [];
        var icons = {};
        var isVisible = true;
        var prevDate = '';

        var userName = <?=json_encode($user['name']); ?>;
        var icon = <?=json_encode(kyselo_small_image($user['avatar_url'], 32, true)); ?>;
        var connection = new WebSocket(<?=json_encode(Flight::config('chat_websocket_url')); ?>);

        if ('Notification' in window) {
            Notification.requestPermission();
        }

        document.addEventListener("visibilitychange", function() {
            isVisible = document.visibilityState === 'visible';
        });

        connection.onopen = function() {
            input.value = '';
            connection.send(JSON.stringify({act: 'login', user: userName, icon: icon}));
        };

        connection.onerror = function(err) {
            //alert('chat crashed');
            input.value = '*** chat has disconnected ***';
            ub.addClass(input, 'is-danger');
            input.disabled = true;
            roster.innerText = ''; // nobody is online when disconnected
            // we want user to do F5
        };

        var _pad = function (n, c) {
            n = String(n)
            while (n.length < c) {
                n = '0' + n
            }
            return n
        };

        function formatDate(time) {
            return time.getDate() + '.' + (time.getMonth()+1) + '.' + time.getFullYear();
        }

        function formatTime(time) {
            return _pad(time.getHours(), 2) + ':' + _pad(time.getMinutes(), 2);
        }

        function addChatLine(line)
        {
            //console.log(line);
            var time = new Date(parseInt(line.date));
            if (formatDate(time)!=prevDate) {
                prevDate = formatDate(time);
                var _div = ub.makeElem('div', {'class':'media'});
                var _left = ub.makeElem('div', {'class': 'media-left'});
                var _right = ub.makeElem('div', {'class':'media-content'});

                _left.appendChild(ub.makeElem('i', {'class':'fa fa-calendar'}));
                _left.appendChild(ub.makeElem('span', {}, ' ' + prevDate));

                _div.appendChild(_left);
                _div.appendChild(_right);
                history.appendChild(_div);
            }

            var div = ub.makeElem('div', {'class': 'media'});

            var left = ub.makeElem('div', {'class': 'media-left'});
            left.appendChild(ub.makeElem('span', {'title': formatDate(time)}, formatTime(time) + ' '));
            left.appendChild(ub.makeElem('img', {'src': line.icon || '#'}));
            left.appendChild(ub.makeElem('strong', {}, ' ' +  line.user));

            var right = ub.makeElem('div', {'class':'media-content'});
            right.appendChild(ub.makeElem('p', {}, line.message))

            div.appendChild(left);
            div.appendChild(right);

            history.appendChild(div);
            history.scrollTop = history.scrollHeight;
        }

        function updateRoster()
        {
            var written = {};
            roster.innerHTML = '';
            for (var i = 0; i < usersOnline.length; i++) {

                var uname = usersOnline[i];
                if (uname==userName) {
                    continue; // we don't want to write ourself
                }
                if (written[uname]) {
                    continue; // this user is already in roster
                }
                written[uname] = true;
                var icon = icons[uname];
                var li = ub.makeElem('li');
                li.appendChild(ub.makeElem('img', {src: icon}));
                li.appendChild(ub.makeElem('span', {}, ' ' + uname));
                roster.appendChild(li);
            }
        }

        function spawnNotification(body, icon, title) {
            if ('Notification' in window && Notification.permission=='granted') {
                var options = {
                    body: body,
                    icon: icon
                };
                try {
                    new Notification(title, options);
                } catch (e) {
                    // old android do not like notification this way
                }
            }
        }


        connection.onmessage = function (message) {
            try {
                var recieved = JSON.parse(message.data);
            } catch (e) {
                return; // we don't understand, we ignore
            }

            var act = recieved.act || '?';
            console.log(recieved);

            if (act=='welcome' && recieved.history) {
                history.innerText = '';
                for (var i = 0; i < recieved.history.length; i++) {
                    addChatLine(recieved.history[i]);
                }
                usersOnline = recieved.present;
                icons = recieved.icons;
                updateRoster();
            }

            if (act=='message') {
                addChatLine(recieved);
                if (recieved.user != userName) {
                    ub.gebi('ping').play();
                    if (!isVisible) {
                        spawnNotification(recieved.message, recieved.icon, recieved.user);
                    }
                }
            }

            if (act=='online') {
                usersOnline.push(recieved.user);
                icons[recieved.user] = recieved.icon;
                updateRoster();
            }

            if (act=='offline') {
                usersOnline = usersOnline.filter(function (name) {
                    return name != recieved.user;
                });
                updateRoster();
            }
        };

        ub.on('frm', 'submit', function (ev) {
            ub.stop(ev);
            var text = input.value;
            input.value = '';
            connection.send(JSON.stringify({act: 'message', user: userName, message: text}))
        });
    });
</script>
