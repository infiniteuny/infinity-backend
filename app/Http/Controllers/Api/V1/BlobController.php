<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blob\BlobRequest;
use App\Repositories\StorageFacade;
use App\Utils\Encoder;

class BlobController extends Controller
{
    public function __construct(
        protected StorageFacade $storageFacade,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(BlobRequest $request)
    {
        $manifest = Encoder::base64UrlDecode($request->blob);
        $name = $request->query('name', json_decode($manifest)->name);
        $filePath = $this->storageFacade->get($manifest);

        if ($request->boolean('force_download')) {
            return response()->download($filePath, $name);
        } else {
            return response()
                ->file($filePath)
                ->setContentDisposition('inline', $name);
        }
    }
}
