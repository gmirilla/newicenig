<?php

use App\Models\MemberFile;
use App\Models\MembershipTier;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Models\UserMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/**
 * Builds the old app's tables on the `legacy` connection (sqlite :memory:,
 * see phpunit.xml) — this app never migrates that schema for real (it's a
 * restored copy of an external database), so tests construct just enough of
 * it to exercise the importers.
 */
function seedLegacySchema(): void
{
    $db = Schema::connection('legacy');

    foreach (['files', 'payments', 'i_c_e_n_membership_registrations', 'i_c_e_n_membership_form_fields', 'h_t_m_l_form_fields', 'i_c_e_n_user_memberships', 'i_c_e_n_memberships', 'model_has_roles', 'roles', 'users'] as $table) {
        $db->dropIfExists($table);
    }

    $db->create('users', function ($table) {
        $table->id();
        $table->string('email')->nullable();
        $table->string('first_name')->nullable();
        $table->string('middle_name')->nullable();
        $table->string('last_name')->nullable();
        $table->string('telephone')->nullable();
        $table->string('password')->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->timestamps();
    });

    $db->create('i_c_e_n_memberships', function ($table) {
        $table->id();
        $table->string('name')->nullable();
        $table->string('abbreviation')->nullable();
        $table->text('description')->nullable();
        $table->decimal('fee', 12, 2)->nullable();
        $table->decimal('renewal_fee', 12, 2)->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    $db->create('i_c_e_n_user_memberships', function ($table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->unsignedBigInteger('i_c_e_n_membership_id')->nullable();
        $table->string('i_c_e_n_number')->nullable();
        $table->string('status')->nullable();
        $table->timestamp('verified_at')->nullable();
        $table->timestamp('expired_at')->nullable();
        $table->timestamp('upgraded_at')->nullable();
        $table->timestamps();
    });

    $db->create('h_t_m_l_form_fields', function ($table) {
        $table->id();
        $table->string('name')->nullable();
        $table->timestamps();
    });

    $db->create('i_c_e_n_membership_form_fields', function ($table) {
        $table->id();
        $table->unsignedBigInteger('h_t_m_l_form_field_id')->nullable();
        $table->unsignedBigInteger('i_c_e_n_membership_id')->nullable();
        $table->timestamps();
    });

    $db->create('i_c_e_n_membership_registrations', function ($table) {
        $table->id();
        $table->unsignedBigInteger('i_c_e_n_user_membership_id')->nullable();
        $table->unsignedBigInteger('i_c_e_n_membership_form_field_id')->nullable();
        $table->text('value')->nullable();
        $table->timestamps();
    });

    $db->create('payments', function ($table) {
        $table->id();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->unsignedBigInteger('paymentable_id')->nullable();
        $table->string('paymentable_type')->nullable();
        $table->string('transaction_reference')->nullable();
        $table->decimal('amount', 12, 2)->nullable();
        $table->string('currency')->nullable();
        $table->string('transaction_status')->nullable();
        $table->string('method')->nullable();
        $table->timestamp('paid_at')->nullable();
        $table->text('metadata')->nullable();
        $table->timestamps();
    });

    $db->create('files', function ($table) {
        $table->id();
        $table->unsignedBigInteger('fileable_id')->nullable();
        $table->string('fileable_type')->nullable();
        $table->string('name')->nullable();
        $table->string('description')->nullable();
        $table->string('path')->nullable();
        $table->timestamps();
    });

    $db->create('roles', function ($table) {
        $table->id();
        $table->string('name')->nullable();
    });

    $db->create('model_has_roles', function ($table) {
        $table->unsignedBigInteger('role_id');
        $table->string('model_type');
        $table->unsignedBigInteger('model_id');
    });
}

/**
 * Seeds one legacy user + tier + membership (with an EAV first_name field) so
 * a --force run has something real to import end to end.
 */
function seedOneLegacyMember(): void
{
    \Illuminate\Support\Facades\DB::connection('legacy')->table('users')->insert([
        'id' => 1,
        'email' => 'legacy-member@example.test',
        'first_name' => 'Legacy',
        'last_name' => 'Member',
        'password' => 'not-a-real-hash',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    \Illuminate\Support\Facades\DB::connection('legacy')->table('i_c_e_n_memberships')->insert([
        'id' => 1,
        'name' => 'Associate Member',
        'fee' => 15000,
        'renewal_fee' => 10000,
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    \Illuminate\Support\Facades\DB::connection('legacy')->table('i_c_e_n_user_memberships')->insert([
        'id' => 1,
        'user_id' => 1,
        'i_c_e_n_membership_id' => 1,
        'i_c_e_n_number' => 'ICEN/2020/00001',
        'status' => 'approved',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    \Illuminate\Support\Facades\DB::connection('legacy')->table('h_t_m_l_form_fields')->insert([
        'id' => 1,
        'name' => 'first_name',
    ]);

    \Illuminate\Support\Facades\DB::connection('legacy')->table('i_c_e_n_membership_form_fields')->insert([
        'id' => 1,
        'h_t_m_l_form_field_id' => 1,
        'i_c_e_n_membership_id' => 1,
    ]);

    \Illuminate\Support\Facades\DB::connection('legacy')->table('i_c_e_n_membership_registrations')->insert([
        'id' => 1,
        'i_c_e_n_user_membership_id' => 1,
        'i_c_e_n_membership_form_field_id' => 1,
        'value' => 'Legacy',
    ]);

    \Illuminate\Support\Facades\DB::connection('legacy')->table('payments')->insert([
        'id' => 1,
        'user_id' => 1,
        'paymentable_id' => 1,
        'paymentable_type' => 'App\\Models\\IcenUserMembership',
        'transaction_reference' => 'LEGACY-REF-001',
        'amount' => 15000,
        'currency' => 'NGN',
        'transaction_status' => 'successful',
        'method' => 'card',
        'paid_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    \Illuminate\Support\Facades\DB::connection('legacy')->table('files')->insert([
        'id' => 1,
        'fileable_id' => 1,
        'fileable_type' => 'App\\Models\\IcenUserMembership',
        'name' => 'passport photo',
        'description' => null,
        'path' => 'member-1/passport.jpg',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

beforeEach(function () {
    seedLegacySchema();
});

it('dry runs by default and writes nothing', function () {
    seedOneLegacyMember();

    $this->artisan('legacy:migrate')->assertSuccessful();

    expect(User::count())->toBe(0)
        ->and(MembershipTier::count())->toBe(0)
        ->and(UserMembership::count())->toBe(0)
        ->and(Payment::count())->toBe(0)
        ->and(MemberFile::count())->toBe(0);
});

it('rejects an invalid --only value', function () {
    $this->artisan('legacy:migrate --only=not-a-real-step')->assertFailed();
});

it('imports users, tiers, memberships, and payments end to end with --force', function () {
    seedOneLegacyMember();
    PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $this->artisan('legacy:migrate --force')->assertSuccessful();

    $user = User::where('email', 'legacy-member@example.test')->first();
    expect($user)->not->toBeNull();

    $tier = MembershipTier::where('name', 'Associate Member')->first();
    expect($tier)->not->toBeNull()
        ->and((float) $tier->registration_fee)->toBe(15000.0);

    $membership = UserMembership::where('membership_number', 'ICEN/2020/00001')->first();
    expect($membership)->not->toBeNull()
        ->and($membership->user_id)->toBe($user->id)
        ->and($membership->membership_tier_id)->toBe($tier->id)
        ->and($membership->first_name)->toBe('Legacy');

    $payment = Payment::where('reference', 'LEGACY-REF-001')->first();
    expect($payment)->not->toBeNull()
        ->and($payment->payable_id)->toBe($membership->id)
        ->and($payment->payable_type)->toBe(UserMembership::class);
});

it('creates the paystack gateway on demand instead of leaving payment_gateway_id null', function () {
    seedOneLegacyMember();

    expect(PaymentGateway::where('slug', 'paystack')->exists())->toBeFalse();

    $this->artisan('legacy:migrate --force')->assertSuccessful();

    $gateway = PaymentGateway::where('slug', 'paystack')->first();
    expect($gateway)->not->toBeNull();

    $payment = Payment::where('reference', 'LEGACY-REF-001')->first();
    expect($payment)->not->toBeNull()
        ->and($payment->payment_gateway_id)->toBe($gateway->id);
});

it('does not duplicate member files when the import is run twice', function () {
    seedOneLegacyMember();
    PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    // No LEGACY_STORAGE_PATH is configured in tests, so the files step reports
    // "skipped" rather than attempting a real file copy — this test only needs
    // to prove a second run can't create a second MemberFile row for the same
    // legacy file once one exists, which we simulate directly.
    $membership = null;

    $this->artisan('legacy:migrate --force')->assertSuccessful();
    $membership = UserMembership::where('membership_number', 'ICEN/2020/00001')->first();

    MemberFile::create([
        'user_membership_id' => $membership->id,
        'type' => 'passport_photo',
        'legacy_file_id' => 1,
    ]);

    expect(MemberFile::where('legacy_file_id', 1)->count())->toBe(1);

    // Running the whole import again must not create a second row for the
    // same legacy_file_id (the bug this test guards against).
    $this->artisan('legacy:migrate --force')->assertSuccessful();

    expect(MemberFile::where('legacy_file_id', 1)->count())->toBe(1);
});

it('does not duplicate users, tiers, memberships, or payments when the import is run twice', function () {
    seedOneLegacyMember();
    PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $this->artisan('legacy:migrate --force')->assertSuccessful();
    $this->artisan('legacy:migrate --force')->assertSuccessful();

    expect(User::where('email', 'legacy-member@example.test')->count())->toBe(1)
        ->and(MembershipTier::where('name', 'Associate Member')->count())->toBe(1)
        ->and(UserMembership::where('membership_number', 'ICEN/2020/00001')->count())->toBe(1)
        ->and(Payment::where('reference', 'LEGACY-REF-001')->count())->toBe(1);
});

it('requires an interactive confirmation before writing in production, and aborts if declined', function () {
    seedOneLegacyMember();
    $this->app['env'] = 'production';

    $this->artisan('legacy:migrate --force')
        ->expectsConfirmation('This will write real data and cannot be safely undone. Continue?', 'no')
        ->assertFailed();

    expect(User::count())->toBe(0);
});

it('proceeds in production once the operator explicitly confirms', function () {
    seedOneLegacyMember();
    PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);
    $this->app['env'] = 'production';

    $this->artisan('legacy:migrate --force')
        ->expectsConfirmation('This will write real data and cannot be safely undone. Continue?', 'yes')
        ->assertSuccessful();

    expect(User::where('email', 'legacy-member@example.test')->count())->toBe(1);
});

it('does not prompt for confirmation on a dry run in production', function () {
    seedOneLegacyMember();
    $this->app['env'] = 'production';

    // No expectsConfirmation() call — the command must not ask, since a dry
    // run writes nothing regardless of environment.
    $this->artisan('legacy:migrate')->assertSuccessful();

    expect(User::count())->toBe(0);
});

it('maps email, telephone, and declaration_date onto real UserMembership columns instead of leaving them unmapped', function () {
    seedOneLegacyMember();
    PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    $db = \Illuminate\Support\Facades\DB::connection('legacy');
    $db->table('h_t_m_l_form_fields')->insert([
        ['id' => 2, 'name' => 'email'],
        ['id' => 3, 'name' => 'telephone'],
        ['id' => 4, 'name' => 'declaration_date'],
    ]);
    $db->table('i_c_e_n_membership_form_fields')->insert([
        ['id' => 2, 'h_t_m_l_form_field_id' => 2, 'i_c_e_n_membership_id' => 1],
        ['id' => 3, 'h_t_m_l_form_field_id' => 3, 'i_c_e_n_membership_id' => 1],
        ['id' => 4, 'h_t_m_l_form_field_id' => 4, 'i_c_e_n_membership_id' => 1],
    ]);
    $db->table('i_c_e_n_membership_registrations')->insert([
        ['id' => 2, 'i_c_e_n_user_membership_id' => 1, 'i_c_e_n_membership_form_field_id' => 2, 'value' => 'applicant@example.test'],
        ['id' => 3, 'i_c_e_n_user_membership_id' => 1, 'i_c_e_n_membership_form_field_id' => 3, 'value' => '08099999999'],
        ['id' => 4, 'i_c_e_n_user_membership_id' => 1, 'i_c_e_n_membership_form_field_id' => 4, 'value' => '2024-06-19'],
    ]);

    $this->artisan('legacy:migrate --force')->assertSuccessful();

    $membership = UserMembership::where('membership_number', 'ICEN/2020/00001')->first();

    expect($membership->email)->toBe('applicant@example.test')
        ->and($membership->phone)->toBe('08099999999')
        ->and($membership->declaration_accepted_at?->toDateString())->toBe('2024-06-19')
        ->and($membership->extra_fields)->toBeNull();
});

it('attaches a user-typed legacy file to that user\'s earliest membership, and copies it', function () {
    seedOneLegacyMember();
    PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    // A second, later membership for the same legacy user — the file must
    // land on the first one (id 1), not this one.
    \Illuminate\Support\Facades\DB::connection('legacy')->table('i_c_e_n_user_memberships')->insert([
        'id' => 2,
        'user_id' => 1,
        'i_c_e_n_membership_id' => 1,
        'i_c_e_n_number' => 'ICEN/2022/00001',
        'status' => 'approved',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $storagePath = sys_get_temp_dir().'/legacy-storage-test-'.uniqid();
    mkdir($storagePath.'/member-1', recursive: true);
    file_put_contents($storagePath.'/member-1/degree.pdf', '%PDF-1.4 fake certificate');
    file_put_contents($storagePath.'/member-1/passport.jpg', 'fake image bytes');
    config(['legacy.storage_path' => $storagePath]);

    \Illuminate\Support\Facades\DB::connection('legacy')->table('files')->insert([
        'id' => 2,
        'fileable_id' => 1,
        'fileable_type' => 'App\\Models\\User',
        'name' => 'Degree Certificate',
        'description' => null,
        'path' => 'member-1/degree.pdf',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('legacy:migrate --force')->assertSuccessful();

    $earliestMembership = UserMembership::where('membership_number', 'ICEN/2020/00001')->first();
    $latestMembership = UserMembership::where('membership_number', 'ICEN/2022/00001')->first();

    $memberFile = MemberFile::where('legacy_file_id', 2)->first();
    expect($memberFile)->not->toBeNull()
        ->and($memberFile->user_membership_id)->toBe($earliestMembership->id)
        ->and($memberFile->user_membership_id)->not->toBe($latestMembership->id)
        ->and($memberFile->type)->toBe('higher_institution_certificate')
        ->and($memberFile->fileUrl())->not->toBeNull();

    // The original passport-photo fixture (fileable_type containing
    // "Membership") must still resolve the old way, unaffected.
    $passport = MemberFile::where('legacy_file_id', 1)->first();
    expect($passport)->not->toBeNull()
        ->and($passport->user_membership_id)->toBe($earliestMembership->id);
});

it('still flags a legacy file whose fileable type is neither a membership nor a user', function () {
    seedOneLegacyMember();
    PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    \Illuminate\Support\Facades\DB::connection('legacy')->table('files')->insert([
        'id' => 3,
        'fileable_id' => 1,
        'fileable_type' => 'App\\Models\\PaymentGateway',
        'name' => 'gateway-logo',
        'description' => null,
        'path' => 'gateways/logo.png',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('legacy:migrate --force')->assertSuccessful();

    expect(MemberFile::where('legacy_file_id', 3)->exists())->toBeFalse();
});

it('prints why records were skipped, not just a count', function () {
    seedOneLegacyMember();
    PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    // A membership with no linked user at all — mirrors the real production
    // data pattern this test suite was built to catch (orphaned applications).
    \Illuminate\Support\Facades\DB::connection('legacy')->table('i_c_e_n_user_memberships')->insert([
        'id' => 2,
        'user_id' => null,
        'i_c_e_n_membership_id' => 1,
        'i_c_e_n_number' => 'ICEN/2021/00099',
        'status' => 'pending',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('legacy:migrate --force')
        ->assertSuccessful()
        ->expectsOutputToContain('Skipped — memberships:')
        ->expectsOutputToContain('was not imported');

    expect(UserMembership::where('membership_number', 'ICEN/2021/00099')->exists())->toBeFalse();
});

it('imports two distinct legacy memberships that share the same membership number without crashing or losing either', function () {
    seedOneLegacyMember();
    PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    // A second, genuinely different legacy user + membership that happens to
    // carry the SAME i_c_e_n_number as the one from seedOneLegacyMember() —
    // this really happens in production data (old app didn't enforce
    // uniqueness), and membership_number IS unique on the new schema.
    \Illuminate\Support\Facades\DB::connection('legacy')->table('users')->insert([
        'id' => 2,
        'email' => 'second-legacy-member@example.test',
        'first_name' => 'Second',
        'last_name' => 'Member',
        'password' => 'not-a-real-hash',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    \Illuminate\Support\Facades\DB::connection('legacy')->table('i_c_e_n_user_memberships')->insert([
        'id' => 2,
        'user_id' => 2,
        'i_c_e_n_membership_id' => 1,
        'i_c_e_n_number' => 'ICEN/2020/00001', // duplicate of seedOneLegacyMember()'s number
        'status' => 'approved',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->artisan('legacy:migrate --force')
        ->assertSuccessful()
        ->expectsOutputToContain('is already used by another legacy membership');

    // Both rows exist — nothing was overwritten or dropped.
    expect(UserMembership::count())->toBe(2);

    $first = UserMembership::whereHas('user', fn ($q) => $q->where('email', 'legacy-member@example.test'))->first();
    $second = UserMembership::whereHas('user', fn ($q) => $q->where('email', 'second-legacy-member@example.test'))->first();

    expect($first)->not->toBeNull()
        ->and($second)->not->toBeNull()
        ->and($first->membership_number)->toBe('ICEN/2020/00001')
        ->and($second->membership_number)->toBeNull();
});

it('does not duplicate memberships on a second run even when membership_number is empty or ambiguous', function () {
    seedOneLegacyMember();
    PaymentGateway::create(['name' => 'Paystack', 'slug' => 'paystack', 'is_active' => true]);

    \Illuminate\Support\Facades\DB::connection('legacy')->table('i_c_e_n_user_memberships')->update(['i_c_e_n_number' => null]);

    $this->artisan('legacy:migrate --force')->assertSuccessful();
    $this->artisan('legacy:migrate --force')->assertSuccessful();

    expect(UserMembership::count())->toBe(1);
});
