<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $todayTransactions = Transaction::whereDate('transaction_date', $today);

        $summary = [
            'kas_in' => (clone $todayTransactions)->where('kas_flow', 'in')->sum('amount'),
            'kas_out' => (clone $todayTransactions)->where('kas_flow', 'out')->sum('amount'),
            'saldo_in' => (clone $todayTransactions)->where('saldo_flow', 'in')->sum('amount'),
            'saldo_out' => (clone $todayTransactions)->where('saldo_flow', 'out')->sum('amount'),
            'total_fee' => (clone $todayTransactions)->sum('fee'),
            'count' => (clone $todayTransactions)->count(),
        ];

        $recentTransactions = Transaction::with('user')
            ->latest('transaction_date')
            ->limit(10)
            ->get();

        $monthlySummary = Transaction::selectRaw("
                strftime('%Y-%m', transaction_date) as month,
                SUM(CASE WHEN kas_flow = 'in' THEN amount ELSE 0 END) as kas_in,
                SUM(CASE WHEN kas_flow = 'out' THEN amount ELSE 0 END) as kas_out,
                SUM(CASE WHEN saldo_flow = 'in' THEN amount ELSE 0 END) as saldo_in,
                SUM(CASE WHEN saldo_flow = 'out' THEN amount ELSE 0 END) as saldo_out,
                SUM(fee) as total_fee,
                COUNT(*) as count
            ")
            ->groupByRaw("strftime('%Y-%m', transaction_date)")
            ->orderByDesc('month')
            ->limit(6)
            ->get();

        return view('dashboard', compact('summary', 'recentTransactions', 'monthlySummary'));
    }
}
