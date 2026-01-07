<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Versions\v618;
use MultiVersion\Network\Proto\Adapter\BaseProtocolAdapter;
final class Protocol618Adapter extends BaseProtocolAdapter{
    private const PROTOCOL = 618;
    private const VERSION_STRING = "1.20.40";
    public function getProtocolVersion(): int{
        return self::PROTOCOL;
    }
    public function getVersionString(): string{
        return self::VERSION_STRING;
    }

}
