<?php

namespace BlizApps\world;

class CoordinateUtils {
    public static function fromBlockToChunk($v) {
        return $v >> 4;
    }

    public static function fromChunkToBlock($v) {
        return $v << 4;
    }

    public static function chunkHash($cx, $cz) {
        return $cx . ':' . $cz;
    }

    public static function getXZ($encodedPos) {
        return array_map('intval', explode(':', $encodedPos));
    }
}
