<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum FilePreviewType: string implements HasColor, HasIcon, HasLabel
{
    case Image = 'image';
    case Pdf = 'pdf';
    case Video = 'video';
    case Audio = 'audio';
    case Other = 'other';

    public static function fromMimeType(?string $mimeType): self
    {
        if (empty($mimeType)) {
            return self::Other;
        }

        if ($mimeType === 'application/pdf') {
            return self::Pdf;
        }

        if (str_starts_with($mimeType, 'image/')) {
            return self::Image;
        }

        if (str_starts_with($mimeType, 'video/')) {
            return self::Video;
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return self::Audio;
        }

        return self::Other;
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Image => 'Imagen',
            self::Pdf => 'Documento PDF',
            self::Video => 'Video',
            self::Audio => 'Audio',
            self::Other => 'Archivo',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Image => 'heroicon-s-photo',
            self::Pdf => 'heroicon-s-document-text',
            self::Video => 'heroicon-s-video-camera',
            self::Audio => 'heroicon-s-speaker-wave',
            self::Other => 'heroicon-s-paper-clip',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Image => 'info',
            self::Pdf => 'danger',
            self::Video => 'warning',
            self::Audio => 'success',
            self::Other => 'gray',
        };
    }
}
