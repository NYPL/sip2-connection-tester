const spawn = require('child_process').spawnSync;
const fs = require('fs')

exports.handler = function(event, context, callback = function () {}) {
  const extensionsRoot = process.env.IS_LOCAL ?
    './node_modules/lib-for-node10-wrapped-php7-lambda-layer/lib' :
    `/opt/lib`

  const env = {
    BARCODE: event.barcode,
    DO_CHECKIN: event.doCheckin,
    LD_LIBRARY_PATH: extensionsRoot,
    SIP2_HOSTNAME: process.env.SIP2_HOSTNAME
  }

  let args = ['-n', '-d expose_php=Off', '-d memory_limit=512M', '-d opcache.file_cache=/tmp']

  const extensions = ['opcache.so']
    .map((filename) => `-d zend_extension=${extensionsRoot}/${filename}`)
  args = args.concat(extensions)

  args.push('sip2-connection-tester.php')

  var php = spawn(
      process.env.LAMBDA_TASK_ROOT + '/php-cgi',
      args,
      { env }
  );

  if (php.error) {
      callback(null, {
          statusCode: 500,
          body: 'Error executing PHP (ERROR: ' + (php.stderr ? php.stderr.toString() : '?') + ')'
      });
      return false;
  }

  if (php.stderr) {
      php.stderr.toString().split("\n").map(function (message) {
          if (message.trim().length) {
            console.log('stderr:');
            console.log(message);
          }
      });
  }

  if (!php.stdout.toString()) {
      callback(null, {
          statusCode: 500,
          body: 'No body returned in response (ERROR: ' + php.stderr.toString() + ')'
      });
      return false;
  }


  console.log(php.stdout.toString());

  callback(null);
}

if (!process.env.LAMBDA_TASK_ROOT) {
  const event = fs.readFileSync('./event.json');
  console.log('process.env.LAMBDA_TASK_ROOT: ', process.env.LAMBDA_TASK_ROOT)
  exports.handler(event, {}, (error, result) => {
    if (error) {
      console.log(`Error: ${error}`)
    }
    console.log(`Result: ${JSON.stringify(result, null, 2)}`)
  });
}
