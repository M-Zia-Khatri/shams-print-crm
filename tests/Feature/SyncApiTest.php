<?php

use App\Enums\DailyShift;
use App\Models\Employee;
use App\Models\EmployeeDailyLaberiEntry;
use App\Models\Expense;
use App\Models\ItemEntry;
use App\Models\ItemPaymentReceived;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function viewerUser(): User
{
    return User::factory()->create(['role' => 'viewer']);
}

test('item entries sync returns created updated and no deleted ids for hard deleted records', function (): void {
    $since = Carbon::parse('2026-07-19 10:00:00');

    $created = ItemEntry::forceCreate([
        'lart_number' => 'L-200',
        'client_business_name' => 'Fresh Party',
        'description' => 'Fresh item',
        'image_url' => 'https://example.test/fresh.jpg',
        'darjan' => 2,
        'total_color' => 3,
        'total_rate' => 10,
        'total_amount' => 240,
        'size_description' => 'Large',
        'created_at' => $since->copy()->addMinute(),
        'updated_at' => $since->copy()->addMinute(),
    ]);

    $updated = ItemEntry::forceCreate([
        'lart_number' => 'L-100',
        'client_business_name' => 'Old Party',
        'description' => 'Updated item',
        'image_url' => 'https://example.test/updated.jpg',
        'darjan' => 1,
        'total_color' => 2,
        'total_rate' => 5,
        'total_amount' => 60,
        'size_description' => 'Small',
        'created_at' => $since->copy()->subDay(),
        'updated_at' => $since->copy()->addMinutes(2),
    ]);

    $response = $this->actingAs(viewerUser())
        ->getJson('/api/sync/item-entries?since='.$since->toISOString());

    $response->assertSuccessful()
        ->assertJsonPath('created.0.id', $created->id)
        ->assertJsonPath('updated.0.id', $updated->id)
        ->assertJsonPath('deleted', [])
        ->assertJsonStructure(['created', 'updated', 'deleted', 'server_time']);
});

test('expenses sync returns soft deleted ids', function (): void {
    $since = Carbon::parse('2026-07-19 10:00:00');

    $deleted = Expense::forceCreate([
        'description' => 'Deleted expense',
        'expense_date' => '2026-07-19',
        'expense_list' => [['name' => 'Ink', 'amount' => 50]],
        'total_expense' => 50,
        'created_at' => $since->copy()->subDay(),
        'updated_at' => $since->copy()->subDay(),
    ]);
    $deleted->delete();
    $deleted->forceFill(['deleted_at' => $since->copy()->addMinute()])->save();

    $response = $this->actingAs(viewerUser())
        ->getJson('/api/sync/expenses?since='.$since->toISOString());

    $response->assertSuccessful()
        ->assertJsonPath('deleted', [$deleted->id])
        ->assertJsonStructure(['created', 'updated', 'deleted', 'server_time']);
});

test('employee daily laberi sync returns soft deleted ids', function (): void {
    $since = Carbon::parse('2026-07-19 10:00:00');
    $employee = Employee::factory()->create();

    $deleted = EmployeeDailyLaberiEntry::factory()
        ->for($employee)
        ->create([
            'laberi_date' => '2026-07-18',
            'daily_shift' => DailyShift::Full,
            'created_at' => $since->copy()->subDay(),
            'updated_at' => $since->copy()->subDay(),
        ]);
    $deleted->delete();
    $deleted->forceFill(['deleted_at' => $since->copy()->addMinute()])->save();

    $response = $this->actingAs(viewerUser())
        ->getJson('/api/sync/employee-daily-laberi?since='.$since->toISOString());

    $response->assertSuccessful()
        ->assertJsonPath('deleted', [$deleted->id])
        ->assertJsonStructure(['created', 'updated', 'deleted', 'server_time']);
});

test('item payment received sync returns no deleted ids for hard deleted records', function (): void {
    $since = Carbon::parse('2026-07-19 10:00:00');

    $payment = ItemPaymentReceived::forceCreate([
        'description' => 'Payment received',
        'party_name' => 'Fresh Party',
        'received_amount' => '25.00',
        'created_at' => $since->copy()->addMinute(),
        'updated_at' => $since->copy()->addMinute(),
    ]);

    $response = $this->actingAs(viewerUser())
        ->getJson('/api/sync/item-payment-receiveds?since='.$since->toISOString());

    $response->assertSuccessful()
        ->assertJsonPath('created.0.id', $payment->id)
        ->assertJsonPath('deleted', [])
        ->assertJsonStructure(['created', 'updated', 'deleted', 'server_time']);
});

test('dashboard summary returns a flat server computed snapshot', function (): void {
    Employee::factory()->count(2)->create();
    ItemEntry::forceCreate([
        'lart_number' => 'L-300',
        'client_business_name' => 'Party',
        'description' => 'Item',
        'image_url' => 'https://example.test/item.jpg',
        'darjan' => 1,
        'total_color' => 1,
        'total_rate' => 10,
        'total_amount' => 120,
        'size_description' => 'Medium',
    ]);
    ItemPaymentReceived::forceCreate([
        'description' => 'Partial',
        'party_name' => 'Party',
        'received_amount' => '20.00',
    ]);
    Expense::forceCreate([
        'description' => 'Expense',
        'expense_date' => today(),
        'expense_list' => [['name' => 'Paper', 'amount' => 30]],
        'total_expense' => 30,
    ]);

    $response = $this->actingAs(viewerUser())->getJson('/api/sync/dashboard-summary');

    $response->assertSuccessful()
        ->assertJsonPath('total_employees', 2)
        ->assertJsonPath('pending_item_payments', 100)
        ->assertJsonPath('expense_total', 30)
        ->assertJsonStructure(['total_employees', 'working_today', 'leave_today', 'pending_item_payments', 'expense_total', 'server_time']);
});

test('sync endpoints require an allowed authenticated role', function (): void {
    $this->getJson('/api/sync/item-entries')->assertUnauthorized();

    $this->actingAs(User::factory()->create(['role' => 'staff']))
        ->getJson('/api/sync/item-entries')
        ->assertForbidden();
});
