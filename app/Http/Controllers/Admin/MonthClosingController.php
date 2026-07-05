<?php

/**
 * MICS source: app Http Controllers Admin MonthClosingController. See docs/file-reference.md for its full responsibility.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CloseBillingMonthRequest;
use App\Http\Requests\Admin\ReopenBillingMonthRequest;
use App\Models\BillingMonth;
use App\Services\MonthClosingService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonthClosingController extends Controller
{
    public function index(Request $request, MonthClosingService $closing): View
    {
        $month = $this->selectedMonth($request);

        return view('admin.month-closing.index', [
            'month' => $month,
            'billingMonth' => BillingMonth::query()->whereDate('month_date', $month)->with(['closedBy', 'events.user'])->first(),
            'preview' => $closing->preview($month),
        ]);
    }

    public function reopen(ReopenBillingMonthRequest $request, MonthClosingService $closing): RedirectResponse
    {
        $closing->reopen(
            CarbonImmutable::createFromFormat('!Y-m', $request->string('month')->toString()),
            $request->user(),
            $request->string('reason')->toString(),
        );

        return to_route('admin.month-closing.index', ['month' => $request->string('month')->toString()])
            ->with('status', 'Month reopened. Validated financial records remain unchanged.');
    }

    public function store(CloseBillingMonthRequest $request, MonthClosingService $closing): RedirectResponse
    {
        $closing->close($request->monthDate(), $request->user());

        return to_route('admin.month-closing.index', ['month' => $request->string('month')->toString()])
            ->with('status', 'Month closed and drafts generated successfully.');
    }

    private function selectedMonth(Request $request): CarbonImmutable
    {
        $month = $request->string('month', now()->format('Y-m'))->toString();
        abort_unless(preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) === 1, 404);

        return CarbonImmutable::createFromFormat('!Y-m', $month);
    }
}
