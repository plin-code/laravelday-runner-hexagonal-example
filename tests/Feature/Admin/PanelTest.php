<?php

use App\Domain\Runners\Models\Runner;
use App\Domain\Users\Models\User;
use App\Infrastructure\Filament\Resources\Runners\Pages\EditRunner;
use App\Infrastructure\Filament\Resources\Runners\Pages\ListRunners;
use Database\Seeders\RunnerPreferenceSeeder;
use Livewire\Livewire;

test('guests are redirected to the panel login', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

test('users who are not administrators are forbidden from the panel', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertForbidden();
});

test('administrators can open the panel and its resources', function (string $path) {
    $this->actingAs(User::factory()->admin()->create())
        ->get($path)
        ->assertOk();
})->with(['/admin', '/admin/contests', '/admin/contests/create', '/admin/runners']);

test('runners table is searched by full name in either order', function () {
    $this->actingAs(User::factory()->admin()->create());
    $mario = Runner::factory()->create(['first_name' => 'Mario', 'last_name' => 'Rossi']);
    $anna = Runner::factory()->create(['first_name' => 'Anna', 'last_name' => 'Bianchi']);

    Livewire::test(ListRunners::class)
        ->searchTable('rossi mario')
        ->assertCanSeeTableRecords([$mario])
        ->assertCanNotSeeTableRecords([$anna]);
});

test('runners table is sorted by full name', function () {
    $this->actingAs(User::factory()->admin()->create());
    $mario = Runner::factory()->create(['first_name' => 'Mario', 'last_name' => 'Rossi']);
    $anna = Runner::factory()->create(['first_name' => 'Anna', 'last_name' => 'Bianchi']);

    Livewire::test(ListRunners::class)
        ->sortTable('full_name')
        ->assertCanSeeTableRecords([$anna, $mario], inOrder: true)
        ->sortTable('full_name', 'desc')
        ->assertCanSeeTableRecords([$mario, $anna], inOrder: true);
});

test('editing a runner shows and saves its custom fields', function () {
    $this->seed(RunnerPreferenceSeeder::class);
    $this->actingAs(User::factory()->admin()->create());
    $runner = Runner::factory()->create(['first_name' => 'Mario']);
    $runner->setCustomFields(['taglia-maglietta' => 'm']);

    Livewire::test(EditRunner::class, ['record' => $runner->id])
        ->assertSchemaStateSet([
            'first_name' => 'Mario',
            'custom_fields.taglia-maglietta' => 'm',
        ])
        ->fillForm([
            'first_name' => 'Marco',
            'custom_fields.taglia-maglietta' => 'xl',
            'custom_fields.newsletter' => true,
            'custom_fields.contatto-di-emergenza' => '+39 333 1234567',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $runner->refresh();

    expect($runner->first_name)->toBe('Marco')
        ->and($runner->getCustomField('taglia-maglietta'))->toBe('xl')
        ->and($runner->getCustomField('newsletter'))->toBeTrue()
        ->and($runner->getCustomField('contatto-di-emergenza'))->toBe('+39 333 1234567');
});
