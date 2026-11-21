<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\User;
use App\Notifications\PaymentReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_submit_a_payment_with_a_receipt(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        Payment::create([
            'user_id' => $tenant->id,
            'is_billing_record' => true,
            'amount' => 2500,
            'amount_paid' => 0,
            'month_year' => 'November 2026',
            'status' => 'Unpaid',
        ]);

        $this->actingAs($tenant)
            ->get(route('tenant.payments.create'))
            ->assertOk()
            ->assertSee('Terms & Conditions for Payment Submissions')
            ->assertSee('Message for admin (optional)');

        $response = $this->actingAs($tenant)->post(route('tenant.payments.store'), [
            'amount' => 2500,
            'amount_paid' => 1250,
            'payment_coverage' => 'partial',
            'month_year' => 'November 2026',
            'due_date' => '2026-11-30',
            'payment_method' => 'Online payment',
            'receipt' => UploadedFile::fake()->image('receipt.png'),
            'notes' => 'GCash reference 123456',
        ]);

        $response->assertRedirect(route('tenant.dashboard'));

        $this->assertDatabaseHas('payments', [
            'user_id' => $tenant->id,
            'amount' => 2500,
            'amount_paid' => 0,
            'pending_amount' => 1250,
            'approval_status' => 'Pending',
            'status' => 'Unpaid',
            'payment_method' => 'Online payment',
            'notes' => 'GCash reference 123456',
        ]);

        $payment = Payment::where('user_id', $tenant->id)
            ->where('month_year', 'November 2026')
            ->firstOrFail();
        Storage::disk('local')->assertExists($payment->receipt_path);
        $this->assertSame('2500.00', $payment->balance);
        $this->assertSame('Pending approval', $payment->payment_state);

        $this->actingAs($tenant)
            ->get(route('tenant.payments.history'))
            ->assertOk()
            ->assertSee('₱2,500.00')
            ->assertSee('₱1,250.00')
            ->assertSee('Receipt visible after admin confirmation')
            ->assertDontSee('Download PDF')
            ->assertDontSee('View upload');

        $this->actingAs($admin)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('₱2,500.00')
            ->assertSee('₱1,250.00')
            ->assertSee('Pending approval')
            ->assertSee('Confirm payment')
            ->assertSee('View upload')
            ->assertSee('Tenant message')
            ->assertSee('GCash reference 123456');

        $this->actingAs($admin)
            ->get(route('payments.uploaded-receipt.view', $payment))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        $this->actingAs($admin)
            ->get(route('payments.receipt', $payment))
            ->assertForbidden();

        $this->actingAs($tenant)
            ->get(route('payments.uploaded-receipt.view', $payment))
            ->assertForbidden();

        $this->actingAs($tenant)
            ->get(route('payments.receipt', $payment))
            ->assertForbidden();

        $this->actingAs($tenant)
            ->get(route('tenant.payments.create'))
            ->assertOk()
            ->assertSee('data-pending-months="{&quot;November 2026&quot;:true}"', false);

        $this->actingAs($tenant)
            ->post(route('tenant.payments.store'), [
                'amount_paid' => 100,
                'payment_coverage' => 'partial',
                'month_year' => 'November 2026',
                'payment_method' => 'Online payment',
            ])
            ->assertSessionHasErrors([
                'amount_paid' => 'Your previous payment for this month is awaiting admin confirmation.',
            ]);

        $this->actingAs($admin)
            ->post(route('payments.approve', $payment))
            ->assertRedirect(route('payments.index'));

        $payment->refresh();
        $this->assertSame('1250.00', $payment->amount_paid);
        $this->assertSame('0.00', $payment->pending_amount);
        $this->assertSame('1250.00', $payment->balance);
        $this->assertSame('Partial', $payment->status);
        $this->assertSame('Approved', $payment->approval_status);

        $this->actingAs($tenant)
            ->get(route('tenant.payments.history'))
            ->assertOk()
            ->assertSee('Download PDF')
            ->assertSee('View upload');

        $this->actingAs($tenant)
            ->get(route('payments.uploaded-receipt.view', $payment))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');

        $this->actingAs($tenant)
            ->get(route('payments.receipt', $payment))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertSeeText('PHP 2,500.00')
            ->assertSeeText('PHP 1,250.00')
            ->assertSeeText('PHP 1,250.00');
    }

    public function test_admin_can_change_payment_status_and_download_pdf_receipt(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $payment = Payment::create([
            'user_id' => $tenant->id,
            'amount' => 1800,
            'month_year' => 'November 2026',
            'status' => 'Unpaid',
        ]);

        $response = $this->actingAs($admin)->put(route('payments.update', $payment), [
            'user_id' => $tenant->id,
            'amount' => 1800,
            'month_year' => 'November 2026',
            'status' => 'Paid',
            'due_date' => '2026-11-30',
            'payment_method' => 'Paid in person',
        ]);

        $response->assertRedirect(route('payments.index'));
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'Paid',
            'payment_method' => 'Paid in person',
        ]);

        $this->actingAs($admin)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('₱1,800.00')
            ->assertSee('₱0.00');

        $receipt = $this->actingAs($tenant)->get(route('payments.receipt', $payment));
        $receipt->assertOk();
        $receipt->assertHeader('Content-Type', 'application/pdf');
        $receipt->assertSeeText('PHP 1,800.00');
        $receipt->assertSeeText('PHP 0.00');
    }

    public function test_full_payment_uses_the_total_due_as_the_amount_paid(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        Payment::create([
            'user_id' => $tenant->id,
            'is_billing_record' => true,
            'amount' => 3550.25,
            'amount_paid' => 0,
            'month_year' => 'November 2026',
            'status' => 'Unpaid',
        ]);

        $this->actingAs($tenant)->post(route('tenant.payments.store'), [
            'amount' => 1,
            'amount_paid' => 1,
            'payment_coverage' => 'full',
            'month_year' => 'November 2026',
            'payment_method' => 'Paid in person',
        ])->assertRedirect(route('tenant.dashboard'));

        $this->assertDatabaseHas('payments', [
            'user_id' => $tenant->id,
            'amount' => 3550.25,
            'amount_paid' => 0,
            'pending_amount' => 3550.25,
            'approval_status' => 'Pending',
            'status' => 'Unpaid',
        ]);

        $payment = Payment::where('user_id', $tenant->id)->firstOrFail();
        $this->actingAs($admin)
            ->post(route('payments.approve', $payment))
            ->assertRedirect(route('payments.index'));

        $this->assertDatabaseHas('payments', [
            'user_id' => $tenant->id,
            'amount' => 3550.25,
            'amount_paid' => 3550.25,
            'pending_amount' => 0,
            'approval_status' => 'Approved',
            'status' => 'Paid',
        ]);
    }

    public function test_tenant_payment_form_uses_only_their_admin_assigned_outstanding_amounts(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $otherTenant = User::factory()->create(['role' => 'tenant']);
        Payment::create([
            'user_id' => $tenant->id,
            'is_billing_record' => true,
            'amount' => 1500,
            'amount_paid' => 250,
            'month_year' => 'November 2026',
            'status' => 'Partial',
        ]);
        Payment::create([
            'user_id' => $otherTenant->id,
            'is_billing_record' => true,
            'amount' => 9000,
            'amount_paid' => 0,
            'month_year' => 'November 2026',
            'status' => 'Unpaid',
        ]);

        $this->actingAs($tenant)
            ->get(route('tenant.payments.create'))
            ->assertOk()
            ->assertSee('data-due-amounts="{&quot;November 2026&quot;:&quot;1250.00&quot;}"', false)
            ->assertDontSee('9000.00');
    }

    public function test_tenant_cannot_submit_payment_without_an_admin_assigned_due_for_the_month(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($tenant)->post(route('tenant.payments.store'), [
            'amount' => 1000,
            'amount_paid' => 1000,
            'payment_coverage' => 'full',
            'month_year' => 'November 2026',
            'payment_method' => 'Paid in person',
        ])->assertSessionHasErrors([
            'amount' => 'No outstanding payment was set by the admin for this month. Please contact the admin.',
        ]);

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_admin_carries_an_unpaid_balance_once_into_the_next_due(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $previousPayment = Payment::create([
            'user_id' => $tenant->id,
            'is_billing_record' => true,
            'amount' => 1000,
            'amount_paid' => 0,
            'month_year' => 'November 2026',
            'status' => 'Unpaid',
        ]);
        $futurePayment = Payment::create([
            'user_id' => $tenant->id,
            'is_billing_record' => true,
            'amount' => 900,
            'amount_paid' => 0,
            'month_year' => 'January 2027',
            'status' => 'Unpaid',
        ]);

        $this->actingAs($tenant)->post(route('tenant.payments.store'), [
            'amount' => 1000,
            'amount_paid' => 250,
            'payment_coverage' => 'partial',
            'month_year' => 'November 2026',
            'payment_method' => 'Paid in person',
        ])->assertRedirect(route('tenant.dashboard'));

        $this->assertSame('1000.00', $previousPayment->fresh()->balance);
        $this->assertSame('250.00', $previousPayment->fresh()->pending_amount);

        $this->actingAs($admin)->post(route('payments.store'), [
            'user_id' => $tenant->id,
            'amount' => 2000,
            'amount_paid' => 0,
            'month_year' => 'December 2026',
            'status' => 'Unpaid',
            'due_date' => today()->addDays(3)->toDateString(),
        ])->assertSessionHasErrors([
            'user_id' => "Confirm or reject the tenant's pending payment before creating the next billing period.",
        ]);

        $this->actingAs($admin)
            ->post(route('payments.approve', $previousPayment))
            ->assertRedirect(route('payments.index'));
        $this->assertSame('750.00', $previousPayment->fresh()->balance);

        $this->actingAs($admin)->post(route('payments.store'), [
            'user_id' => $tenant->id,
            'amount' => 2000,
            'amount_paid' => 0,
            'month_year' => 'December 2026',
            'status' => 'Unpaid',
            'due_date' => today()->addDays(3)->toDateString(),
        ])->assertRedirect(route('payments.index'));

        $nextPayment = Payment::where('user_id', $tenant->id)
            ->where('month_year', 'December 2026')
            ->firstOrFail();
        $previousPayment->refresh();

        $this->assertSame('2750.00', $nextPayment->amount);
        $this->assertSame('750.00', $nextPayment->carried_in_amount);
        $this->assertSame('0.00', $previousPayment->balance);
        $this->assertSame('750.00', $previousPayment->carried_balance);
        $this->assertSame('Carried forward', $previousPayment->payment_state);
        $this->assertSame('December 2026', $previousPayment->carriedTo->month_year);
        $this->assertSame('900.00', $futurePayment->fresh()->balance);
        $this->assertNull($futurePayment->fresh()->carried_to_payment_id);

        $this->actingAs($admin)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('₱2,750.00')
            ->assertSee('Includes ₱750.00 carried from a previous balance')
            ->assertSee('₱750.00 carried to December 2026');

        $this->actingAs($tenant)
            ->get(route('tenant.dashboard'))
            ->assertOk()
            ->assertSee('Remaining balance')
            ->assertSee('₱2,750.00')
            ->assertSee('Includes ₱750.00 carried from a previous balance');

        $this->actingAs($tenant)
            ->get(route('tenant.payments.history'))
            ->assertOk()
            ->assertSee('₱750.00 carried to December 2026');
    }

    public function test_admin_can_reject_a_pending_payment_without_reducing_the_balance(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $payment = Payment::create([
            'user_id' => $tenant->id,
            'is_billing_record' => true,
            'amount' => 1000,
            'amount_paid' => 0,
            'month_year' => 'November 2026',
            'status' => 'Unpaid',
            'approval_status' => 'Pending',
            'pending_amount' => 400,
        ]);

        $this->actingAs($tenant)
            ->post(route('payments.approve', $payment))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('payments.reject', $payment))
            ->assertRedirect(route('payments.index'));

        $payment->refresh();
        $this->assertSame('Rejected', $payment->approval_status);
        $this->assertSame('0.00', $payment->pending_amount);
        $this->assertSame('0.00', $payment->amount_paid);
        $this->assertSame('1000.00', $payment->balance);
        $this->assertSame('Rejected', $payment->payment_state);

        $this->actingAs($tenant)
            ->get(route('tenant.payments.history'))
            ->assertOk()
            ->assertSee('Submission rejected; balance remains due');

        $this->actingAs($tenant)->post(route('tenant.payments.store'), [
            'amount_paid' => 200,
            'payment_coverage' => 'partial',
            'month_year' => 'November 2026',
            'payment_method' => 'Paid in person',
        ])->assertRedirect(route('tenant.dashboard'));

        $payment->refresh();
        $this->assertSame('Pending', $payment->approval_status);
        $this->assertSame('200.00', $payment->pending_amount);
        $this->assertSame('0.00', $payment->amount_paid);
        $this->assertSame('1000.00', $payment->balance);
    }

    public function test_admin_cannot_confirm_the_same_payment_twice(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $payment = Payment::create([
            'user_id' => $tenant->id,
            'is_billing_record' => true,
            'amount' => 1000,
            'amount_paid' => 0,
            'month_year' => 'November 2026',
            'status' => 'Unpaid',
            'approval_status' => 'Pending',
            'pending_amount' => 400,
        ]);

        $this->actingAs($admin)
            ->post(route('payments.approve', $payment))
            ->assertRedirect(route('payments.index'));

        $this->actingAs($admin)
            ->post(route('payments.approve', $payment))
            ->assertSessionHasErrors([
                'approval_status' => 'This payment does not have a pending submission to confirm.',
            ]);

        $this->assertSame('400.00', $payment->fresh()->amount_paid);
        $this->assertSame('600.00', $payment->fresh()->balance);
    }

    public function test_admin_search_filters_payments_by_tenant_name_and_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create([
            'name' => 'Kerby Verador',
            'email' => 'kerby.verador@example.test',
            'role' => 'tenant',
        ]);
        $otherTenant = User::factory()->create([
            'name' => 'Juan Tenant',
            'email' => 'juan@example.test',
            'role' => 'tenant',
        ]);

        Payment::create([
            'user_id' => $tenant->id,
            'amount' => 2000,
            'amount_paid' => 2000,
            'month_year' => 'November 2026',
            'status' => 'Paid',
        ]);
        Payment::create([
            'user_id' => $otherTenant->id,
            'amount' => 900,
            'amount_paid' => 0,
            'month_year' => 'November 2026',
            'status' => 'Unpaid',
        ]);

        $this->actingAs($admin)
            ->get(route('payments.index', ['search' => 'Kerby Verador', 'status' => 'Paid']))
            ->assertOk()
            ->assertSee('Kerby Verador')
            ->assertDontSee('Juan Tenant');

        $this->actingAs($admin)
            ->get(route('payments.index', ['search' => 'kerby.verador@example.test']))
            ->assertOk()
            ->assertSee('Kerby Verador')
            ->assertDontSee('Juan Tenant');
    }

    public function test_admin_can_send_reminders_for_selected_month_only_to_tenants_with_open_balances(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);
        $dueTenant = User::factory()->create(['role' => 'tenant']);
        $paidTenant = User::factory()->create(['role' => 'tenant']);
        $pendingTenant = User::factory()->create(['role' => 'tenant']);
        $otherMonthTenant = User::factory()->create(['role' => 'tenant']);
        $otherDayTenant = User::factory()->create(['role' => 'tenant']);

        $duePayment = Payment::create([
            'user_id' => $dueTenant->id,
            'is_billing_record' => true,
            'amount' => 1250,
            'amount_paid' => 250,
            'month_year' => 'November 2026',
            'due_date' => '2026-11-30',
            'status' => 'Partial',
            'approval_status' => 'Approved',
        ]);
        Payment::create([
            'user_id' => $paidTenant->id,
            'is_billing_record' => true,
            'amount' => 900,
            'amount_paid' => 900,
            'month_year' => 'November 2026',
            'status' => 'Paid',
            'approval_status' => 'Approved',
        ]);
        Payment::create([
            'user_id' => $pendingTenant->id,
            'is_billing_record' => true,
            'amount' => 800,
            'amount_paid' => 0,
            'pending_amount' => 400,
            'month_year' => 'November 2026',
            'status' => 'Unpaid',
            'approval_status' => 'Pending',
        ]);
        Payment::create([
            'user_id' => $otherMonthTenant->id,
            'is_billing_record' => true,
            'amount' => 700,
            'amount_paid' => 0,
            'month_year' => 'December 2026',
            'due_date' => '2026-12-30',
            'status' => 'Unpaid',
            'approval_status' => 'Approved',
        ]);
        Payment::create([
            'user_id' => $otherDayTenant->id,
            'is_billing_record' => true,
            'amount' => 600,
            'amount_paid' => 0,
            'month_year' => 'November 2026',
            'due_date' => '2026-11-29',
            'status' => 'Unpaid',
            'approval_status' => 'Approved',
        ]);

        $this->actingAs($admin)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('Reminder date (month, day, year)')
            ->assertSee('Send payment reminders');

        $this->actingAs($admin)
            ->post(route('payments.reminders.send'), ['reminder_date' => '2026-11-30'])
            ->assertRedirect(route('payments.index'))
            ->assertSessionHas('success', 'Payment reminders sent to 1 tenant due on November 30, 2026 by email and in-app notification.');

        Notification::assertSentTo($dueTenant, PaymentReminder::class, function (PaymentReminder $notification, array $channels) use ($duePayment, $dueTenant): bool {
            $data = $notification->toArray($dueTenant);

            $this->assertSame(['database', 'mail'], $channels);
            $this->assertTrue($notification->payment->is($duePayment));
            $this->assertSame('November 2026', $data['month_year']);
            $this->assertSame('1,250.00', $data['amount_due']);
            $this->assertSame('250.00', $data['amount_paid']);
            $this->assertSame('1,000.00', $data['balance']);
            $this->assertSame('November 30, 2026', $data['due_date']);

            return true;
        });
        Notification::assertNotSentTo($paidTenant, PaymentReminder::class);
        Notification::assertNotSentTo($pendingTenant, PaymentReminder::class);
        Notification::assertNotSentTo($otherMonthTenant, PaymentReminder::class);
        Notification::assertNotSentTo($otherDayTenant, PaymentReminder::class);
        Notification::assertNotSentTo($admin, PaymentReminder::class);
    }

    public function test_admin_reminder_date_must_be_a_valid_calendar_date(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('payments.reminders.send'), ['reminder_date' => '2026-11-31'])
            ->assertSessionHasErrors('reminder_date');

        Notification::assertNothingSent();
    }

    public function test_tenant_can_view_and_mark_only_their_own_notifications_as_read(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $otherTenant = User::factory()->create(['role' => 'tenant']);
        $tenantPayment = Payment::create([
            'user_id' => $tenant->id,
            'is_billing_record' => true,
            'amount' => 1000,
            'amount_paid' => 250,
            'month_year' => 'November 2026',
            'due_date' => '2026-11-30',
            'status' => 'Partial',
            'approval_status' => 'Approved',
        ]);
        $otherTenantPayment = Payment::create([
            'user_id' => $otherTenant->id,
            'is_billing_record' => true,
            'amount' => 1000,
            'amount_paid' => 0,
            'month_year' => 'November 2026',
            'due_date' => '2026-11-30',
            'status' => 'Unpaid',
            'approval_status' => 'Approved',
        ]);

        Notification::sendNow($tenant, new PaymentReminder($tenantPayment), ['database']);
        Notification::sendNow($otherTenant, new PaymentReminder($otherTenantPayment), ['database']);
        $tenantNotification = $tenant->notifications()->firstOrFail();
        $otherNotification = $otherTenant->notifications()->firstOrFail();

        $this->actingAs($tenant)
            ->get(route('tenant.dashboard'))
            ->assertOk()
            ->assertSee('Payment reminder: November 2026')
            ->assertSee('Total due: ₱1,000.00.')
            ->assertSee('Confirmed paid: ₱250.00.')
            ->assertSee('Outstanding balance: ₱750.00.')
            ->assertSee('Mark as read');

        $this->actingAs($tenant)
            ->post(route('tenant.notifications.read', $tenantNotification->id))
            ->assertRedirect(route('tenant.dashboard'));

        $this->assertNotNull($tenantNotification->fresh()->read_at);

        $this->actingAs($tenant)
            ->post(route('tenant.notifications.read', $otherNotification->id))
            ->assertNotFound();

        $this->assertNull($otherNotification->fresh()->read_at);
    }

    public function test_tenant_cannot_access_payment_records_through_admin_api_routes(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $payment = Payment::create([
            'user_id' => $tenant->id,
            'amount' => 1000,
            'month_year' => 'November 2026',
            'status' => 'Unpaid',
            'approval_status' => 'Pending',
            'pending_amount' => 400,
            'receipt_path' => 'payment-receipts/pending.png',
        ]);
        Sanctum::actingAs($tenant);

        $this->getJson('/api/payments')->assertForbidden();
        $this->putJson('/api/payments/'.$payment->id, [
            'amount' => 0,
            'status' => 'Paid',
        ])->assertForbidden();
    }

    public function test_public_api_registration_always_creates_a_tenant_account(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'New Tenant',
            'email' => 'new-tenant@example.test',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.role', 'tenant');
        $this->assertDatabaseHas('users', [
            'email' => 'new-tenant@example.test',
            'role' => 'tenant',
        ]);
    }

    public function test_admin_can_preview_uploaded_receipt_and_tenant_access_remains_scoped(): void
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $otherTenant = User::factory()->create(['role' => 'tenant']);
        $receiptPath = UploadedFile::fake()->image('receipt.png')->store('payment-receipts', 'local');
        $payment = Payment::create([
            'user_id' => $tenant->id,
            'amount' => 2000,
            'amount_paid' => 2000,
            'month_year' => 'November 2026',
            'status' => 'Paid',
            'receipt_path' => $receiptPath,
        ]);

        $this->actingAs($admin)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('View upload');

        $this->actingAs($admin)
            ->get(route('payments.uploaded-receipt.view', $payment))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('Content-Disposition', 'inline; filename="payment-receipt-'.$payment->id.'.png"');

        $this->actingAs($otherTenant)
            ->get(route('payments.uploaded-receipt.view', $payment))
            ->assertNotFound();

        $pdfPath = 'payment-receipts/receipt.pdf';
        Storage::disk('local')->put($pdfPath, "%PDF-1.4\n% Boarding house receipt\n");
        $pdfPayment = Payment::create([
            'user_id' => $tenant->id,
            'amount' => 1500,
            'amount_paid' => 1500,
            'month_year' => 'December 2026',
            'status' => 'Paid',
            'receipt_path' => $pdfPath,
        ]);

        $this->actingAs($admin)
            ->get(route('payments.uploaded-receipt.view', $pdfPayment))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_admin_can_record_a_partial_payment_with_an_exact_balance_on_receipt(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $payment = Payment::create([
            'user_id' => $tenant->id,
            'amount' => 1200,
            'amount_paid' => 0,
            'month_year' => 'November 2026',
            'status' => 'Unpaid',
        ]);

        $this->actingAs($admin)->put(route('payments.update', $payment), [
            'user_id' => $tenant->id,
            'amount' => 1200,
            'amount_paid' => 450.25,
            'month_year' => 'November 2026',
            'status' => 'Partial',
            'payment_method' => 'Online payment',
        ])->assertRedirect(route('payments.index'));

        $payment->refresh();
        $this->assertSame('749.75', $payment->balance);
        $this->assertSame('Partial', $payment->payment_state);

        $this->actingAs($admin)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('₱1,200.00')
            ->assertSee('₱450.25')
            ->assertSee('₱749.75');

        $this->actingAs($tenant)
            ->get(route('payments.receipt', $payment))
            ->assertOk()
            ->assertSeeText('PHP 1,200.00')
            ->assertSeeText('PHP 450.25')
            ->assertSeeText('PHP 749.75');
    }

    public function test_tenant_cannot_download_another_tenants_receipt(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);
        $otherTenant = User::factory()->create(['role' => 'tenant']);
        $payment = Payment::create([
            'user_id' => $otherTenant->id,
            'amount' => 2000,
            'amount_paid' => 2000,
            'month_year' => 'November 2026',
            'status' => 'Paid',
        ]);

        $this->actingAs($tenant)
            ->get(route('payments.receipt', $payment))
            ->assertNotFound();
    }

    public function test_admin_and_tenant_see_a_five_day_payment_reminder(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $tenant = User::factory()->create(['role' => 'tenant']);
        $dueDate = today()->addDays(5)->toDateString();

        Payment::create([
            'user_id' => $tenant->id,
            'amount' => 1000,
            'amount_paid' => 0,
            'month_year' => now()->format('F Y'),
            'due_date' => $dueDate,
            'status' => 'Unpaid',
        ]);

        $this->actingAs($admin)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('Payment reminder:')
            ->assertSee('Due in 5 days');

        $this->actingAs($tenant)
            ->get(route('tenant.dashboard'))
            ->assertOk()
            ->assertSee('Reminders')
            ->assertSee('Submit a payment')
            ->assertSee('Due in 5 days');
    }

    public function test_overdue_payment_shows_days_past_due(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        Payment::create([
            'user_id' => $tenant->id,
            'amount' => 1000,
            'amount_paid' => 0,
            'month_year' => now()->format('F Y'),
            'due_date' => today()->subDays(2)->toDateString(),
            'status' => 'Unpaid',
        ]);

        $this->actingAs($tenant)
            ->get(route('tenant.dashboard'))
            ->assertOk()
            ->assertSee('Overdue by 2 days');
    }

    public function test_tenant_dashboard_links_to_payment_submission_and_separate_history(): void
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->actingAs($tenant)
            ->get(route('tenant.dashboard'))
            ->assertOk()
            ->assertSee('Submit a payment')
            ->assertSee('View payment history')
            ->assertDontSee('Total due');

        $this->actingAs($tenant)
            ->get(route('tenant.payments.create'))
            ->assertOk()
            ->assertSee('Payment coverage');

        $this->actingAs($tenant)
            ->get(route('tenant.payments.history'))
            ->assertOk()
            ->assertSee('Payment history');
    }
}
