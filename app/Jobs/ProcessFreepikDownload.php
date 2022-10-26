<?php

namespace App\Jobs;

use App\Models\Freepik;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessFreepikDownload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $id = $this->data;
        $data = Freepik::find($id);
        try {
            $reqFreepik = new \GuzzleHttp\Client();
            $resFreepik = $reqFreepik->get(config('app.api_freepik_url') . $data->url, ['timeout' => 180]);
            // $resFreepik = $reqFreepik->get('https://fpk.niwabi.my.id/download?url=' . $data->url, ['timeout' => 180]);
            $fileSize = $resFreepik->getHeaders()['Content-Length'][0];
            $fileName = explode(";", $resFreepik->getHeaders()['Content-Disposition'][0]);
            preg_match_all('`"([^"]*)"`', $fileName[1], $resultName);
            $fileName = $resultName[1][0];
            Storage::disk('local')->put('freepik/' . $fileName, $resFreepik->getBody()->getContents());

            $data->file_name = $fileName;
            $data->file_path = 'freepik/' . $fileName;
            $data->file_size = $fileSize;
            $data->status = 'completed';
            $data->save();
        } catch (\Throwable $th) {
            $data->status = 'failed';
            $data->save();
        }
    }
}
