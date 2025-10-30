<?php

namespace App\Filament\Resources\DriverResource\Schemas;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DriverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Driver Information')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Driver Name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('phone')
                        ->label('Phone Number')
                        ->tel()
                        ->required()
                        ->maxLength(50)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('id_card_number')
                        ->label('ID Card Number')
                        ->required()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true)
                        ->columnSpan(2),

                    Forms\Components\FileUpload::make('photo')
                        ->label('Photo')
                        ->image()
                        ->imageEditor()
                        ->imageEditorAspectRatios([
                            '1:1',
                        ])
                        ->maxSize(2048)
                        ->directory('drivers')
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }
}
