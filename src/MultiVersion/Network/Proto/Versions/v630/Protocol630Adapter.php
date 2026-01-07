<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Versions\v630;
use MultiVersion\Network\Proto\Adapter\BaseProtocolAdapter;
final class Protocol630Adapter extends BaseProtocolAdapter{
    private const PROTOCOL = 630;
    private const VERSION_STRING = "1.20.60";
    public function getProtocolVersion(): int{
        return self::PROTOCOL;
    }
    public function getVersionString(): string{
        return self::VERSION_STRING;
    }

}
