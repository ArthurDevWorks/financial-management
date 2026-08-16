<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreditCardStoreRequest;
use App\Http\Requests\CreditCardUpdateRequest;
use App\Models\Bank;
use App\Models\CreditCard;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CreditCardController extends Controller
{
    public function index()
    {
        $cards = CreditCard::with('bank')
            ->where('user_id', Auth::id())
            ->get()
            ->map(function (CreditCard $card) {
                $mv = $card->currentInvoiceMonthYear();
                $period = $card->invoicePeriod($mv['month'], $mv['year']);

                return [
                    'id' => $card->id,
                    'name' => $card->name,
                    'color' => $card->color,
                    'limit' => $card->limit,
                    'closing_day' => $card->closing_day,
                    'due_day' => $card->due_day,
                    'bank' => $card->bank ? ['name' => $card->bank->name] : null,
                    'current_invoice' => [
                        'total' => $card->invoiceTotal($mv['month'], $mv['year']),
                        'month' => $mv['month'],
                        'year' => $mv['year'],
                        'period' => $period,
                    ],
                ];
            });

        return Inertia::render('credit-cards/Index', [
            'cards' => $cards,
        ]);
    }

    public function create()
    {
        return Inertia::render('credit-cards/Create', [
            'banks' => Bank::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(CreditCardStoreRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['color'] = $data['color'] ?? '#22c9a2';

        CreditCard::create($data);

        return redirect()
            ->route('credit-cards.index')
            ->with('success', 'Cartão cadastrado com sucesso.');
    }

    public function edit(CreditCard $creditCard)
    {
        if ($creditCard->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('credit-cards/Edit', [
            'card' => $creditCard->load('bank'),
            'banks' => Bank::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(CreditCardUpdateRequest $request, CreditCard $creditCard)
    {
        if ($creditCard->user_id !== Auth::id()) {
            abort(403);
        }

        $creditCard->update($request->validated());

        return redirect()
            ->route('credit-cards.index')
            ->with('success', 'Cartão atualizado com sucesso.');
    }

    public function destroy(CreditCard $creditCard)
    {
        if ($creditCard->user_id !== Auth::id()) {
            abort(403);
        }

        $creditCard->delete();

        return redirect()
            ->route('credit-cards.index')
            ->with('success', 'Cartão removido com sucesso.');
    }
}
