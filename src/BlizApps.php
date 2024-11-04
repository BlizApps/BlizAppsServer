<?php

namespace BlizApps;

use Dgram\RemoteInfo;
use Events\EventEmitter;
use TaskTimer\TaskTimer;
use BlizApps\BinaryStream;
use BlizApps\Logger;
use BlizApps\RakNet\NetEvents;
use BlizApps\RakNet\NetworkSession;
use BlizApps\RakNet\RakServer;
use BlizApps\Block\BlockManager;
use BlizApps\Config;
use BlizApps\Encryption;
use BlizApps\Entity\EntityPlayer;
use BlizApps\Network\Minecraft\Internal\DataPacket;
use BlizApps\Network\Minecraft\PlayerListEntry;
use BlizApps\Network\PacketRegistry;
use BlizApps\Network\PlayerConnection;
use BlizApps\Resources\ResourceManager;
use BlizApps\World\Generator\GeneratorManager;
use BlizApps\World\World;

class BlizApps extends EventEmitter
{
    private static $instance;
    private $server;
    private $config;
    private $connections = [];
    private $encryption = null;
    private $playerList = [];
    private $world;
    private $ticker;

    public function __construct(array $config)
    {
        parent::__construct();
        if (self::$instance) {
            self::getLogger()->fatal('Attempted to start the server twice on a single process.');
        }

        $this->config = $config;

        self::$instance = $this;
        $this->start();
    }

    private function start(): void
    {
        self::getLogger()->info('Bootstrapping BlizAPPS server for Minecraft bedrock edition...');

        // Allow unlimited listeners
        $this->setMaxListeners(0);

        $this->server = new RakServer(
            self::getConfig()['server']['port'] ?? 19132,
            self::getConfig()['server']['maxPlayers'] ?? 20,
            self::getLogger()
        );

        // Init packet registry
        PacketRegistry::init();
        ResourceManager::init();
        BlockManager::init();
        GeneratorManager::init();

        // TODO: cleanup this mess
        $this->world = new World(
            self::getConfig()['defaultWorld'] ?? 'world',
            GeneratorManager::getGenerator('flat')
        );

        // Init encryption
        if (self::getConfig()['encryption'] !== false) {
            $this->encryption = new Encryption();
            self::getLogger()->info('Encryption is enabled, preparing server keys...');
        }

        // Start the actual server
        $this->server->addListener(NetEvents::GAME_PACKET, [$this, 'handleRawNetwork']);
        $this->server->on(NetEvents::CLOSE_SESSION, function (RemoteInfo $rinfo) {
            // We already know that connection is close, so we're safe doing it
            if (isset($this->connections[$rinfo->getAddress()])) {
                $connection = $this->connections[$rinfo->getAddress()];
                assert($connection !== null, "Connection not found with key {$rinfo->getAddress()}");
                $connection->disconnect();
                unset($this->connections[$rinfo->getAddress()]);
            }
        });

        try {
            $this->server->start();
            self::getLogger()->info('Successfully loaded BlizAPPS software!');
        } catch (Exception $err) {
            self::getLogger()->fatal($err);
        }

        // Main server tick (every 1/20 seconds)
        $this->ticker = new TaskTimer(50);
        $this->ticker
            ->add([
                'callback' => function () {
                    // TODO: tick worlds that will tick entities and players
                    foreach ($this->getOnlinePlayers() as $onlinePlayer) {
                        $onlinePlayer->tick(0); // Just temp
                    }
                },
            ])
            ->start();
    }

    private function handleRawNetwork(BinaryStream $stream, NetworkSession $session): void
    {
        $rinfo = $session->getRemoteInfo();
        if (!isset($this->connections[$rinfo->getAddress()])) {
            $this->connections[$rinfo->getAddress()] = new PlayerConnection($session);
        }

        $conn = $this->connections[$rinfo->getAddress()];
        $conn->handleWrapper($stream);
    }

    public function broadcastDataPacket(DataPacket $packet, bool $immediate = false): void
    {
        foreach ($this->getOnlinePlayers() as $player) {
            $immediate
                ? $player->getConnection()->sendImmediateDataPacket($packet)
                : $player->getConnection()->sendQueuedDataPacket($packet);
        }
    }

    public function getOnlinePlayers(): array
    {
        return array_map(
            function ($conn) {
                return $conn->getPlayerInstance();
            },
            array_filter($this->connections, function ($conn) {
                return $conn->isInitialized();
            })
        );
    }

    public function getOnlinePlayer(string $username): ?EntityPlayer
    {
        foreach ($this->getOnlinePlayers() as $player) {
            if ($player->getUsername() === $username) {
                return $player;
            }
        }
        return null;
    }

    public function getPlayerList(): array
    {
        return $this->playerList;
    }

    public function shutdown(): void
    {
        // Close network provider
        $this->server->close();
        // Stop ticking connections
        $this->ticker->stop();
        // Remove all connections
        $this->connections = [];
        self::getLogger()->info('Successfully closed the server socket!');

        exit(0);
    }

    public static function getServer(): self
    {
        return self::$instance;
    }

    public static function getRakServer(): RakServer
    {
        return self::$instance->server;
    }

    public static function getConfig(): array
    {
        return self::$instance->config;
    }

    public static function getLogger(): Logger
    {
        return self::$instance->config['logger'];
    }

    public static function getEncryption(): ?Encryption
    {
        return self::$instance->encryption;
    }

    public static function getWorld(): World
    {
        return self::$instance->world;
    }
}

// Main execution
if ($argc < 2) {
    echo "Usage: php blizapps.php <config>\n";
    exit(1);
}

$configPath = realpath($argv[1]);
$config = null;

try {
    // TODO: Check config correctness
    $config = require $configPath;
} catch (Exception $err) {
    echo "Could not load the configuration file: " . $err->getMessage() . "\n";
    exit(1);
}

new BlizAPPS($config);
