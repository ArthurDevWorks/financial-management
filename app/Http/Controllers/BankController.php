<?php

namespace App\Http\Controllers;

use App\Http\Requests\BankStoreRequest;
use App\Http\Requests\BankUpdateRequest;
use App\Models\Bank;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Http\Request;

class BankController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $banks = Bank::orderBy('name', 'asc')->paginate(15);

        return $banks;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BankStoreRequest $request)
    {
        $validated = $request->validated();

        if ($request->hasFile('logo'))
        {
            $path = $request->file('logo')->store('banks', 'public');
            $validated['logo'] = $request->file('logo')->store('banks', 'public');
        }

        return Bank::create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bank $bank)
    {
        return $bank;
    }   

    /**
     * Update the specified resource in storage.
     */
    public function update(BankUpdateRequest $request, Bank $bank)
    {
        if ($request->hasFile('logo')) {
            if ($bank->logo) {
                Storage::disk('public')->delete($bank->logo);
            }

            $validated['logo'] = $request->file('logo')->store('banks', 'public');
        }

        $bank->update($validated);

        return $bank;
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

        return response()->noContent();
    }
}
