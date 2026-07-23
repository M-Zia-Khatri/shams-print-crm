<?php

use App\Models\User;
use App\Services\Contracts\ImageUploadServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// Helper: base payload for a single entry (no image).
function validEntryPayload(array $overrides = []): array
{
    return array_merge([
        'entries' => [
            0 => [
                'lart_number'          => 'LRT-001',
                'client_business_name' => 'Test Party',
                'description'          => 'Test description',
                'size_description'     => 'A4',
                'darjan'               => 5,
                'total_color'          => 4,
                'total_rate'           => '10.00',
            ],
        ],
    ], $overrides);
}

// Helper: create an admin user and act as them.
function itemEntryAdminUser(): User
{
    return User::factory()->admin()->create();
}

// ─── Scenario A: No image submitted → 422 validation error ────────────────────
test('batch submit without image fails with required validation error', function () {
    $admin = itemEntryAdminUser();

    $response = $this->actingAs($admin)
        ->post(route('item-entries.store'), validEntryPayload());

    $response->assertSessionHasErrors('entries.0.image');
});

// ─── Scenario B: Valid image submitted → dispatches job and redirects ─────────
test('batch submit with valid image succeeds and is queued', function () {
    $admin = itemEntryAdminUser();

    // Mock the upload service to avoid real Cloudinary calls.
    $this->instance(
        ImageUploadServiceInterface::class,
        \Mockery::mock(ImageUploadServiceInterface::class, function ($mock) {
            $mock->shouldReceive('upload')
                ->once()
                ->andReturn([
                    'url'           => 'https://res.cloudinary.com/demo/image/upload/v1/item_entries/test.jpg',
                    'public_id'     => 'item_entries/test',
                    'secure_url'    => 'https://res.cloudinary.com/demo/image/upload/v1/item_entries/test.jpg',
                    'width'         => 800,
                    'height'        => 600,
                    'bytes'         => 51200,
                    'format'        => 'jpg',
                    'resource_type' => 'image',
                ]);
        })
    );

    // Use create() with an explicit MIME type — avoids GD extension requirement.
    $image = UploadedFile::fake()->create('photo.jpg', 50, 'image/jpeg');

    $payload = [
        'entries' => [
            0 => [
                'lart_number'          => 'LRT-002',
                'client_business_name' => 'Test Party',
                'description'          => 'Valid image test',
                'size_description'     => 'A4',
                'darjan'               => 3,
                'total_color'          => 2,
                'total_rate'           => '5.00',
                'image'                => $image,
            ],
        ],
    ];

    $response = $this->actingAs($admin)
        ->post(route('item-entries.store'), $payload);

    $response->assertRedirect(route('item-entries.index'));
    $response->assertSessionHas('status');
});

// ─── Scenario C: Invalid file type → 422 mimes validation error ───────────────
test('batch submit with non-image file fails with mimes validation error', function () {
    $admin = itemEntryAdminUser();

    // A .txt file disguised — fails the mimes:jpg,jpeg,png,webp rule.
    $badFile = UploadedFile::fake()->create('document.txt', 10, 'text/plain');

    $payload = [
        'entries' => [
            0 => [
                'lart_number'          => 'LRT-003',
                'client_business_name' => 'Test Party',
                'description'          => 'Bad file test',
                'size_description'     => 'A4',
                'darjan'               => 1,
                'total_color'          => 1,
                'total_rate'           => '1.00',
                'image'                => $badFile,
            ],
        ],
    ];

    $response = $this->actingAs($admin)
        ->post(route('item-entries.store'), $payload);

    $response->assertSessionHasErrors('entries.0.image');
});
