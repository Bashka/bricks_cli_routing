<?php
namespace Bricks\Cli\Routing;
require_once('Call.php');

/**
 * @author Artur Sh. Mamedbekov
 */
class CallTest extends \PHPUnit_Framework_TestCase{
  const CALLED_SCRIPT_NAME = '/test.php';

  /**
   * @var Call Вызов без опций.
   */
	private $simplyCall;

  /**
   * @var Call Вызов без именованных опций.
   */
	private $callWithNoNamedOptions;

  /**
   * @var Call Вызов с именованными опциями.
   */
	private $callWithNamedOptions;

	public function setUp(){
    global $argv;

    $argv = [self::CALLED_SCRIPT_NAME];
    $this->simplyCall = new Call;

    $argv = [self::CALLED_SCRIPT_NAME, '-adelete', '--action="delete"', '-s'];
    $this->callWithNoNamedOptions = new Call;

    $argv = [self::CALLED_SCRIPT_NAME, '-adelete', '--action="delete"', '-s'];
    $this->callWithNamedOptions = new Call('a:s', ['action:']);
  }

  /**
   * Должен получать имя вызванного скрипта.
   */
  public function testName(){
    $this->assertEquals(self::CALLED_SCRIPT_NAME, $this->simplyCall->name());
  }

  /**
   * Должен возвращать значение опции.
   */
  public function testOpt(){
    $this->assertEquals('-adelete', $this->callWithNoNamedOptions->opt(0));
    $this->assertEquals('--action="delete"', $this->callWithNoNamedOptions->opt(1));
    $this->assertEquals('delete', $this->callWithNamedOptions->opt('a'));
    $this->assertEquals('delete', $this->callWithNamedOptions->opt('action'));
  }

  /**
   * Должен возвращать все опции, переданные при вызове, если не уточнена 
   * целевая опция.
   */
  public function testOpt_shouldAllOptionsReturnIfNotSpecific(){
    $this->assertEquals(
      [
        '-adelete',
        '--action="delete"',
        '-s'
      ], $this->callWithNoNamedOptions->opt()
    );
    $this->assertEquals(
      [
        'a' => 'delete',
        'action' => 'delete',
        's' => true,
        'n' => false // Является частью Mock-функции getopt.
      ], $this->callWithNamedOptions->opt()
    );
  }

  /**
   * Должен возвращать null, если запрашивается отсутствующая опция.
   */
  public function testOpt_shouldNullReturnIfOptionNotFound(){
    $this->assertNull($this->simplyCall->opt('test'));
    $this->assertNull($this->callWithNoNamedOptions->opt('test'));
    $this->assertNull($this->callWithNamedOptions->opt('test'));
  }

  /**
   * Должен возвращать пустой массив, если опций при вызове не передано.
   */
  public function testOpt_shouldReturnEmptyArray(){
    $this->assertEquals([], $this->simplyCall->opt());
  }

  /**
   * Должен возвращать значение переменной окружения.
   */
  public function testEnv(){
    $this->assertEquals('test', $this->simplyCall->env('ENV_VARIABLE'));
  }

  /**
   * Должен возвращать null, если переменная окружения не определена.
   */
  public function testEnv_shouldNullReturnIfEnvironmentVariableNotFound(){
    $this->assertNull($this->simplyCall->env(''));
  }

  /**
   * Должен возвращать данные из потока ввода.
   */
  public function testInput(){
    $this->assertEquals('test', $this->simplyCall->input());
  }
}
