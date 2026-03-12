<?php

namespace App\Enums;

enum Categories : string
{
    case MEALS = 'meals';
    case SNACKS = 'snacks';
    case BEVERAGES = 'beverages';
    case DESSERTS = 'desserts';
    case COMBOS = 'combos';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function description(): string
    {
        return match($this) {
            self::MEALS => 'Hearty main courses and dishes',
            self::SNACKS => 'Light bites and finger food',
            self::BEVERAGES => 'Refreshing drinks and hot beverages',
            self::DESSERTS => 'Sweet treats to end your meal',
            self::COMBOS => 'Value meal combinations',
        };
    }
}


