const SOCKET_PORT = 9000;
const REDIS = {
    "host": "127.0.0.1",
    "port": "6379",
    "password": "redispassword",
    "family": 4
}
function handler(request, response) {
    response.writeHead(200);
    response.end('');
}
var app = require('http').createServer(handler);
var io = require('socket.io')(app);
var ioRedis = require('ioredis');
var redis = new ioRedis(REDIS);
app.listen(SOCKET_PORT, function() {
    console.log(new Date() + ' - Server is running on port ' + SOCKET_PORT + ' and listening Redis on port ' + REDIS.port + '!');
});
io.on('connection', function(socket) {
    console.log('A client connected '+socket.id);
});
redis.psubscribe('*', function(err, count) {
    console.log('Subscribed');
});
redis.on('pmessage', function(subscribed, channel, data) {
    let channel_name = channel;
    data = JSON.parse(data);
    console.log(new Date());
    console.log(data);
    io.emit(channel_name, data);
});