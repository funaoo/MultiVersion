<?php
declare(strict_types=1);
namespace MultiVersion\Network\Proto\Versions\v594;
use MultiVersion\Network\Proto\Adapter\BaseProtocolAdapter;
final class Protocol594Adapter extends BaseProtocolAdapter{
    private const PROTOCOL = 594;
    private const VERSION_STRING = "1.20.10";
    public function getProtocolVersion(): int{
        return self::PROTOCOL;
    }
    public function getVersionString(): string{
        return self::VERSION_STRING;
    }

}
