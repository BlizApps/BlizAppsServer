<?php

namespace BlizApps\world\chunk;

use Assert\Assertion;

use Jukebox\binarystream\BinaryStream;
use Jukebox\binarystream\WriteStream;

use BlizApps\block\BlockManager;

class ChunkSlice {
    private const DATA_SIZE = 4096;

    private array $palette = [BlockManager::getRuntimeId('minecraft:air')];
    private array $blocks = array_fill(0, ChunkSlice::DATA_SIZE, 0);

    public function getRuntimeId(int $x, int $y, int $z): int {
        // ChunkSlice::checkBounds($x, $y, $z);
        $paletteIndex = $this->blocks[ChunkSlice::getIndex($x, $y, $z)];
        return $this->palette[$paletteIndex];
    }

    public function setRuntimeId(int $x, int $y, int $z, int $id): void {
        // ChunkSlice::checkBounds($x, $y, $z);
        if (!in_array($id, $this->palette)) {
            $this->palette[] = $id;
        }
        $this->blocks[ChunkSlice::getIndex($x, $y, $z)] = array_search($id, $this->palette);
    }

    public function streamEncode(WriteStream $stream): void {
        $stream->writeByte(8); // sub chunk version
        $stream->writeByte(1); // storages count

        $bitsPerBlock = ceil(log(count($this->palette), 2));
        switch ($bitsPerBlock) {
            case 0:
                $bitsPerBlock = 1;
                break;
            case 1:
            case 2:
            case 3:
            case 4:
            case 5:
            case 6:
                break;
            case 7:
            case 8:
                $bitsPerBlock = 8;
                break;
            default:
                $bitsPerBlock = 16;
        }

        $stream->writeByte(($bitsPerBlock << 1) | 1);

        $blocksPerWord = floor(32 / $bitsPerBlock);
        $wordsPerChunk = ceil(ChunkSlice::DATA_SIZE / $blocksPerWord);

        $position = 0;
        for ($w = 0; $w < $wordsPerChunk; $w++) {
            $word = 0;
            for ($block = 0; $block < $blocksPerWord; $block++) {
                $state = $this->blocks[$position];
                $word |= $state << ($bitsPerBlock * $block);
                $position++;
            }
            $stream->writeUnsignedIntLE($word);
        }

        $stream->writeVarInt(count($this->palette));
        foreach ($this->palette as $runtimeId) {
            $stream->writeVarInt($runtimeId);
        }
    }

    private static function checkBounds(int $x, int $y, int $z): void {
        Assertion::between($x, 0, 15, "x ($x) is not between 0 and 15");
        Assertion::between($y, 0, 15, "y ($y) is not between 0 and 15");
        Assertion::between($z, 0, 15, "z ($z) is not between 0 and 15");
    }

    private static function getIndex(int $x, int $y, int $z): int {
        return ($x << 8) | ($z << 4) | $y;
    }
}
