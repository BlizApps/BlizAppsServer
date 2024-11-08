<?php

namespace BlizApps;

class Server{

  private $world = 'Only Test';

  public function __construct($world){
    $this->world = $world;
  }

  public function getWorld() : void{
    return $this->world;
  }

  public function getWorld2() : void{
    echo "Comming Soon";
  }
}
