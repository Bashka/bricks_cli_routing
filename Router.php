<?php
namespace Bricks\Cli\Routing;
require_once(__DIR__ . '/Call.php');
require_once(__DIR__ . '/RoutingException.php');

/**
 * Объекты класса маршрутизируют Cli-вызовы к функция и методам обработчикам.
 *
 * Пример установки анонимной функции-обработчика:
 *   $router = new Router('a:', ['action:', 'id:']);
 *   $router->get(
 *     [
 *       'a' => '^delete$',
 *       'id' => '^[0-9]+$'
 *     ],
 *     function($call){
 *      echo 'Delete ' . $call->opt('id');
 *     }
 *   );
 *   $router->run();
 *
 * Пример установки метода класса:
 *   $router = new Router('a:', ['action:', 'data:']);
 *   $router->route(
 *     [
 *       'a' => '^create$',
 *       'data' => '^.+$'
 *     ],
 *     'createAction', 'EntityController'
 *   );
 *   $router->run();
 *
 * @author Artur Sh. Mamedbekov
 */
class Router{
  /**
   * @var array Карта маршрутизации.
   */
  private $map;

  /**
   * @var Call Объект вызова.
   */
  private $call;

  /**
   * todo
   * @param mixed $options [optional]
   * @param mixed $longoptions [optional]
   */
  public function __construct($options = null, $longoptions = null){
    $this->call = new Call($options, $longoptions);
    $this->map = [];
  }

  /**
   * Метод вызывает указанную функцию в заданном контексте.
   *
   * @param callable|string $callback Анонимная функция или имя целевой 
   * функции/метода.
   * @param string|object|null $context Контекст вызова в виде объекта или имени 
   * класса, который будет инстанциирован.
   */
  private function call($callback, $context){
    if(!is_null($context)){
      if(is_string($context)){
        $context = new $context;
      }
      $callback = [$context, $callback];
    }

    return call_user_func_array($callback, [$this->call]);
  }

  /**
   * Добавление маршрута для обработки CLI вызова.
   *
   * @param array $pattern Ассоциативный массив регулярных выражений, ключами 
   * которого выступают имена обязательных опций вызова, а значениями - 
   * требуемые значения этих опций. Если все опции перечисленные в этом массиве 
   * соответствуют регулярным выражениям, маршрут считается найденным.
   * @param callable|string $callback Обработчик запроса в виде анонимной 
   * функции или имени функции.
   * При вызове обработчику будут переданы следующие параметры:
   *   - Экземпляр класса Call
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
   * Выполняет поиск и вызов подходящего обработчика запроса.
   *
   * Пример отсутствия обработчика маршрута:
   *   $router = new Router('a:']);
   *   $router->get(['a' => '^help$'], 'helpAction', 'EntityController');
   *   try{
   *     $router->run();
   *   catch(RoutingException $e){
   *     echo 'Invalid action';
   *     return 0;
   *   }
   *
   * @throws RoutingException Выбрасывается в случае отсутствия подходящего 
   * обработчика запроса.
   *
   * @return mixed Данные, возвращаемые используемым обработчиком.
   */
  public function run(){
    foreach($this->map as $options){
      $success = true;
      foreach($options['pattern'] as $opt => $pattern){
        if(!preg_match('~' . $pattern . '~', $this->call->opt($opt))){
          $success = false;
          break;
        }
      }
      if($success){
        return $this->call($options['callback'], $options['context']);
      }
    }

    throw new RoutingException('Invalid call');
  }
}
