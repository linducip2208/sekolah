<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\Finance\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct(private CurrencyService $currency) {}

    public function show()
    {
        $school = School::findOrFail(auth()->user()->school_id);
        $sample = 12_345_678;
        $preview = $this->currency->format($sample, $school);
        return view('school-admin.currency.show', [
            'school'  => $school,
            'presets' => CurrencyService::PRESETS,
            'preview' => $preview,
            'sample'  => $sample,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'preset'                  => 'nullable|string|max:3',
            'currency_code'           => 'required|string|max:3',
            'currency_symbol'         => 'required|string|max:8',
            'currency_decimals'       => 'required|integer|min:0|max:6',
            'currency_thousands_sep'  => 'nullable|string|max:2',
            'currency_decimal_sep'    => 'required|string|max:2',
        ]);

        $school = School::findOrFail(auth()->user()->school_id);

        if (!empty($data['preset']) && isset(CurrencyService::PRESETS[strtoupper($data['preset'])])) {
            $this->currency->applyPreset($school, $data['preset']);
            return back()->with('success', 'Currency preset diterapkan.');
        }

        $school->fill([
            'currency_code'          => strtoupper($data['currency_code']),
            'currency_symbol'        => $data['currency_symbol'],
            'currency_decimals'      => $data['currency_decimals'],
            'currency_thousands_sep' => $data['currency_thousands_sep'] ?? '',
            'currency_decimal_sep'   => $data['currency_decimal_sep'],
        ])->save();

        return back()->with('success', 'Currency setting tersimpan.');
    }
}
