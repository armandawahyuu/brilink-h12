<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Ringkasan hari ini (pakai amount * quantity, bukan amount saja).
        $todayTransactions = Transaction::whereDate('transaction_date', $today);

        $summary = [
            'kas_in' => (clone $todayTransactions)->where('kas_flow', 'in')->sum(DB::raw('amount * quantity')),
            'kas_out' => (clone $todayTransactions)->where('kas_flow', 'out')->sum(DB::raw('amount * quantity')),
            'saldo_in' => (clone $todayTransactions)->where('saldo_flow', 'in')->sum(DB::raw('amount * quantity')),
            'saldo_out' => (clone $todayTransactions)->where('saldo_flow', 'out')->sum(DB::raw('amount * quantity')),
            'total_fee' => (clone $todayTransactions)->sum('fee'),
            'count' => (clone $todayTransactions)->count(),
        ];

        // Saldo berjalan = saldo awal + akumulasi seluruh arus (sejak tanggal mulai).
        // Asumsi: fee adalah pendapatan tunai agen, jadi menambah kas fisik.
        $saldoKasAwal = (float) Setting::get('saldo_kas_awal', '0');
        $saldoBrilinkAwal = (float) Setting::get('saldo_brilink_awal', '0');
        $tanggalMulai = Setting::get('tanggal_mulai');

        $allTime = Transaction::query();
        if ($tanggalMulai) {
            $allTime->whereDate('transaction_date', '>=', $tanggalMulai);
        }

        $totalKasIn = (clone $allTime)->where('kas_flow', 'in')->sum(DB::raw('amount * quantity'));
        $totalKasOut = (clone $allTime)->where('kas_flow', 'out')->sum(DB::raw('amount * quantity'));
        $totalSaldoIn = (clone $allTime)->where('saldo_flow', 'in')->sum(DB::raw('amount * quantity'));
        $totalSaldoOut = (clone $allTime)->where('saldo_flow', 'out')->sum(DB::raw('amount * quantity'));
        $totalFeeAll = (clone $allTime)->sum('fee');

        $balance = [
            'kas' => $saldoKasAwal + $totalKasIn - $totalKasOut + $totalFeeAll,
            'saldo_brilink' => $saldoBrilinkAwal + $totalSaldoIn - $totalSaldoOut,
            'total_fee' => $totalFeeAll,
            'kas_awal' => $saldoKasAwal,
            'brilink_awal' => $saldoBrilinkAwal,
        ];

        $recentTransactions = Transaction::with('user')
            ->latest('transaction_date')
            ->limit(10)
            ->get();

        $monthlySummary = Transaction::selectRaw("
                strftime('%Y-%m', transaction_date) as month,
                SUM(CASE WHEN kas_flow = 'in' THEN amount * quantity ELSE 0 END) as kas_in,
                SUM(CASE WHEN kas_flow = 'out' THEN amount * quantity ELSE 0 END) as kas_out,
                SUM(CASE WHEN saldo_flow = 'in' THEN amount * quantity ELSE 0 END) as saldo_in,
                SUM(CASE WHEN saldo_flow = 'out' THEN amount * quantity ELSE 0 END) as saldo_out,
                SUM(fee) as total_fee,
                COUNT(*) as count
            ")
            ->groupByRaw("strftime('%Y-%m', transaction_date)")
            ->orderByDesc('month')
            ->limit(6)
            ->get();

        return view('dashboard', compact('summary', 'balance', 'recentTransactions', 'monthlySummary'));
    }
}
