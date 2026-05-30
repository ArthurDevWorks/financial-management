<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankStoreRequest;
use App\Http\Requests\BankUpdateRequest;
use Illuminate\Support\Facades\Storage;
use App\Models\Bank;
use Inertia\Inertia;

class BankController extends Controller
{
    /**
     * List of banks has created
     */
    public function index()
    {
        $banks = Bank::query()
            ->withCount('accounts')
            ->paginate(10);

        $banks->getCollection()->transform(function ($bank) {
            $bank->logo_url = $bank->logo
                ? asset('storage/' . $bank->logo)
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

        if (!$save) {
            return redirect()->route('banks.index')
                ->with('sucess', 'Erro ao cadastrar banco');
        }

        return redirect()->route('banks.index')
            ->with('sucess', 'Banco cadastrado com sucesso');
    }

    /**
     * Show form to update data
     */
    public function edit(Bank $bank)
    {
        $bank->logo_url = $bank->logo
            ? asset('storage/' . $bank->logo)
            : null;

        return Inertia::render('banks/Edit',[
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

        if (!$update) {
            return redirect()->route('banks.index')
                ->with('success', 'Erro ao atualizar banco');
        }

        return redirect()->route('banks.index')
            ->with('success', 'Banco atualizado com sucesso');
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

        return redirect()->route('banks.index');
    }
}
