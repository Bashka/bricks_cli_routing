# Введение

Данный пакет служит для организации роутинга консольных вызовов PHP 
интерпретатора. Маршрутизация ориентирована на значения опций вызова, на пример:

```php
<?php
use Bricks\Cli\Routing\Router;
use Bricks\Cli\Routing\Call;

$router = new Router;
$router->route(['a' => '/^create$/'], function(Call $call){
    ...
});
$router->run(new Call('a:'));
```

Такому маршруту будет соответствовать вызов вида:

```bash
php script.php -acreate
```
