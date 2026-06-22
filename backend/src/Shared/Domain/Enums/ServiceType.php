<?php

namespace MunicipalSaas\Shared\Domain\Enums;

enum ServiceType: string
{
    case Predial = 'PREDIAL';
    case Water = 'WATER';
    case TrafficFine = 'TRAFFIC_FINE';

    public function folioSegment(): string
    {
        return match ($this) {
            self::Predial => 'PRE',
            self::Water => 'AGU',
            self::TrafficFine => 'MUL',
        };
    }
}
