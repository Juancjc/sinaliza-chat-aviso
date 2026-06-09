const queueWorkers = Math.max(Number.parseInt(process.env.QUEUE_WORKERS || '2', 10), 1);
const queueConnection = process.env.QUEUE_CONNECTION || 'redis';
const queueNames = process.env.QUEUE_NAMES || 'default';
const queueSleep = process.env.QUEUE_SLEEP || '1';
const queueTries = process.env.QUEUE_TRIES || '3';
const queueTimeout = process.env.QUEUE_TIMEOUT || '120';
const queueMaxTime = process.env.QUEUE_MAX_TIME || '3600';

module.exports = {
    apps: [
        {
            name: 'php-fpm',
            script: '/usr/local/sbin/php-fpm',
            args: '--nodaemonize',
            interpreter: 'none',
            autorestart: true,
            time: true,
        },
        {
            name: 'nginx',
            script: '/usr/sbin/nginx',
            args: '-g "daemon off;"',
            interpreter: 'none',
            autorestart: true,
            time: true,
        },
        {
            name: 'queue',
            script: '/usr/local/bin/php',
            args: `artisan queue:work ${queueConnection} --queue=${queueNames} --sleep=${queueSleep} --tries=${queueTries} --timeout=${queueTimeout} --max-time=${queueMaxTime}`,
            cwd: '/var/www/html',
            uid: 'www-data',
            gid: 'www-data',
            interpreter: 'none',
            instances: queueWorkers,
            exec_mode: 'fork',
            autorestart: true,
            kill_timeout: Number.parseInt(queueTimeout, 10) * 1000 + 10000,
            merge_logs: true,
            time: true,
        },
        {
            name: 'scheduler',
            script: '/usr/local/bin/php',
            args: 'artisan schedule:work',
            cwd: '/var/www/html',
            uid: 'www-data',
            gid: 'www-data',
            interpreter: 'none',
            autorestart: true,
            time: true,
        },
        {
            name: 'reverb',
            script: '/usr/local/bin/php',
            args: 'artisan reverb:start --host=0.0.0.0 --port=8080',
            cwd: '/var/www/html',
            uid: 'www-data',
            gid: 'www-data',
            interpreter: 'none',
            autorestart: true,
            kill_timeout: 10000,
            time: true,
        },
    ],
};
