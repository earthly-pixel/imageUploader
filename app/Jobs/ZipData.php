<?php

namespace App\Jobs;

use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ZipData implements ShouldQueue
{
    use Queueable;

    public $path;

    public $filename;

    /**
     * Create a new job instance.
     */
    public function __construct($path, $filename)
    {
        $this->path = $path;

        $this->filename = $filename;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // The path to the folder that you want to zip
        $folder = storage_path($this->path);

        // The name of the zip file that will be created
        $zipFile = storage_path('/app/public/').$this->filename.'.zip';

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

            Notification::make()
                ->success()
                ->title('Zip Success')
                ->body('Zip can be downloaded now.')
                ->actions([
                    Action::make('download')
                        ->url(Storage::url($this->filename.'.zip')),
                ])
                ->sendToDatabase(Auth::user());
        } else {
            Notification::make()
                ->warning()
                ->title('Zip Failed')
                ->body('Try again later.')
                ->sendToDatabase(Auth::user());
        }
    }
}
