<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLocaleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;

class LocaleController extends Controller
{
    public function __invoke(UpdateLocaleRequest $request): RedirectResponse
    {
        $locale = $request->validated('locale');
        $request->user()->update(['locale' => $locale]);
        App::setLocale($locale);

        return back()->with('status', __('messages.language_updated'));
    }
}
