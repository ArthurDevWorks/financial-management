<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Enums\RecurrenceFrequency;
use App\Http\Requests\ReleaseStoreRequest;
use App\Http\Requests\ReleaseUpdateRequest;
use App\Models\Account;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\RecurrencePlan;
use App\Models\Release;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class ReleaseController extends Controller
{
    public function index(Request $request)
    {
        $releases = Release::with(['account.bank', 'category', 'parent'])
            ->where('user_id', Auth::id())
            ->when($request->search, fn ($q, $search) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('account', fn ($q) => $q->where('account', 'like', "%{$search}%"));
            }))
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('releases/Index', [
            'releases' => $releases,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('releases/Create', [
            'accounts'              => Account::with('bank')->where('user_id', Auth::id())->get(),
            'categories'            => Category::where('type', 'receita')->orWhere('type', 'despesa')->get(),
            'paymentMethods'        => \App\Enums\PaymentMethod::options(),
            'recurrenceFrequencies' => \App\Enums\RecurrenceFrequency::options(),
            'releaseStatuses'       => \App\Enums\ReleaseStatus::options(),
            'creditCards'           => CreditCard::where('user_id', Auth::id())->orderBy('name')->get(['id', 'name', 'color', 'limit']),
        ]);
    }

    public function store(ReleaseStoreRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        $isInstallment = !empty($validated['is_installment']);
        $isRecurring = !empty($validated['is_recurring']);

        unset($validated['is_installment'], $validated['is_recurring']);
        unset($validated['recurrence_frequency'], $validated['recurrence_end_date']);

        if ($isInstallment && $validated['payment_method'] === 'credit_card') {
            $totalInstallments = (int) ($request->input('total_installments', 1));
            $installmentAmount = round($validated['amount'] / $totalInstallments, 2);
            $remainder = round($validated['amount'] - ($installmentAmount * $totalInstallments), 2);

            $parent = null;

            for ($i = 1; $i <= $totalInstallments; $i++) {
                $data = $validated;
                $data['installment_number'] = $i;
                $data['total_installments'] = $totalInstallments;
                $data['status'] = $i === 1 ? 'paid' : 'pending';

                $data['date'] = \Carbon\Carbon::parse($validated['date'])
                    ->addMonthsNoOverflow($i - 1)
                    ->format('Y-m-d');

                $amount = $installmentAmount;
                if ($i === $totalInstallments) {
                    $amount += $remainder;
                }
                $data['amount'] = $amount;

                $release = Release::create($data);

                if ($i === 1) {
                    $parent = $release;
                } else {
                    $release->update(['parent_id' => $parent->id]);
                }
            }

            return redirect()->route('releases.index')->with('success', 'Parcelamento criado com sucesso.');
        }

        if ($isRecurring) {
            $frequency = RecurrenceFrequency::from($request->input('recurrence_frequency'));
            $endDate = $request->input('recurrence_end_date');
            $startDate = $validated['date'];

            $recurrencePlan = RecurrencePlan::create([
                'user_id' => Auth::id(),
                'account_id' => $validated['account_id'],
                'credit_card_id' => $validated['credit_card_id'] ?? null,
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'amount' => $validated['amount'],
                'type' => $validated['type'],
                'payment_method' => $validated['payment_method'] ?? null,
                'frequency' => $frequency->value,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'next_generation' => $frequency->addToDate(\Carbon\Carbon::parse($startDate))->format('Y-m-d'),
                'active' => true,
            ]);

            $validated['status'] ??= 'paid';
            $validated['recurrence_id'] = $recurrencePlan->id;
            Release::create($validated);

            return redirect()->route('releases.index')->with('success', 'Lançamento recorrente criado com sucesso.');
        }

        $validated['status'] ??= 'paid';
        Release::create($validated);

        return redirect()->route('releases.index')->with('success', 'Lançamento criado com sucesso.');
    }

    public function edit(Release $release)
    {
        if ($release->user_id !== Auth::id()) {
            abort(403);
        }

        return Inertia::render('releases/Edit', [
            'release'               => $release->load('parent', 'recurrencePlan', 'creditCard'),
            'accounts'              => Account::with('bank')->where('user_id', Auth::id())->get(),
            'categories'            => Category::all(),
            'paymentMethods'        => \App\Enums\PaymentMethod::options(),
            'recurrenceFrequencies' => \App\Enums\RecurrenceFrequency::options(),
            'releaseStatuses'       => \App\Enums\ReleaseStatus::options(),
            'creditCards'           => CreditCard::where('user_id', Auth::id())->orderBy('name')->get(['id', 'name', 'color', 'limit']),
        ]);
    }

    public function update(ReleaseUpdateRequest $request, Release $release)
    {
        if ($release->user_id !== Auth::id()) {
            abort(403);
        }

        $release->update($request->validated());

        return redirect()->route('releases.index')->with('success', 'Lançamento atualizado com sucesso.');
    }

    public function export()
    {
        $releases = Release::with(['account.bank', 'category', 'parent'])
            ->where('user_id', Auth::id())
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $callback = function () use ($releases) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['ID', 'Título', 'Descrição', 'Categoria', 'Conta', 'Data', 'Valor', 'Tipo', 'Forma de Pagamento', 'Status', 'Parcela']);

            foreach ($releases as $release) {
                fputcsv($handle, [
                    $release->id,
                    $release->title,
                    $release->description ?? '',
                    $release->category?->name ?? 'Sem Categoria',
                    $release->account?->account ?? '-',
                    $release->date?->format('d/m/Y'),
                    number_format($release->amount, 2, ',', '.'),
                    $release->type === 'revenue' ? 'Receita' : 'Despesa',
                    $release->payment_method?->label() ?? '-',
                    $release->status?->label() ?? 'Pago',
                    $release->installment_number ? "{$release->installment_number}/{$release->total_installments}" : '-',
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="lancamentos.csv"',
        ]);
    }

    public function destroy(Release $release)
    {
        if ($release->user_id !== Auth::id()) {
            abort(403);
        }

        $release->delete();

        return redirect()->route('releases.index')->with('success', 'Lançamento excluído com sucesso.');
    }
}
