# ⚡ Mmb Thunder

## What Is Thunder?

Thunder run a listener to the telegram and open some process in the background
(grouped by [Tagger](#Tagger)), and then pass the updates to child processes.

That means the application is ready to handle updates! Doesn't need to load
all packages, configs, service providers per each update.


## Installation

Easily install using:

```shell
composer require mmb/thunder:dev-main
```

## Commands

### Start Command

To start the thunder service, run this command:

```shell
php artisan thunder:start
```

### Stop Command

To stop the thunder service that running in the background, run this command:

```shell
php artisan thunder:stop
```

This command send a message to main thunder process and request to stop that.
It may take a long time, because the main process is blocked for listening
to Telegram.
You can send a message to Telegram to make it faster XD.

### Hot Reload

If you change a code, the thunder process will not refresh the process
(actually this is the point of thunder to speeding up).
But you can run the following command to kill and reload the processes.

```shell
php artisan thunder:hot-reload
```


## Customize

### Publish

Publish the config with following command:

```shell
php artisan vendor:publish --tag=thunder
```

### Puncher

Puncher is the way to open new process for a tag and update.

```php
'driver' => 'process',
```

> Currently, only "process" driver is available.

Release is time to release a tagged process when no updates are available for that.

```php
'release' => 180,
```


### Tagger

Tagging is grouping the updates to handle by a separate process.

```php
'tagger' => [
    'class' => Tagger\ChatTagger::class,
],
```

You can customize tagger:

```php
class UserTagger implements Tagger
{
    public function tag(Update $update) : string
    {
        return $update->getUser()?->id ?? 'global';
    }
}
```


### Sharing

Sharing is the way to main process and child process should connect to each
other and send messages.

```php
'driver' => 'file',
```

> Currently, only "file" driver is available.


### Hook

Hook is settings for listening for updates.

The "long" key is the timeout for "getUpdates" method in Telegram api.
This parameter is time to wait by telegram until new update passed.

```php
'long' => 60,
```
