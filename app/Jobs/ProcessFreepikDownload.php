<?php

namespace App\Jobs;

use App\Models\Config;
use App\Models\Freepik;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
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

            $resFreepik = json_decode($resFreepik->getBody()->getContents());
            Storage::disk('local')->put('freepik/' . $resFreepik->filename, base64_decode($resFreepik->file));

            $data->file_name = $resFreepik->filename;
            $data->file_path = 'freepik/' . $resFreepik->filename;
            $data->file_size = $resFreepik->size;
            $data->thumbnail = $resFreepik->thumbnail;
            $data->status = 'completed';
            $data->save();

            $quota = Config::where('key', 'freepik_limit')->first();
            $quota->value = $resFreepik->count;
            $quota->save();
        } catch (\Throwable $th) {
            Http::post(config('app.api_freepik_error_notif_url'));
            $data->status = 'failed';
            $data->save();
        }
    }
}
