<?php

namespace Tests\Feature;

use App\Jobs\ProcessImageJob;
use App\Models\ImageJob;
use App\Services\Providers\BackgroundRemovalProvider;
use App\Services\Providers\ProviderException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function rgbaPng(): string
    {
        $im = imagecreatetruecolor(40, 30);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        imagefill($im, 0, 0, imagecolorallocatealpha($im, 255, 0, 0, 127));
        ob_start();
        imagepng($im);

        return ob_get_clean();
    }

    private function upload(string $name = 'p.png', ?UploadedFile $file = null)
    {
        $file ??= UploadedFile::fake()->image($name, 100, 80);

        return $this->postJson('/image-jobs', ['image' => $file]);
    }

    public function test_pages_render(): void
    {
        foreach (['/', '/pricing', '/faq', '/privacy', '/terms', '/contact', '/how-it-works', '/use-cases', '/about', '/sitemap.xml', '/robots.txt'] as $u) {
            $this->get($u)->assertOk();
        }
        $this->get('/nope')->assertNotFound();
    }

    public function test_upload_queues_job_with_random_storage_name(): void
    {
        Queue::fake();
        $res = $this->upload('../../evil.png')->assertStatus(202)->assertJsonPath('status', 'queued');
        Queue::assertPushed(ProcessImageJob::class);
        $job = ImageJob::first();
        $this->assertStringNotContainsString('evil', $job->original_storage_key);
        Storage::disk('local')->assertExists($job->original_storage_key);
    }

    public function test_rejects_bad_files(): void
    {
        $this->upload(file: UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'))->assertStatus(422);
        $this->upload(file: UploadedFile::fake()->createWithContent('a.png', 'not really an image'))->assertStatus(422);
        $this->upload(file: UploadedFile::fake()->image('big.png', 100, 100)->size(20000))->assertStatus(422);
        $this->upload(file: UploadedFile::fake()->image('wide.png', 9000, 10))->assertStatus(422);
        $this->postJson('/image-jobs', [])->assertStatus(422);
        $this->assertSame(0, ImageJob::count());
    }

    public function test_full_flow_and_download_preserves_alpha(): void
    {
        $png = $this->rgbaPng();
        $this->app->bind(BackgroundRemovalProvider::class, fn () => new class($png) implements BackgroundRemovalProvider {
            public function __construct(private string $png) {}
            public function name(): string { return 'fake'; }
            public function remove(string $path): string { return $this->png; }
        });

        $id = $this->upload()->json('id'); // sync queue in tests
        $this->getJson("/image-jobs/$id")->assertJsonPath('status', 'completed');
        $res = $this->get("/image-jobs/$id/download")->assertOk();
        $this->assertSame('image/png', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $res->headers->get('Content-Disposition'));
        $this->assertSame(6, ord(file_get_contents($res->baseResponse->getFile()->getPathname())[25]));
        $this->get("/image-jobs/$id/original")->assertOk();
    }

    public function test_provider_failure_marks_job_failed_with_safe_message(): void
    {
        $this->app->bind(BackgroundRemovalProvider::class, fn () => new class implements BackgroundRemovalProvider {
            public function name(): string { return 'fake'; }
            public function remove(string $path): string { throw new ProviderException('provider_unavailable', 'secret detail', false); }
        });
        $id = $this->upload()->json('id');
        $this->getJson("/image-jobs/$id")->assertJsonPath('status', 'failed')
            ->assertJsonPath('download_url', null);
        $this->get("/image-jobs/$id/download")->assertNotFound();
    }

    public function test_non_transparent_output_is_rejected(): void
    {
        $im = imagecreatetruecolor(10, 10);
        ob_start(); imagejpeg($im); $jpg = ob_get_clean();
        $this->app->bind(BackgroundRemovalProvider::class, fn () => new class($jpg) implements BackgroundRemovalProvider {
            public function __construct(private string $b) {}
            public function name(): string { return 'fake'; }
            public function remove(string $path): string { return $this->b; }
        });
        $id = $this->upload()->json('id');
        $this->getJson("/image-jobs/$id")->assertJsonPath('status', 'failed')->assertJsonPath('error', fn ($e) => str_contains($e, 'invalid'));
    }

    public function test_other_sessions_cannot_access_job(): void
    {
        Queue::fake();
        $id = $this->upload()->json('id');
        $this->getJson("/image-jobs/$id")->assertOk();

        $this->flushSession();
        $this->getJson("/image-jobs/$id")->assertNotFound();
        $this->get("/image-jobs/$id/original")->assertNotFound();
        $this->deleteJson("/image-jobs/$id")->assertNotFound();
    }

    public function test_delete_removes_files(): void
    {
        Queue::fake();
        $id = $this->upload()->json('id');
        $key = ImageJob::first()->original_storage_key;
        $this->deleteJson("/image-jobs/$id")->assertOk();
        Storage::disk('local')->assertMissing($key);
        $this->assertSame(0, ImageJob::count());
    }

    public function test_cleanup_expires_old_jobs(): void
    {
        Queue::fake();
        $id = $this->upload()->json('id');
        $job = ImageJob::first();
        $key = $job->original_storage_key;
        $job->update(['expires_at' => now()->subMinute()]);
        $this->artisan('images:cleanup')->assertSuccessful();
        Storage::disk('local')->assertMissing($key);
        $this->getJson("/image-jobs/$id")->assertStatus(410);
    }
}
