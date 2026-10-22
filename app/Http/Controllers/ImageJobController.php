<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Jobs\ProcessImageJob;
use App\Models\ImageJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ImageJobController extends Controller
{
    private const ERRORS = [
        'provider_unavailable' => 'The processing engine is temporarily unavailable. Please try again in a moment.',
        'provider_not_configured' => 'The processing engine is not configured.',
        'provider_quota' => 'We are over capacity right now. Please try again shortly.',
        'unprocessable_image' => 'We could not process this image. Try a different photo.',
        'invalid_output' => 'Processing produced an invalid result. Please retry.',
    ];

    public function store(ImageUploadRequest $request): JsonResponse
    {
        $file = $request->file('image');
        $info = getimagesize($file->getRealPath());
        $ext = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2]];
        $key = 'uploads/'.Str::random(40).'.'.$ext; // server-generated name; user filename is never used

        ImageJob::disk()->putFileAs('uploads', $file, basename($key));

        $job = ImageJob::create([
            'public_id' => (string) Str::ulid(),
            'status' => ImageJob::QUEUED,
            'original_storage_key' => $key,
            'original_mime_type' => $info['mime'],
            'original_size_bytes' => $file->getSize(),
            'original_width' => $info[0],
            'original_height' => $info[1],
            'expires_at' => now()->addHours(config('bgremover.retention_hours')),
        ]);

        // Ownership: only the uploading session can read/download/delete this job.
        $request->session()->push('image_jobs', $job->public_id);

        ProcessImageJob::dispatch($job->id);

        return response()->json($this->payload($job), 202);
    }

    public function show(Request $request, ImageJob $imageJob): JsonResponse
    {
        $this->authorizeJob($request, $imageJob);

        return response()->json($this->payload($imageJob));
    }

    public function original(Request $request, ImageJob $imageJob): Response
    {
        $this->authorizeJob($request, $imageJob);
        abort_unless($imageJob->original_storage_key && ImageJob::disk()->exists($imageJob->original_storage_key), 404);

        return $this->file($imageJob->original_storage_key, $imageJob->original_mime_type);
    }

    public function result(Request $request, ImageJob $imageJob): Response
    {
        return $this->serveResult($request, $imageJob, false);
    }

    public function download(Request $request, ImageJob $imageJob): Response
    {
        return $this->serveResult($request, $imageJob, true);
    }

    public function destroy(Request $request, ImageJob $imageJob): JsonResponse
    {
        $this->authorizeJob($request, $imageJob);
        $imageJob->deleteFiles();
        $request->session()->put('image_jobs', array_values(array_diff($request->session()->get('image_jobs', []), [$imageJob->public_id])));
        $imageJob->delete();

        return response()->json(['deleted' => true]);
    }

    private function serveResult(Request $request, ImageJob $job, bool $attachment): Response
    {
        $this->authorizeJob($request, $job);
        abort_unless($job->status === ImageJob::COMPLETED && $job->result_storage_key && ImageJob::disk()->exists($job->result_storage_key), 404);

        return $this->file($job->result_storage_key, 'image/png', $attachment ? 'background-removed.png' : null);
    }

    private function file(string $key, string $mime, ?string $downloadName = null): Response
    {
        $headers = ['Content-Type' => $mime, 'X-Content-Type-Options' => 'nosniff'];
        if ($downloadName) {
            $headers['Content-Disposition'] = 'attachment; filename="'.$downloadName.'"';
        }

        $response = response()->file(ImageJob::disk()->path($key), $headers);
        $response->setPrivate();
        $response->setMaxAge(600);

        return $response;
    }

    private function authorizeJob(Request $request, ImageJob $job): void
    {
        abort_unless(in_array($job->public_id, $request->session()->get('image_jobs', []), true), 404);
        abort_if($job->status === ImageJob::EXPIRED, 410);
    }

    private function payload(ImageJob $job): array
    {
        $id = $job->public_id;

        return [
            'id' => $id,
            'status' => $job->status,
            'width' => $job->original_width,
            'height' => $job->original_height,
            'error' => $job->status === ImageJob::FAILED ? (self::ERRORS[$job->error_code] ?? 'Something went wrong while processing your image. Please retry.') : null,
            'original_url' => route('image-jobs.original', $id),
            'result_url' => $job->status === ImageJob::COMPLETED ? route('image-jobs.result', $id) : null,
            'download_url' => $job->status === ImageJob::COMPLETED ? route('image-jobs.download', $id) : null,
            'expires_at' => $job->expires_at->toIso8601String(),
        ];
    }
}
