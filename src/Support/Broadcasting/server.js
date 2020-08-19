var request = require('request')
var server = require('http').Server()
var io = require('socket.io')(server)
var Redis = require('ioredis')
var redis = new Redis()

redis.subscribe('codicastudio-health-broadcasting-channel')

redis.on('message', function(channel, message) {
  message = JSON.parse(message)

  console.log('ponging...')

  if (message.event == 'codicastudio\\Health\\Events\\HealthPing') {
    request.get(
      message.data.callbackUrl + '?data=' + JSON.stringify(message.data),
    )
  }
})

server.listen(3000)
