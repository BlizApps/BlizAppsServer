<?php

namespace BlizApps\network;

class Protocol{
    const MC_VERSION = '1.21.40';
    const MC_PROTOCOL = 748;

    const UNKNOWN = 0x00;
    const LOGIN = 0x01;
    const PLAY_STATUS = 0x02;
    const SERVER_TO_CLIENT_HANDSHAKE = 0x03;
    const CLIENT_TO_SERVER_HANDSHAKE = 0x04;
    const RESOURCE_PACKS_INFO = 0x06;
    const RESOURCE_PACK_STACK = 0x07;
    const RESOURCE_PACK_RESPONSE = 0x08;
    const TEXT = 0x09;
    const MOVE_PLAYER = 0x13;
    const SET_TIME = 0x0a;
    const START_GAME = 0x0b;
    const ADD_PLAYER = 0x0c;

    const UPDATE_ATTRIBUTES = 0x1d;
    const TICK_SYNC = 0x17;

    const SET_ENTITY_METADATA = 0x27;
    const SET_SPAWN_POSITION = 0x2b;

    const ADVENTURE_SETTINGS = 0x37;
    const LEVEL_CHUNK = 0x3a;
    const SET_DIFFICULTY = 0x3c;
    const PLAYER_LIST = 0x3f;

    const REQUEST_CHUNK_RADIUS = 0x45;
    const CHUNK_RADIUS_UPDATED = 0x46;

    const CREATIVE_CONTENT = 0x91;
    const SET_LOCAL_PLAYER_AS_INITIALIZED = 0x71;
    const NETWORK_CHUNK_PUBLISHER_UPDATE = 0x79;
    const BIOME_DEFINITION_LIST = 0x7a;

    const PACKET_VIOLATION_WARNING = 0x9c;

    // TODO: implement all other identifiers...
    const CLIENT_CACHE_STATUS = 0x81;
    const NETWORK_SETTINGS = 0x8f;

    const PLAYER_FOG = 0xa0;
    const ITEM_COMPONENT = 0xa2;
}
