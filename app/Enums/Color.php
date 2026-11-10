<?php

namespace App\Enums;

enum Color: string
{
    case Black = 'black';
    case White = 'white';
    case Silver = 'silver';
    case Grey = 'grey';
    case Green = 'green';
    case Blue = 'blue';
    case Red = 'red';
    case Purple = 'purple';
    case Orange = 'orange';
    case Yellow = 'yellow';
    case Graphite = 'graphite';
    case SpaceGray = 'space_gray';
    case Midnight = 'midnight';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Black => 'Black',
            self::White => 'White',
            self::Silver => 'Silver',
            self::Grey => 'Grey',
            self::Green => 'Green',
            self::Blue => 'Blue',
            self::Red => 'Red',
            self::Purple => 'Purple',
            self::Orange => 'Orange',
            self::Yellow => 'Yellow',
            self::Graphite => 'Graphite',
            self::SpaceGray => 'Space Gray',
            self::Midnight => 'Midnight',
            self::Custom => 'Custom',
        };
    }
}
