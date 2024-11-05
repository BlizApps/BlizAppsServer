<?php

namespace BlizApps\world;

use BlizApps\chunk\Chunk;
use BlizApps\generator\WorldGenerator;

class World {
    private $name;
    private $generator;
    public $chunks = [];

    public function __construct($name, $generator) {
        $this->name = $name;
        $this->generator = $generator;
    }

    public function tick($timestamp) {}

    public function getChunk($cx, $cz, $create = true) {
        $hash = $cx . "_" . $cz;
        if (!array_key_exists($hash, $this->chunks)) {
            $chunk = $this->generator->generateChunk($cx, $cz);
            $this->chunks[$hash] = $chunk;
        }
        return $this->chunks[$hash];
    }

    public function getFolderName() {
        return $this->name;
    }
}
