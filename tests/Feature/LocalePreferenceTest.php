<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use App\Support\LocalizedFormat;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocalePreferenceTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_english_is_the_default_user_locale(): void
    {
        $user = User::factory()->create();

        $this->assertSame('en', $user->locale);
    }

    public function test_authenticated_user_can_store_a_supported_locale(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('teacher.dashboard'))
            ->post(route('locale.update'), ['locale' => 'uk'])
            ->assertRedirect(route('teacher.dashboard'))
            ->assertSessionHas('status', 'Мову змінено.');

        $this->assertSame('uk', $user->refresh()->locale);
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('locale.update'), ['locale' => 'de'])
            ->assertSessionHasErrors('locale');

        $this->assertSame('en', $user->refresh()->locale);
    }

    public function test_saved_locale_is_applied_to_rendered_pages(): void
    {
        $user = User::factory()->teacher()->create(['locale' => 'uk']);

        $this->actingAs($user)
            ->get(route('teacher.dashboard'))
            ->assertOk()
            ->assertSee('Панель викладача')
            ->assertSee('Мова');
    }

    public function test_dates_and_numbers_are_presented_by_locale_in_kyiv_timezone(): void
    {
        config(['app.timezone' => 'Europe/Kyiv']);
        $date = CarbonImmutable::parse('2026-07-07 21:30:00', 'UTC');

        App::setLocale('en');
        $this->assertSame("July 8, 2026 at 12:30\u{202F}AM", LocalizedFormat::dateTime($date));
        $this->assertSame('1,234.50', LocalizedFormat::number(1234.5));

        App::setLocale('uk');
        $this->assertSame("8 липня 2026\u{202F}р. о 00:30", LocalizedFormat::dateTime($date));
        $this->assertSame('1 234,50', LocalizedFormat::number(1234.5));
    }

    public function test_all_teacher_workflow_pages_render_in_ukrainian(): void
    {
        $teacher = User::factory()->teacher()->create(['locale' => 'uk']);
        $student = Student::factory()->create(['staff_id' => $teacher->staff_id]);

        $pages = [
            [route('teacher.dashboard'), 'Вітаємо'],
            [route('teacher.students.index'), 'Призначені учні'],
            [route('teacher.students.create'), 'Новий учень автоматично буде призначений'],
            [route('teacher.students.edit', $student), 'Ви можете оновити дані призначеного учня'],
            [route('teacher.lesson-counts.index'), 'Розрахунковий місяць'],
        ];

        foreach ($pages as [$url, $expectedText]) {
            $this->actingAs($teacher)
                ->get($url)
                ->assertOk()
                ->assertSeeText($expectedText);
        }
    }

    public function test_teacher_student_validation_is_rendered_in_ukrainian(): void
    {
        $teacher = User::factory()->teacher()->create(['locale' => 'uk']);

        $this->actingAs($teacher)
            ->followingRedirects()
            ->from(route('teacher.students.create'))
            ->post(route('teacher.students.store'), [])
            ->assertOk()
            ->assertSeeText('Поле «ім’я» обов’язкове.')
            ->assertSeeText('Поле «дата приєднання» обов’язкове.');
    }

    public function test_core_admin_finance_pages_render_in_ukrainian(): void
    {
        $admin = User::factory()->admin()->create(['locale' => 'uk']);

        $this->actingAs($admin)->get(route('admin.finance-summary', ['month' => '2026-07']))
            ->assertOk()->assertSee('Місячний фінансовий звіт');
        $this->actingAs($admin)->get(route('admin.month-closing.index', ['month' => '2026-07']))
            ->assertOk()->assertSee('Формування місячних чернеток');
        $this->actingAs($admin)->get(route('admin.bank-months.index', ['month' => '2026-07']))
            ->assertOk()->assertSee('Фактичний кінцевий баланс');
        $this->actingAs($admin)->get(route('admin.student-charges.index', ['month' => '2026-07']))
            ->assertOk()->assertSee('Перевірка нарахувань учням');
        $this->actingAs($admin)->get(route('admin.payments.index', ['month' => '2026-07']))
            ->assertOk()->assertSee('Записати оплату');
        $this->actingAs($admin)->get(route('admin.expenses.index', ['month' => '2026-07']))
            ->assertOk()->assertSee('Витрати та чернетки зарплат');
    }
}
