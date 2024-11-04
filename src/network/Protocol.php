<?php

namespace BlizApps\network;

enum Protocol: int
{
    case MC_VERSION = '1.21.40';
    case MC_PROTOCOL = 748;

    case UNKNOWN = 0x00;
    case LOGIN = 0x01;
    case PLAY_STATUS = 0x02;
    case SERVER_TO_CLIENT_HANDSHAKE = 0x03;
    case CLIENT_TO_SERVER_HANDSHAKE = 0x04;
    case RESOURCE_PACKS_INFO = 0x06;
    case RESOURCE_PACK_STACK = 0x07;
    case RESOURCE_PACK_RESPONSE = 0x08;
    case TEXT = 0x09;
    case MOVE_PLAYER = 0x13;
    case SET_TIME = 0x0a;
    case START_GAME = 0x0b;
    case ADD_PLAYER = 0x0c;

    case UPDATE_ATTRIBUTES = 0x1d;
    case TICK_SYNC = 0x17;

    case SET_ENTITY_METADATA = 0x27;
    case SET_SPAWN_POSITION = 0x2b;

    case ADVENTURE_SETTINGS = 0x37;
    case LEVEL_CHUNK = 0x3a;
    case SET_DIFFICULTY = 0x3c;
    case PLAYER_LIST = 0x3f;

    case REQUEST_CHUNK_RADIUS = 0x45;
    case CHUNK_RADIUS_UPDATED = 0x46;

    case CREATIVE_CONTENT = 0x91;
    case SET_LOCAL_PLAYER_AS_INITIALIZED = 0x71;
    case NETWORK_CHUNK_PUBLISHER_UPDATE = 0x79;
    case BIOME_DEFINITION_LIST = 0x7a;

    case PACKET_VIOLATION_WARNING = 0x9c;

    // TODO: implement all other identifiers...
    case CLIENT_CACHE_STATUS = 0x81;
    case NETWORK_SETTINGS = 0x8f;

    case PLAYER_FOG = 0xa0;
    case ITEM_COMPONENT = 0xa2;
}

