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
     */
    public function __invoke(BlobRequest $request, string $blob)
    {
        if (! $request->hasValidRelativeSignature()) {
            config('app.debug')
                ? throw new AccessDeniedHttpException
                : throw new NotFoundHttpException('File not found');
        }

        if (Storage::disk('local')->exists($blob)) {
            $name = $request->validated()->name;
            $forceDownload = $request->validated()->force_download;

            $headers = [
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
            ];

            try {
                if ($forceDownload) {
                    $response = Storage::disk('local')->download($blob, $name, $headers);
                } else {
                    $response = Storage::disk('local')->serve($request, $blob, $name, $headers);
                }

                return tap(
                    $response,
                    function ($response) use ($headers) {
                        if (! $response->headers->has('Content-Security-Policy')) {
                            $response->headers->replace($headers);
                        }
                    }
                );
            } catch (PathTraversalDetected $e) {
                throw new NotFoundHttpException('File not found');
            }
        } else {
            throw new NotFoundHttpException('File not found');
        }
    }
}
