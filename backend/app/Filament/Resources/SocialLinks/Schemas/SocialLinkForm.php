<?php

namespace App\Filament\Resources\SocialLinks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SocialLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('platform')
                    ->options([
                        'github' => 'GitHub',
                        'linkedin' => 'LinkedIn',
                        'tiktok' => 'TikTok',
                        'x' => 'X',
                        'instagram' => 'Instagram',
                        'youtube' => 'YouTube',
                    ])
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('One link per platform.'),
                TextInput::make('label')->required()->maxLength(60),
                TextInput::make('url')->url()->required()->maxLength(500)->columnSpanFull(),
                Toggle::make('is_visible')->label('Visible on the site')->default(true),
                TextInput::make('sort_order')->numeric()->default(0)->required(),
            ]);
    }
}
