<?php
namespace Bricks\Cli\Routing;

/**
 * CLI-вызов.
 *
 * @author Artur Sh. Mamedbekov
 */
class Call{
  /**
   * @var string Команда вызова.
   */
  private $call;

  /**
   * @var array Опции вызова.
   */
  private $options;

  /**
   * @var string Входной поток.
   */
  private $input;

  /**
   * Пример создания экземпляра:
   *   $call = new Call('a::h', ['all::', 'hight']);
   *
   * @param string $options [optional] Шаблон разбора опций вызова.
   * Правила определяются в шаблоне в виде имен допустимых опций вызова вида:
   *   - a - допустипа опция 'a' без значения
   *   - a: - допустима опция 'a' с обязательным значением
   *   - a:: - допустима опция 'a' с необязательным значением
   * @param array $longoptions [optional] Шаблон разбора "длянных" опций.
   * Правила определяются в шаблоне аналогично параметру $options, но с помощью 
   * массива.
   */
  public function __construct($options = null, $longoptions = null){
    global $argv;
    $this->call = $argv[0];

    if(is_null($options)){
      $this->options = $argv;
      array_shift($this->options);
    }
    else{
      if(is_null($longoptions)){
        $longoptions = [];
      }
      $this->options = getopt($options, $longoptions);
    }
  }

  /**
   * Получение команды вызова.
   *
   * @return string Команда вызова.
   */
  public function name(){
    return $this->call;
  }

  /**
   * Получение значения опции.
   *
   * @param int|string $name [optional] Имя или индекс целевой опции.  Если 
   * параметр не задан, возвращаются все опции вызова.
   *
   * @return string|array|null Значение целевой опции или null - если опция не 
   * задана. Если параметр не передан, возвращаются все опции вызова.
   */
  public function opt($name = null){
    if(is_null($name)){
      return $this->options;
    }

    if(!isset($this->options[$name])){
      return false;
    }

    return $this->options[$name];
  }

  /**
   * Получение значения переменной окружения.
   *
   * @param string $name Имя целевой переменной окружения.
   *
   * @return string|null Значение целевой переменной окружения или null - если 
   * переменная не задана.
   * окружения.
   */
  public function env($name){
    return getenv($name);
  }

  /**
   * Получение содержимого входного потока.
   *
   * @return string Содержимое входного потока.
   */
  public function input(){
    if(is_null($this->input)){
      $this->input = file_get_contents('php://stdin');
    }
    return $this->input;
  }
}
