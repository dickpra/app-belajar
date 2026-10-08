<?php

namespace App\Filament\Admin\Resources\SignDictionaryResource\Pages;

use App\Filament\Admin\Resources\SignDictionaryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSignDictionary extends EditRecord
{
    protected static string $resource = SignDictionaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
