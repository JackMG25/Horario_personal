<?php

namespace App\Enums;

enum ActivityStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::InProgress => 'En curso',
            self::Completed => 'Completado',
            self::Skipped => 'Omitido',
        };
    }
}
