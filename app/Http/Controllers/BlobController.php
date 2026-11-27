<?php

namespace App\Http\Controllers;

use App\Http\Requests\Blob\BlobRequest;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\PathTraversalDetected;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlobController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @queryParam force_download boolean Whether to force the browser to download the file instead of displaying it inline. No-example
     * @queryParam name string Custom filename to use when serving or downloading the file. No-example
     */
    public function __invoke(BlobRequest $request, string $blob)
    {
        if (! $request->hasValidRelativeSignatureWhileIgnoring(['force_download', 'name'])) {
            config('app.debug')
                ? throw new AccessDeniedHttpException
                : throw new NotFoundHttpException('File not found');
        }

        $headers = [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ];

        try {
            if (! Storage::disk('local')->fileExists($blob)) {
                throw new NotFoundHttpException('File not found');
            }

            $name = $request->validated('name');
            $forceDownload = (bool) $request->validated('force_download', false);

            if ($forceDownload) {
                $response = Storage::disk('local')->download($blob, $name, $headers);
            } else {
                $response = Storage::disk('local')->serve($request, $blob, $name, $headers);
            }

            return tap(
                $response,
                function ($response) use ($headers) {
                    if (! $response->headers->has('Content-Security-Policy')) {
                        $response->headers->add($headers);
                    }
                }
            );
        } catch (PathTraversalDetected $e) {
            throw new NotFoundHttpException('File not found');
        }
    }
}
