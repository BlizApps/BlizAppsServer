<?php

namespace BlizApps;

class Server{

  public function __construct(){
    //NOOP
  }

  public function getWorld() : void{
    return $this->getWorld2();
  }

  public function getWorld2() : void{
    echo "Comming Soon";
  }
}
