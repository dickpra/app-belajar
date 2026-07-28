<?php

namespace App\Filament\Admin\Resources\StudentAnswerResource\Pages;

use App\Filament\Admin\Resources\StudentAnswerResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageStudentAnswers extends ManageRecords
{
    protected static string $resource = StudentAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    // TAMBAHKAN BLOK INI UNTUK MEMUNCULKAN WIDGET STATISTIK
    protected function getHeaderWidgets(): array
    {
        return [
            StudentAnswerResource\Widgets\StudentAnswerStats::class,
        ];
    }
}
