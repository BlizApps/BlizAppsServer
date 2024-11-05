<?php

namespace BlizApps\world\chunk;

use BlizApps\BinaryStream;
use BlizApps\WriteStream;
use BlizApps\network\minecraft\internal\WrapperPacket;
use BlizApps\network\minecraft\LevelChunk;

class Chunk {
    private const MAX_SLICES = 16;
    private const BIOMES_SIZE = 256;

    private $biomes;
    private $slices;

    private $x;
    private $z;

    public function __construct($x, $z) {
        $this->biomes = str_repeat("\x00", self::BIOMES_SIZE);
        $this->slices = new \SplFixedArray(self::MAX_SLICES);
        $this->x = $x;
        $this->z = $z;
    }

    public function getX() {
        return $this->x;
    }

    public function getZ() {
        return $this->z;
    }

    public function getRuntimeId($x, $y, $z) {
        return $this->getSlice($y >> 4)->getRuntimeId($x & 0x0f, $y & 0x0f, $z & 0x0f);
    }

    public function setRuntimeId($x, $y, $z, $id) {
        $this->getSlice($y >> 4)->setRuntimeId($x & 0x0f, $y & 0x0f, $z & 0x0f, $id);
    }

    public function getWrapper() {
        return new Promise(function ($resolve) {
            $topEmpty = $this->getTopEmpty();

            $stream = new WriteStream(str_repeat("\x00", ($topEmpty + 2) * 4096));
            for ($ci = 0; $ci < $topEmpty; $ci++) {
                $this->getSlice($ci)->streamEncode($stream);
            }

            $stream->write($this->biomes);
            $stream->writeByte(0); // border blocks size
            $stream->writeUnsignedVarInt(0); // extra data

            $levelChunk = new McpeLevelChunk();
            $levelChunk->cacheEnabled = false;
            $levelChunk->chunkX = $this->x;
            $levelChunk->chunkZ = $this->z;
            $levelChunk->subChunkCount = $topEmpty;
            $levelChunk->data = $stream->getBuffer();

            $wrapper = new WrapperPacket();
            $wrapper->addPacket($levelChunk);
            $resolve($wrapper);
        });
    }

    public function getTopEmpty() {
        $topEmpty = self::MAX_SLICES;
        for ($ci = 0; $ci <= self::MAX_SLICES; $ci++) {
            if (!isset($this->slices[$ci])) {
                $topEmpty = $ci;
            } else {
                break;
            }
        }
        return $topEmpty;
    }

    public function getSlice($y) {
        if ($y >= self::MAX_SLICES) {
            throw new \RangeException("Expected y to be up to " . self::MAX_SLICES . ", got $y");
        }
        if (!isset($this->slices[$y])) {
            $this->slices[$y] = new ChunkSlice();
        }
        return $this->slices[$y];
    }
}
```
