<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index(Request $request)
    {

        $query = Produk::query();

        if ($request->search) {
            $query->where('nama', 'like', "%{$request->search}%");
        }

        $perPage = $request->input('per_page', 10);

        return Inertia::render('Produk/Index', [
            'produks' => $query
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
            'filters' => $request->only('search', 'per_page'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Produk/Create', [
            'title' => 'Tambah Produk',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string',
            'harga' => 'required|numeric|min:0',
        ]);

        Produk::create($request->all());

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil ditambahkan');
    }

    public function edit(Produk $produk)
    {
        return Inertia::render('Produk/Edit', [
            'title' => 'Edit Produk',
            'produk' => $produk,
        ]);
    }

    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'required|string',
            'harga' => 'required|numeric|min:0',
        ]);

        $produk->update($request->all());

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil diperbarui');
    }

    public function destroy(Produk $produk)
    {
        $produk->delete();

        return redirect()
            ->route('produk.index')
            ->with('success', 'Produk berhasil dihapus');
    }
}
