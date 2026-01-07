# MultiVersion

Advanced multi-protocol support plugin for PocketMine-MP 5 servers. Allow players with different Minecraft Bedrock Edition versions to play together seamlessly.

## Overview

MultiVersion is a comprehensive protocol translation plugin that enables your PocketMine-MP server to support multiple Minecraft Bedrock Edition client versions simultaneously. Players running anything from 1.19.2 to the latest version can connect and play together without compatibility issues.

## Key Features

- **Multi-Protocol Support**: Full support for Minecraft PE versions 1.19.2 through 1.20.70+
- **Intelligent Translation**: Automatic translation of blocks, items, entities, and chunks between protocol versions
- **High Performance**: Advanced caching system minimizes translation overhead
- **Feature Detection API**: Detect and handle version-specific features programmatically
- **Memory Efficient**: Smart memory management with WeakMap sessions and configurable cache limits
- **Developer Friendly**: Clean, well-documented API for plugin integration
- **Highly Configurable**: Extensive configuration options for fine-tuning behavior
- **Robust Error Handling**: Comprehensive logging and graceful fallback mechanisms

## Supported Versions

| Minecraft PE Version | Protocol ID | Support Status |
|----------------------|-------------|----------------|
| 1.19.2 - 1.19.40     | 527         | Full Support   |
| 1.19.50 - 1.19.80    | 560         | Full Support   |
| 1.20.0 - 1.20.30     | 594         | Full Support   |
| 1.20.40              | 618         | Full Support   |
| 1.20.50 - 1.20.60    | 621         | Full Support   |
| 1.20.70+             | 630         | Full Support   |
| Latest (PM5 Native)  | Current     | Native Support |

## Installation

### Requirements

- PocketMine-MP 5.0.0 or higher
- PHP 8.0 or higher
- Minimum 2GB RAM (4GB+ recommended for busy servers)

### Steps

