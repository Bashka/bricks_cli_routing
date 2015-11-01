<?php
namespace Bricks\Cli\Routing;

function getopt($options, array $longopts = null){
  return ['a' => 'delete', 'action' => 'delete', 's' => true, 'n' => false];
}

function getenv($varname){
  if($varname == ''){
    return false;
  }
  return 'test';
}

function file_get_contents($file){
  return 'test';
}
