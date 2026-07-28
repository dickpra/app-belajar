<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ModuleResource\Pages;
use App\Filament\Admin\Resources\ModuleResource\RelationManagers;
use App\Models\Module;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Checkbox;


class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Manajemen Modul';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // ==========================================
                // LEVEL 0: INFORMASI MODUL UTAMA
                // ==========================================
                Forms\Components\Section::make('Informasi Modul')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->label('Judul Modul (Misal: Modul Minggu ke-8)'),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Singkat (Opsional)'),

                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->directory('module-covers')
                            ->label('Gambar Sampul Modul (Biar Menarik!)'),
                        
                        // TAMBAHKAN KOLOM PIN DI SINI
                        Forms\Components\TextInput::make('access_pin')
                            ->label('PIN Akses Modul (Opsional)')
                            ->placeholder('Biarkan kosong jika tidak pakai PIN')
                            ->maxLength(6)
                            ->numeric(), // Agar hanya bisa diisi angka
                            
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Modul Aktif?'),
                    ]),

                // ==========================================
                // LEVEL 1: REPEATER AKTIVITAS
                // ==========================================
                Repeater::make('activities')
                    ->relationship() // Relasi Module -> Activities
                    ->label('Daftar Aktivitas')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->label('Judul Aktivitas (Misal: Aktivitas 1.1 Mengamati Benda)'),

                        // Konteks Utama (Gambar & Penjelasan yang berlaku untuk semua soal di bawahnya)
                        Section::make('Konteks / Referensi Utama')
                            ->description('Isi jika ada gambar atau cerita yang digunakan untuk menjawab beberapa soal sekaligus.')
                            ->schema([
                                FileUpload::make('image')
                                    ->image()
                                    ->directory('activity-images')
                                    ->label('Gambar Utama Aktivitas')
                                            ->imageEditor() // Memunculkan tombol edit gambar bawaan
                                            ->imagePreviewHeight('250') // Memperbesar kotak preview gambar
                                            ->panelAspectRatio('2:1')
                                            ->panelLayout('integrated')
                                            ->directory('activity-images'), // sesuaikan directory-nya (activity / question)
                                RichEditor::make('description')
                                    ->label('Teks Penjelasan / Instruksi Utama'),
                            ])
                            ->collapsible()
                            ->collapsed(), // Dibuat tertutup secara default agar form tidak terlalu panjang

                        // ==========================================
                        // LEVEL 2: REPEATER RENTETAN SOAL
                        // ==========================================
                        Repeater::make('questions')
                            ->relationship() // Relasi Activity -> Questions
                            ->label('Rentetan Soal')
                            ->schema([
                                // PENGATURAN TIPE & LAYOUT
                                Grid::make(2)->schema([
                                    Select::make('answer_format')
                                        ->options([
                                            'multiple_choice' => 'Pilihan Ganda / Ceklis',
                                            'number_input'    => 'Input Angka',
                                            'text_input'      => 'Input Teks Singkat',
                                        ])
                                        ->required()
                                        ->live()
                                        ->label('Tipe Jawaban'),

                                    Select::make('layout_position')
                                        ->options([
                                            'image_left'   => 'Gambar di Kiri, Soal di Kanan',
                                            'image_right'  => 'Soal di Kiri, Gambar di Kanan',
                                            'image_top'    => 'Gambar di Atas, Soal di Bawah',
                                            'image_bottom' => 'Soal di Atas, Gambar di Bawah',
                                        ])
                                        ->default('image_left')
                                        ->required()
                                        ->live()
                                        ->label('Posisi Gambar Spesifik Soal'),
                                ]),

                                // KONTEN SOAL
                                Section::make('Konten Pertanyaan')
                                    ->schema([
                                        FileUpload::make('image')
                                            ->image()
                                            ->imageEditor() // Memunculkan tombol edit gambar bawaan
                                            ->imagePreviewHeight('250') // Memperbesar kotak preview gambar
                                            ->panelAspectRatio('2:1')
                                            ->panelLayout('integrated')
                                            ->directory('activity-images') // sesuaikan directory-nya (activity / question)
                                            ->label('Gambar Upload'),

                                        RichEditor::make('question_text')
                                            ->required()
                                            ->label('Teks Pertanyaan')
                                            ->live(debounce: 500),
                                    ])->columns(2),

                                // OPSI JAWABAN (Hanya muncul jika pilihan ganda)
                                Repeater::make('options')
                                    ->schema([
                                        TextInput::make('teks_pilihan')
                                            ->required()
                                            ->label('Teks Opsi')
                                            ->live(debounce: 500),
                                        Checkbox::make('is_correct')
                                            ->label('Jawaban Benar?'),
                                    ])
                                    ->columns(2)
                                    ->label('Pilihan Jawaban')
                                    ->visible(fn (\Filament\Forms\Get $get): bool => $get('answer_format') === 'multiple_choice'),
                                // ==========================================
                                // KOTAK PREVIEW TAMPILAN MURID
                                // ==========================================
                                Section::make('Pratinjau Tampilan Murid')
                                    ->schema([
                                        Placeholder::make('preview')
                                            ->hiddenLabel()
                                            ->content(function (\Filament\Forms\Get $get) {
                                                return view('filament.components.preview-soal', [
                                                    'layout' => $get('layout_position'),
                                                    'teks'   => $get('question_text'),
                                                    'tipe'   => $get('answer_format'),
                                                    'opsi'   => $get('options'),
                                                    'gambar' => $get('image'),
                                                ]);
                                            })
                                    ])
                                    ->collapsible()
                                    ->collapsed(), // Ditutup default agar guru buka hanya saat butuh
                            ])
                            ->itemLabel(fn (array $state): ?string => strip_tags($state['question_text'] ?? 'Soal Baru'))
                            ->collapsible()
                            ->reorderable()
                            ->cloneable()
                            ->columnSpanFull(),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Aktivitas Baru')
                    ->collapsible()
                    ->reorderable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModules::route('/'),
            'create' => Pages\CreateModule::route('/create'),
            'edit' => Pages\EditModule::route('/{record}/edit'),
        ];
    }
}