1. Download the latest `.phar` file from the [Releases](https://github.com/Funaoo/MultiVersion/releases) page
2. Place the file in your server's `plugins/` folder
3. Restart your server
4. Configure the plugin in `plugin_data/MultiVersion/config.yml`

## Configuration

### Basic Configuration Example

```yaml
multiversion:
  enabled: true

  protocols:
    min_protocol: 527
    max_protocol: 999
    strict_mode: false

    latest:
      enabled: true

    527:
      enabled: true
      name: "1.19.2"

    594:
      enabled: true
      name: "1.20.0"

    621:
      enabled: true
      name: "1.20.50"
```

### Cache Configuration

```yaml
cache:
  chunks:
    enabled: true
    max_size: 2000
    max_memory_mb: 256
    ttl_seconds: 300

  palettes:
    enabled: true
    preload: true
```

### Fallback Behavior

```yaml
fallback:
  behavior: "visual_match"
  unknown_blocks: "stone"
  unknown_items: "air"
  unsupported_entities: "despawn"
```

### Performance Tuning

```yaml
performance:
  cleanup_interval: 60
  cache_optimization_interval: 300

debug:
  log_protocol_detection: false
  log_packet_translation: false
  log_cache_stats: false
```

## Commands

### Main Command: `/multiversion` (aliases: `/mv`, `/mversion`)

**Subcommands:**

- `/mv info` - Display current protocol information and statistics
- `/mv stats` - Show detailed cache statistics and performance metrics
- `/mv clear` - Clear all caches (chunks and packets)
- `/mv reload` - Reload plugin configuration

**Permission:** `multiversion.command` (default: op)

## Permissions

```yaml
multiversion.command:
  description: Allows usage of MultiVersion commands
  default: op

multiversion.admin:
  description: Full access to MultiVersion features
  default: op

multiversion.info:
  description: View protocol information
  default: true
```

## API for Developers

### Getting Player Protocol Information

```php
use MultiVersion\MultiVersionAPI;

$player = $event->getPlayer();

$protocol = MultiVersionAPI::getProtocol($player);

$version = MultiVersionAPI::getClientVersion($player);

if(MultiVersionAPI::isLegacy($player)){
    $player->sendMessage("You are using an older client version");
}
```

### Feature Detection

```php
use MultiVersion\MultiVersionAPI;

if(MultiVersionAPI::supportsFeature($player, 'trial_chambers')){
    $player->teleport($trialChambersLocation);
} else {
    $player->sendMessage("Your client doesn't support Trial Chambers");
}
```

### Supported Features

- `deep_dark` - Deep Dark biome (1.19.50+)
- `ancient_cities` - Ancient Cities structure (1.19.50+)
- `warden` - Warden mob (1.19.50+)
- `trial_chambers` - Trial Chambers structure (1.20.50+)
- `crafter` - Crafter block (1.20.50+)
- `breeze` - Breeze mob (1.20.50+)
- `cherry_grove` - Cherry Grove biome (1.20.0+)
- `sniffer` - Sniffer mob (1.20.0+)
- `camel` - Camel mob (1.20.0+)

### Event Handling

```php
use MultiVersion\Events\PlayerProtocolJoinEvent;
use MultiVersion\Events\PlayerProtocolQuitEvent;
use pocketmine\event\Listener;

class MyListener implements Listener {

    public function onProtocolJoin(PlayerProtocolJoinEvent $event): void {
        $player = $event->getPlayer();
        $protocol = $event->getProtocol();
        $version = $event->getVersionString();

        $player->sendMessage("Connected with protocol {$protocol} ({$version})");
    }

    public function onProtocolQuit(PlayerProtocolQuitEvent $event): void {
        $player = $event->getPlayer();
        $protocol = $event->getProtocol();
    }
}
```

### Protocol Management

```php
use MultiVersion\MultiVersionAPI;

$protocols = MultiVersionAPI::getSupportedProtocols();

if(MultiVersionAPI::isProtocolSupported(594)){
    echo "Protocol 594 is supported";
}

$min = MultiVersionAPI::getMinProtocol();
$max = MultiVersionAPI::getMaxProtocol();
```

## Architecture

### Project Structure

```
MultiVersion/
├── Cache/              Caching system for chunks and packets
├── Commands/           Plugin commands implementation
├── Core/               Core components (Registry, Router, Events)
├── Entity/             Entity system and factory
├── Events/             Custom event system
├── Handler/            Packet and game logic handlers
├── Item/               Item system and registry
├── Network/            Network layer and protocol management
│   └── Proto/          Protocol adapters and translation layer
├── Player/             Player session management
├── Translator/         Block, item, and entity translators
├── Utils/              Utility classes and helpers
└── World/              World and chunk management
```

### Packet Flow

```
Incoming: Client -> MVPacketInterceptor -> Protocol Adapter -> Translation -> Server
Outgoing: Server -> MVPacketInterceptor -> Protocol Adapter -> Translation -> Client
```

## Performance

### Memory Usage

- Base plugin overhead: 10-20 MB
- Per player overhead: 1-5 MB (varies with chunk cache usage)
- Chunk cache: Configurable (default max: 256 MB)
- Total typical usage: 50-300 MB depending on player count and settings

### CPU Impact

- Minimal impact when caching is effective
- Higher load during initial chunk generation and translation
- Scales linearly with number of legacy protocol clients
- Protocol translation overhead: <1ms per packet on modern hardware

### Optimization Tips

1. **Enable chunk caching** - Reduces repeated translation work significantly
2. **Preload palettes** - Eliminates startup delays for protocol translations
3. **Tune cache TTL** - Balance between memory usage and translation frequency
4. **Monitor with `/mv stats`** - Check cache hit rates and adjust settings accordingly
5. **Allocate sufficient RAM** - Ensure server has overhead for cache operations

## Troubleshooting

### Connection Issues

**Problem:** Players disconnected with "Unsupported protocol version"

**Solution:**
1. Verify the protocol is enabled in `config.yml`
2. Check protocol ID is within `min_protocol` and `max_protocol` range
3. Review server logs for specific error messages
4. Ensure MultiVersion is fully loaded before players connect

### Performance Issues

**Problem:** Server lag when legacy clients join

**Solution:**
1. Increase `cache.chunks.max_size` in config
2. Enable `cache.palettes.preload`
3. Increase `cache.chunks.ttl_seconds` to reduce retranslation
4. Allocate more RAM to the server
5. Monitor cache statistics with `/mv stats`

### Item/Block Display Issues

**Problem:** Items appear as "update" blocks or wrong types

**Solution:**
1. Verify `fallback.behavior` is configured correctly
2. Check palette files loaded successfully (enable debug logging)
3. Some newer blocks don't exist in older versions - expected behavior
4. Update palette files if available

### Translation Errors

**Problem:** Console flooded with translation errors

**Solution:**
1. Enable `debug.log_packet_translation` for detailed info
2. Verify PocketMine-MP version compatibility
3. Check for conflicting plugins
4. Report persistent issues with full logs

## Known Limitations

- Newer blocks/items may appear as placeholder "update" blocks in older clients
- Entity animations may differ between protocol versions
- Some particle effects are not backward compatible
- Complex NBT data in items may be simplified or lost during translation
- Protocol-specific features (Trial Chambers, Crafter, etc.) unavailable to older clients
- Some gameplay mechanics may behave differently across versions

## Contributing

Contributions are welcome! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Add PHPDoc comments to all public methods
- Include tests for new features when possible
- Update documentation for API changes
- Ensure backward compatibility when possible

## Changelog

### Version 2.0.0 (Current)

- Complete architecture refactoring with BaseProtocolAdapter
- Fixed PlayerActionPacket compatibility across versions
- Enhanced error handling and logging system
- Comprehensive API documentation
- Significant performance optimizations
- Memory leak fixes
- Improved cache management
- Extended developer API

### Version 1.0.0

- Initial public release
- Multi-protocol support for 1.19.2-1.20.70
- Basic caching system
- Protocol translation layer
- Event system implementation

## Support

- **Issues:** [GitHub Issues](https://github.com/Funaoo/MultiVersion/issues)
- **Discussions:** [GitHub Discussions](https://github.com/Funaoo/MultiVersion/discussions)
- **Wiki:** [Documentation Wiki](https://github.com/Funaoo/MultiVersion/wiki)

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Credits

- **Author:** [Funaoo](https://github.com/Funaoo)
- **Contributors:** See [Contributors](https://github.com/Funaoo/MultiVersion/graphs/contributors)
- **Special Thanks:** PocketMine-MP team for the excellent server software

## Disclaimer

This plugin is provided "as-is" without warranty of any kind, express or implied. Use at your own risk. Always backup your server data before installing or updating plugins.

---

**Made with dedication for the PocketMine-MP community**

Star this project if you find it useful!
