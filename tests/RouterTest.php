<?php
namespace Bricks\Cli\Routing;
require_once('Router.php');

/**
 * @author Artur Sh. Mamedbekov
 */
class RouterTest extends \PHPUnit_Framework_TestCase{
  /**
   * @var Router Роутер.
	 */
	private $router;

  /**
   * @var Call Представление CLI-запроса.
   */
  private $call;

	public function setUp(){
    global $argv;
    $this->router = new Router;

    $argv = ['/test.php', '-adelete', '--action="delete"', '-s'];
    $this->call = new Call('a:s', ['action:']);
  }

  /**
   * Метод для тестирования вызова обработчика запроса.
   */
  public function callTest(Call $call){
  }

  /**
   * Должен определять маршрут и вызывать соответствующий обработчик, передавая 
   * ему представление запроса.
   */
  public function testRun(){
    $testMock = $this->getMock(get_class($this));
    $testMock->expects($this->once())
      ->method('callTest')
      ->with($this->equalTo($this->call));

    $this->router->route([
      'a' => '/^delete$/',
      's' => '/^1$/',
      'n' => '/^$/',
    ], 'callTest', $testMock);
    $this->router->run($this->call);
  }

  /**
   * Должен выбрасывать исключение, если не удалось определить маршрут.
   */
  public function testRun_shouldThrowExceptionIfRouteNotFound(){
    $this->setExpectedException('Bricks\Cli\Routing\RoutingException');

    $testMock = $this->getMock(get_class($this));
    $testMock->expects($this->never())
      ->method('callTest');

    $this->router->route([
      'a' => '/^test$/',
    ], 'callTest', $testMock);
    $this->router->run($this->call);
  }
}
