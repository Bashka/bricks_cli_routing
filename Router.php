<?php
namespace Bricks\Cli\Routing;
require_once(__DIR__ . '/Call.php');
require_once(__DIR__ . '/RoutingException.php');

/**
 * Маршрутизирует запросы, используемые при вызове PHP интерпретатора из 
 * командной строки.
 *
 * Экземпляры данного класса позволяют описать маршруты обработки запросов и 
 * вызвать требуемые функции в зависимости от опций вызова.
 * 
 * Пример установки анонимной функции-обработчика:
 *     $router = new Router;
 *     $router->route(
 *       [
 *         'a' => '/^delete$/', // Если опция '-a' равна 'delete'
 *         'id' => '/^[0-9]+$/' // и опция '--id' содержит целое число
 *       ],
 *       function($call){
 *        echo 'Delete ' . $call->opt('id');
 *       }
 *     );
 *     $router->run(new Call('a:', ['action:', 'id:']));
 *
 * Пример установки метода класса:
 *   $router = new Router;
 *   $router->route(
 *     [
 *       'a' => '/^create$/',
 *       'data' => '/^.+$/'
 *     ],
 *     'createAction',    // Вызов метода 'createAction'
 *     'EntityController' // объекта класса 'EntityController'
 *   );
 *   $router->run(new Call('a:', ['action:', 'data:']));
 *
 * @author Artur Sh. Mamedbekov
 */
class Router{
  /**
   * @var array Карта маршрутизации в виде массива объектов следующей структуры:
   *     [
   *       pattern => шаблонОпций,
   *       callback => обработчик,
   *       context => контекстОбработчика
   *     ]
   */
  private $map = [];

  /**
   * Вызывает указанную функцию в заданном контексте.
   *
   * @param Call $call Объект запроса.
   * @param callable|string $callback Анонимная функция или имя целевой 
   * функции/метода.
   * @param string|object|null $context Контекст вызова в виде объекта или имени 
   * класса, который будет инстанциирован.
   */
  private function call(Call $call, $callback, $context){
    if(!is_null($context)){
      if(is_string($context)){
        $context = new $context;
      }
      $callback = [$context, $callback];
    }

    return call_user_func_array($callback, [$call]);
  }

  /**
   * Регистрирует маршрут.
   *
   * Пример маршрутизации запроса справки:
   *     $router = new Router;
   *     $router->route(
   *       ['a' => '/^help$/'], // Для вызова с опцией '-a' равной 'help'
   *       'helpAction',      // обработка методом 'helpAction'
   *       'EntityController' // объекта класса 'EntityController'
   *     );
   *     $router->run(new Call('a:'));
   *
   * @param array $pattern Ассоциативный массив выражений, ключами которого 
   * выступают имена опций вызова, а значениями - требуемые значения этих опций 
   * в виде регулярного выражения. Если все опции перечисленные в этом массиве 
   * соответствуют регулярным выражениям, маршрут считается найденным.
   * @param callable|string $callback Обработчик запроса в виде анонимной 
   * функции или имени функции.
   * При вызове обработчику будет передан экземпляр класса Call, представляющий 
   * запрос.
   * @param object|string $context [optional] Контекст вызова обработчика в виде 
   * объекта или имени класса, который будет инстанциирован.
   */
  public function route(array $pattern, $callback, $context = null){
    array_push($this->map, [
      'pattern' => $pattern,
      'callback' => $callback,
      'context' => $context
    ]);
  }

  /**
   * Выполняет поиск маршрута и вызов обработчика в случае успеха.
   *
   * Маршруты обрабатываются в порядке их регистрации, при этом обработка 
   * останавливается при нахождении первого подходящего маршрута.
   * Пример обработки ошибки маршрутизации:
   *     $router = new Router;
   *     $router->route(['a' => '/^help$/'], 'helpAction', 'EntityController');
   *     try{
   *       $router->run(new Call('a:'));
   *     catch(RoutingException $e){
   *       echo 'Invalid action';
   *       return 1;
   *     }
   *
   * @param Call $call Объект запроса.
   *
   * @throws RoutingException Выбрасывается в случае ошибки маршрутизации 
   * (отсутствия подходящего обработчика запроса).
   *
   * @return mixed Данные, возвращаемые вызванным обработчиком.
   */
  public function run(Call $call){
    foreach($this->map as $options){
      $success = true;
      foreach($options['pattern'] as $opt => $pattern){
        if(!preg_match($pattern, $call->opt($opt))){
          $success = false;
          break;
        }
      }
      if($success){
        return $this->call($call, $options['callback'], $options['context']);
      }
    }

    throw new RoutingException('Invalid call');
  }
}
