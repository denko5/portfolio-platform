<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Enums\LinkType;
use App\Enums\PublicationStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        $optional = 'Optional. Leave empty and this section is hidden on the website.';

        return $schema
            ->components([
                Tabs::make('Project')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Basics')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(160)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                                        if ($operation === 'create') {
                                            $set('slug', Str::slug((string) $state));
                                        }
                                    }),
                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(120)
                                    ->alphaDash()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Part of the project web address. Avoid changing it after publishing.'),
                                Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->required(),
                                Textarea::make('summary')
                                    ->required()
                                    ->maxLength(500)
                                    ->rows(3)
                                    ->columnSpanFull(),
                                TextInput::make('status_label')
                                    ->maxLength(40)
                                    ->helperText('For example "In development". Shown as a badge.'),
                                Select::make('technologies')
                                    ->relationship('technologies', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Tab::make('Media')
                            ->schema([
                                Repeater::make('images')
                                    ->relationship('images')
                                    ->orderColumn('sort_order')
                                    ->reorderable()
                                    ->collapsible()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add image')
                                    ->helperText('The first image is the cover. Every image needs alt text describing what it shows.')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('image')
                                            ->collection('image')
                                            ->image()
                                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                                            ->maxSize(5120)
                                            ->required(),
                                        TextInput::make('alt')
                                            ->label('Alt text')
                                            ->required()
                                            ->maxLength(300),
                                        TextInput::make('caption')
                                            ->maxLength(200),
                                        Toggle::make('is_placeholder')
                                            ->label('Placeholder graphic (not a real screenshot)')
                                            ->default(false),
                                    ])
                                    ->columnSpanFull(),
                                SpatieMediaLibraryFileUpload::make('downloads')
                                    ->collection('downloads')
                                    ->multiple()
                                    ->reorderable()
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->maxSize(10240)
                                    ->helperText('Public downloads such as a report (PDF). Do not upload confidential or personal data.')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Case study')
                            ->schema([
                                Textarea::make('problem')->helperText($optional)->rows(3)->columnSpanFull(),
                                Textarea::make('objectives')->helperText($optional)->rows(3)->columnSpanFull(),
                                Textarea::make('role')->label('My role')->helperText($optional)->rows(3)->columnSpanFull(),
                                MarkdownEditor::make('approach')->helperText($optional)->columnSpanFull(),
                                MarkdownEditor::make('challenges')->helperText($optional)->columnSpanFull(),
                                MarkdownEditor::make('outcomes')
                                    ->helperText($optional.' Only verified results.')
                                    ->columnSpanFull(),
                                MarkdownEditor::make('lessons')->helperText($optional)->columnSpanFull(),
                                MarkdownEditor::make('description_markdown')
                                    ->label('Full description')
                                    ->helperText($optional)
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Links and features')
                            ->schema([
                                Repeater::make('features')
                                    ->relationship('features')
                                    ->orderColumn('sort_order')
                                    ->reorderable()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add feature')
                                    ->schema([
                                        TextInput::make('body')->label('Feature')->required()->maxLength(300),
                                    ])
                                    ->columnSpanFull(),
                                Repeater::make('links')
                                    ->relationship('links')
                                    ->orderColumn('sort_order')
                                    ->reorderable()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add link')
                                    ->schema([
                                        TextInput::make('label')->required()->maxLength(80),
                                        TextInput::make('url')->url()->required()->maxLength(500),
                                        Select::make('type')->options(LinkType::class)->default('other')->required(),
                                        Toggle::make('is_visible')->default(true),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),
                                TextInput::make('link_note')
                                    ->maxLength(120)
                                    ->helperText('Shown when there are no links, for example "Private repository".'),
                                Textarea::make('public_note')
                                    ->rows(3)
                                    ->helperText('A short public note shown in the project details.')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Tab::make('SEO')
                            ->schema([
                                TextInput::make('seo_title')->maxLength(70)->helperText('Leave empty to use the project title.'),
                                Textarea::make('seo_description')->maxLength(170)->rows(3)->helperText('Leave empty to use the summary.'),
                            ]),

                        Tab::make('Publishing')
                            ->schema([
                                Select::make('publication_status')
                                    ->options(PublicationStatus::class)
                                    ->default(PublicationStatus::Draft->value)
                                    ->required()
                                    ->rules([
                                        fn (Get $get): \Closure => function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                                            if ($value === PublicationStatus::Published->value && filled($get('publish_block_reason'))) {
                                                $fail('Clear the publishing hold below before publishing this project.');
                                            }
                                        },
                                    ]),
                                DateTimePicker::make('published_at')
                                    ->helperText('Set automatically when first published.'),
                                Toggle::make('is_featured')->label('Featured on the homepage'),
                                TextInput::make('sort_order')->numeric()->default(0)->required(),
                                Textarea::make('publish_block_reason')
                                    ->label('Publishing hold (internal)')
                                    ->maxLength(300)
                                    ->rows(2)
                                    ->helperText('While this has text, the project cannot be published. Use it for items that need review, such as an outdated screenshot.')
                                    ->columnSpanFull(),
                            ])->columns(2),
                    ]),
            ]);
    }
}
