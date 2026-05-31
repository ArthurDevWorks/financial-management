<?php

namespace App\Http\Controllers;

use App\Models\Investiment;
use Inertia\Inertia;

class PrecoTetoController extends Controller
{
    public function index()
    {
        $investimentId = request()->query('investiment_id');

        return Inertia::render('preco-teto/Index', [
            'investiment' => $investimentId
                ? Investiment::find((int) $investimentId, ['id', 'name', 'value'])
                : null,
        ]);
    }
}
