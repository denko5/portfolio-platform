<?php

namespace App\Filament\Resources\SiteSettings\Schemas;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class SiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Identity')
                            ->schema([
                                TextInput::make('owner_name')->label('Name')->required()->maxLength(120),
                                TextInput::make('headline')->required()->maxLength(160),
                                TextInput::make('location')->maxLength(120),
                                Textarea::make('intro_text')
                                    ->label('Homepage introduction')
                                    ->required()
                                    ->rows(3)
                                    ->helperText('A short, accurate introduction. Avoid claims you cannot support.')
                                    ->columnSpanFull(),
                                MarkdownEditor::make('bio_markdown')
                                    ->label('About page text')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Tab::make('Contact')
                            ->schema([
                                TextInput::make('email')
                                    ->label('Public contact email')
                                    ->email()
                                    ->required()
                                    ->maxLength(190),
                                TextInput::make('contact_notification_email')
                                    ->label('Enquiry notification email')
                                    ->email()
                                    ->required()
                                    ->maxLength(190)
                                    ->helperText('Where new contact form enquiries are sent.'),
                                TextInput::make('phone')->tel()->maxLength(40),
                                TextInput::make('whatsapp_number')
                                    ->label('WhatsApp number')
                                    ->regex('/^[0-9]{8,15}$/')
                                    ->maxLength(15)
                                    ->helperText('Digits only, with the country code and no plus sign or spaces.'),
                                TextInput::make('tiktok_handle')
                                    ->label('TikTok handle')
                                    ->maxLength(60)
                                    ->helperText('Without the @ sign.'),
                                TextInput::make('tiktok_followers_label')
                                    ->label('TikTok followers (as shown)')
                                    ->maxLength(20)
                                    ->helperText('Typed by hand, for example 46K+. Update it when the count changes.'),
                            ])->columns(2),

                        Tab::make('Overcend')
                            ->schema([
                                Toggle::make('overcend_visible')
                                    ->label('Show the Overcend section on the site')
                                    ->helperText('Overcend is an emerging venture. Keep it hidden until you are ready.')
                                    ->live(),
                                TextInput::make('overcend_heading')->maxLength(120),
                                Textarea::make('overcend_body')
                                    ->rows(4)
                                    ->requiredIf('overcend_visible', true)
                                    ->helperText('Do not imply clients, staff, established products or operations.')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('SEO')
                            ->schema([
                                TextInput::make('default_seo_title')->maxLength(70),
                                Textarea::make('default_seo_description')->maxLength(170)->rows(3),
                            ]),

                        Tab::make('Images and files')
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('portrait')
                                    ->collection('portrait')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(2048)
                                    ->helperText('A small, professional portrait. It is optimised when the site is built.'),
                                SpatieMediaLibraryFileUpload::make('cv')
                                    ->label('CV (PDF)')
                                    ->collection('cv')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(10240),
                                SpatieMediaLibraryFileUpload::make('default_og_image')
                                    ->label('Default social sharing image')
                                    ->collection('default-og-image')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(2048)
                                    ->helperText('Recommended size 1200 by 630 pixels.'),
                                SpatieMediaLibraryFileUpload::make('content_showcase')
                                    ->label('TikTok video covers')
                                    ->collection('content-showcase')
                                    ->multiple()
                                    ->reorderable()
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(2048)
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ]),
            ]);
    }
}
