<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Secure generic upload for mobile clients.
 *
 * - MIME + size validated server-side; extension derived from detected
 *   MIME (never the client filename).
 * - Random filenames (unenumerable); school-scoped directories.
 * - Sensitive purposes (ppdb/medical/payment_proof) live on the private
 *   disk and are served only via the authorized download endpoint.
 */
class UploadController extends Controller
{
    private const array IMAGE_PURPOSES = ['chat', 'assignment'];
    private const array DOC_PURPOSES = ['ppdb', 'medical', 'payment_proof'];

    public function store(Request $request): JsonResponse
    {
        $imageOnly = in_array($request->input('purpose'), self::IMAGE_PURPOSES, true);

        $validated = $request->validate([
            'file' => [
                'required', 'file', 'max:10240',
                $imageOnly ? 'mimes:jpg,jpeg,png,webp' : 'mimes:pdf,jpg,jpeg,png',
            ],
            'purpose' => 'required|string|in:chat,assignment,ppdb,medical,payment_proof',
        ]);

        $schoolId = (int) $request->user()->school_id;
        $purpose = $validated['purpose'];
        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $validated['file'];

        $ext = $file->guessExtension() ?: 'bin';
        $name = Str::uuid()->toString().'.'.$ext;
        $dir = "uploads/{$schoolId}/{$purpose}/".now()->format('Y/m');

        if (in_array($purpose, self::IMAGE_PURPOSES, true)) {
            // intervention/image v4 API (decode + scaleDown + typed encode).
            $image = \Intervention\Image\Laravel\Facades\Image::decode($file);
            $image->scaleDown(1600, 1600);
            $path = "{$dir}/{$name}";
            Storage::disk('public')->put($path, (string) $image->encodeUsingFileExtension($ext));
        } else {
            $path = $file->storeAs($dir, $name, 'local');
        }

        return response()->json([
            'path' => $path,
            'url' => in_array($purpose, self::IMAGE_PURPOSES, true)
                ? Storage::disk('public')->url($path)
                : null,
            'purpose' => $purpose,
            'size' => $file->getSize(),
            'mime' => $file->getMimeType(),
        ], 201);
    }

    /**
     * Authorized file download. The path must live under the caller's own
     * school directory; traversal outside is rejected with 404.
     */
    public function show(Request $request): StreamedResponse|JsonResponse
    {
        $data = $request->validate(['path' => 'required|string|max:500']);

        $path = str_replace('\\', '/', $data['path']);
        $prefix = 'uploads/'.(int) $request->user()->school_id.'/';

        if (str_contains($path, '..') || ! str_starts_with($path, $prefix)) {
            abort(404);
        }

        $disk = str_contains($path, '/ppdb/') || str_contains($path, '/medical/') || str_contains($path, '/payment_proof/')
            ? 'local'
            : 'public';

        if (! Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        $mime = Storage::disk($disk)->mimeType($path) ?? 'application/octet-stream';
        $stream = Storage::disk($disk)->readStream($path);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
