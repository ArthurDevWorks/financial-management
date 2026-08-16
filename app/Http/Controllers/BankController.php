<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankStoreRequest;
use App\Http\Requests\BankUpdateRequest;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class BankController extends Controller
{
    /**
     * List of banks has created
     */
    public function index(Request $request)
    {
        $banks = Bank::query()
            ->withCount('accounts')
            ->when($request->search, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->paginate(10);

        $banks->getCollection()->transform(function ($bank) {
            $bank->logo_url = $bank->logo
                ? asset('storage/'.$bank->logo)
                : null;

            return $bank;
        });

        return Inertia::render('banks/Index', [
            'banks' => $banks,
        ]);
    }

    /**
     * Show form to create banks
     */
    public function create()
    {
        return Inertia::render('banks/Create');
    }

    /**
     * Save banks in database
     */
    public function store(BankStoreRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('banks', 'public');
        } else {
            $data['logo'] = '';
        }

        $save = Bank::create([
            'name' => $data['name'],
            'logo' => $data['logo'],
        ]);

        if (! $save) {
            return redirect()->route('banks.index')
                ->with('error', 'Erro ao cadastrar banco');
        }

        return redirect()->route('banks.index')
            ->with('success', 'Banco cadastrado com sucesso');
    }

    /**
     * Show form to update data
     */
    public function edit(Bank $bank)
    {
        $bank->logo_url = $bank->logo
            ? asset('storage/'.$bank->logo)
            : null;

        return Inertia::render('banks/Edit', [
            'bank' => $bank,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BankUpdateRequest $request, Bank $bank)
    {
        $data = $request->validated();

        if ($request->hasFile('logo')) {
            if ($bank->logo) {
                Storage::disk('public')->delete($bank->logo);
            }

            $data['logo'] = $request->file('logo')->store('banks', 'public');
        } else {
            unset($data['logo']);
        }

        $update = $bank->update($data);

        if (! $update) {
            return redirect()->route('banks.index')
                ->with('error', 'Erro ao atualizar banco');
        }

        return redirect()->route('banks.index')
            ->with('success', 'Banco atualizado com sucesso');
    }

    public function export()
    {
        $banks = Bank::query()
            ->withCount('accounts')
            ->orderBy('name')
            ->get();

        $callback = function () use ($banks) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($handle, ['ID', 'Nome', 'Contas Vinculadas']);

            foreach ($banks as $bank) {
                fputcsv($handle, [
                    $bank->id,
                    $bank->name,
                    $bank->accounts_count,
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="bancos.csv"',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bank $bank)
    {
        if ($bank->logo) {
            Storage::disk('public')->delete($bank->logo);
        }

        $bank->delete();

        return redirect()->route('banks.index')
            ->with('success', 'Banco removido com sucesso');
    }
}
