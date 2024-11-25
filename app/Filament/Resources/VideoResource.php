<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VideoResource\Pages;
use App\Filament\Resources\VideoResource\RelationManagers;
use App\Jobs\ZipData;
use App\Models\Video;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class VideoResource extends Resource
{
    protected static ?string $model = Video::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file')
                    ->storeFileNamesIn('name')
                    ->directory('videos'),
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Tables\Actions\Action::make('zip_data')
                    ->action(function() {
                        dispatch(new ZipData('/app/public/videos/', 'fullVideo', Auth::user()));
                    })
                    ->after(fn() => Notification::make('notif_v')->info()->title('Please Wait')->body('Zip your data.')->send(Auth::user())),
                Tables\Actions\Action::make('clear')
                    ->action(function() {
                        Video::truncate();

                        File::cleanDirectory(storage_path('/app/public/uploads/'));
                    })
                
            ])
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('file')
                    ->formatStateUsing(function($state) {
                        return new HtmlString('<video style="height: 300px;" src="/storage/'.$state.'"></video>');
                    })
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
            'index' => Pages\ListVideos::route('/'),
            'create' => Pages\CreateVideo::route('/create'),
            'edit' => Pages\EditVideo::route('/{record}/edit'),
        ];
    }
}
