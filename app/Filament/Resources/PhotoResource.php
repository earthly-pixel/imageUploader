<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhotoResource\Pages;
use App\Filament\Resources\PhotoResource\RelationManagers;
use App\Jobs\ZipData;
use App\Models\Photo;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\FormsComponent;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PhotoResource extends Resource
{
    protected static ?string $model = Photo::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

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
                Tables\Actions\Action::make('zip_data_choice')
                    ->form([
                        Forms\Components\TextInput::make('tag'),
                    ])
                    ->action(function($data) {
                        $tag = $data['tag'];
                        dispatch(new ZipData('/app/public/uploads/'.$tag.'/', 'fullImage', static::user()));
                    })
                    ->visible(fn() => static::user()->hasRole('super_admin'))
                    ->after(fn() => Notification::make('notif_p')->info()->title('Please Wait')->body('Zip your data.')->send(Auth::user())),
                Tables\Actions\Action::make('zip_data')
                    ->action(function() {
                        $tag = static::user()->tag;
                        dispatch(new ZipData('/app/public/uploads/'.$tag.'/', 'fullImage', static::user()));
                    })
                    ->visible(fn() => static::user()->hasRole('admin'))
                    ->hidden(fn() => static::user()->hasRole('super_admin'))
                    ->after(fn() => Notification::make('notif_p')->info()->title('Please Wait')->body('Zip your data.')->send(Auth::user())),
                Tables\Actions\Action::make('clear')
                    ->action(function() {
                        Photo::truncate();

                        File::cleanDirectory(storage_path('/app/public/uploads/'));
                    })
                    ->visible(fn() => static::user()->hasRole('super_admin'))
            ])
            ->columns([
                Tables\Columns\TextColumn::make('tag')
                    ->searchable()
                    ->visible(function() {
                        $user = User::find(Auth::id());
                        return $user->hasRole('super_admin');
                    }),
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

    public static function getEloquentQuery(): Builder
    {
        if(static::user()->hasRole('super_admin'))
        {
            return parent::getEloquentQuery();    
        }
        return parent::getEloquentQuery()->where('tag', static::user()->tag);
    }

    public static function user() {
        $user = User::find(Auth::id());

        return $user;
    }
}
