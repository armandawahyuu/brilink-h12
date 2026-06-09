<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('user')->latest('transaction_date');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->paginate(20);

        return view('transactions.index', compact('transactions'));
    }

    public function create()
    {
        return view('transactions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:tarik_tunai,setor_tunai,transfer,topup_ewallet,pembayaran,lainnya',
            'payment_method' => 'required|in:tunai,edc',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'fee' => 'required|numeric|min:0',
            'kas_flow' => 'nullable|in:in,out,none',
            'saldo_flow' => 'nullable|in:in,out,none',
            'note' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
        ]);

        $validated = $this->applyFlows($validated);
        $validated['user_id'] = auth()->id();
        $validated['source'] = 'web';

        Transaction::create($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dicatat.');
    }

    public function edit(Transaction $transaction)
    {
        return view('transactions.edit', compact('transaction'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'type' => 'required|in:tarik_tunai,setor_tunai,transfer,topup_ewallet,pembayaran,lainnya',
            'payment_method' => 'required|in:tunai,edc',
            'amount' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'fee' => 'required|numeric|min:0',
            'kas_flow' => 'nullable|in:in,out,none',
            'saldo_flow' => 'nullable|in:in,out,none',
            'note' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
        ]);

        $validated = $this->applyFlows($validated);

        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil diupdate.');
    }

    /**
     * Tentukan kas_flow & saldo_flow berdasarkan jenis + metode pembayaran.
     * - EDC: selalu none/none (uang dari saldo kartu pelanggan).
     * - Tunai jenis 'lainnya': boleh override manual dari form.
     * - Tunai jenis lain: ikut peta default.
     */
    private function applyFlows(array $validated): array
    {
        $method = $validated['payment_method'] ?? 'tunai';
        $flows = Transaction::getFlows($validated['type'], $method);

        if ($method === 'edc') {
            $validated['kas_flow'] = 'none';
            $validated['saldo_flow'] = 'none';
        } else {
            $validated['kas_flow'] = $validated['kas_flow'] ?? $flows['kas_flow'];
            $validated['saldo_flow'] = $validated['saldo_flow'] ?? $flows['saldo_flow'];
        }

        return $validated;
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }
}
