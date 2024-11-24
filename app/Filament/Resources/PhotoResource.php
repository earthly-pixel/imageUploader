<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoResource\Pages;
use App\Filament\Resources\PhotoResource\RelationManagers;
use App\Models\Photo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\FormsComponent;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PhotoResource extends Resource
{
    protected static ?string $model = Photo::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file')
                    ->image()
                    ->storeFileNamesIn('name')
                    ->directory('uploads'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\Action::make('zip_data')
                    ->action(function() {
                        // The path to the folder that you want to zip
                        $folder = storage_path('/app/public/uploads/');

                        // The name of the zip file that will be created
                        $zipFile = storage_path('/app/public/').'fullImage.zip';

                        // Check if file exist
                        if(File::exists($zipFile))
                        {
                            // Delete the archive file
                            unlink($zipFile);
                        }

                        // Initialize the archive object
                        $zip = new \ZipArchive();

                        ini_set('memory_limit', '2048M');
                        ini_set('max_execution_time', '6000');

                        // Create the archive
                        if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE) {
                            // Add all the files in the folder
                            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder));
                            foreach ($files as $name => $file) {
                                if (!$file->isDir()) {
                                    $zip->addFile(realpath($file), $file->getFilename());
                                }
                            }

                            // Close the archive
                            $zip->close();

                            // Send the archive to the browser for download
                            header('Content-Type: application/zip');
                            header('Content-Length: ' . filesize($zipFile));
                            header('Content-Disposition: attachment; filename="' . basename($zipFile) . '"');
                            readfile($zipFile);

                            Notification::make()
                                ->success()
                                ->title('Dwonload in Progress')
                                ->send();
                        } else {
                            Notification::make()
                                ->warning()
                                ->title('Zip Failed')
                                ->send();
                        }
                    })
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\ImageColumn::make('thumb')
                    ->action(fn($state) => redirect()->to(Storage::url($state))),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPhotos::route('/'),
            'create' => Pages\CreatePhoto::route('/create'),
            'edit' => Pages\EditPhoto::route('/{record}/edit'),
        ];
    }

    public static function zipData()
    {
        // The path to the folder that you want to zip
        $folder = storage_path('/app/public/uploads/');

        // The name of the zip file that will be created
        $zipFile = 'fullImage.zip';

        // Initialize the archive object
        $zip = new \ZipArchive();

        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', '6000');

        // Create the archive
        if ($zip->open($zipFile, \ZipArchive::CREATE) === TRUE) {
            // Add all the files in the folder
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder));
            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $zip->addFile(realpath($file), $file->getFilename());
                }
            }

            // Close the archive
            $zip->close();

            // Send the archive to the browser for download
            header('Content-Type: application/zip');
            header('Content-Length: ' . filesize($zipFile));
            header('Content-Disposition: attachment; filename="' . basename($zipFile) . '"');
            readfile($zipFile);

            // Delete the archive file
            unlink($zipFile);

            Notification::make()
                ->success()
                ->title('Dwonload in Progress')
                ->send();
        } else {
            Notification::make()
                ->warning()
                ->title('Zip Failed')
                ->send();
        }
    }
}
