<?php

/**
 * MICS HUB source: app Http Controllers Admin FinanceSummaryController. See docs/file-reference.md for its full responsibility.
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FinanceSummaryService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceSummaryController extends Controller
{
    public function __invoke(Request $request, FinanceSummaryService $summary): View
    {
        $month = $request->string('month', now()->format('Y-m'))->toString();
        abort_unless(preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) === 1, 404);
        $monthDate = CarbonImmutable::createFromFormat('!Y-m', $month);

        return view('admin.finance-summary', [
            'month' => $monthDate,
            'summary' => $summary->forMonth($monthDate),
        ]);
    }
}
