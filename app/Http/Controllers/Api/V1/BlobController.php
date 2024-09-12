<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blob\BlobRequest;
use App\Repositories\StorageRepository;
use App\Utils\Encoder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BlobController extends Controller
{
    public function __construct(
        protected StorageRepository $storageRepository,
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(BlobRequest $request): BinaryFileResponse
    {
        $manifest = Encoder::base64UrlDecode($request->blob);
        $name = $request->query('name', json_decode($manifest)->name);
        $filePath = $this->storageRepository->get($manifest);

        if ($request->boolean('force_download')) {
            return response()->download($filePath, $name);
        } else {
            return response()
                ->file($filePath)
                ->setContentDisposition('inline', $name);
        }
    }
}
