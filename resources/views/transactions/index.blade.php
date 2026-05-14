@extends('layouts.app')

@section('title', 'Daftar Transaksi')
@section('page-title', 'Daftar Transaksi')
@section('page-description', 'Semua transaksi yang tercatat')
@section('breadcrumb', 'Transaksi')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-filter fs-14 text-muted"></i></div>
                <h4 class="card-title mb-0">Filter</h4>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('transactions.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Jenis</label>
                        <select name="type" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            <option value="tarik_tunai" {{ request('type') == 'tarik_tunai' ? 'selected' : '' }}>Tarik Tunai</option>
                            <option value="setor_tunai" {{ request('type') == 'setor_tunai' ? 'selected' : '' }}>Setor Tunai</option>
                            <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="topup_ewallet" {{ request('type') == 'topup_ewallet' ? 'selected' : '' }}>Topup E-Wallet</option>
                            <option value="pembayaran" {{ request('type') == 'pembayaran' ? 'selected' : '' }}>Pembayaran</option>
                            <option value="lainnya" {{ request('type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Dari Tanggal</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sampai Tanggal</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        <a href="{{ route('transactions.index') }}" class="btn btn-label-secondary btn-sm">Reset</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-list fs-14 text-muted"></i></div>
                <h4 class="card-title mb-0">Transaksi</h4>
                <div class="card-addon">
                    <a href="{{ route('transactions.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i> Catat Baru</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th class="text-end">Nominal</th>
                                <th class="text-end">Qty</th>
                                <th class="text-center">Kas</th>
                                <th class="text-center">Saldo</th>
                                <th class="text-end">Fee</th>
                                <th class="text-center">Source</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $trx)
                            <tr>
                                <td>{{ $trx->transaction_date->format('d/m/Y') }}</td>
                                <td>{{ str_replace('_', ' ', ucfirst($trx->type)) }}</td>
                                <td class="text-end">Rp {{ number_format($trx->amount, 0, ',', '.') }}</td>
                                <td class="text-end">{{ $trx->quantity }}</td>
                                <td class="text-center">
                                    @if($trx->kas_flow === 'in')
                                        <span class="badge bg-success-subtle text-success">Masuk</span>
                                    @elseif($trx->kas_flow === 'out')
                                        <span class="badge bg-danger-subtle text-danger">Keluar</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($trx->saldo_flow === 'in')
                                        <span class="badge bg-info-subtle text-info">Masuk</span>
                                    @elseif($trx->saldo_flow === 'out')
                                        <span class="badge bg-warning-subtle text-warning">Keluar</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($trx->fee, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $trx->source === 'telegram' ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary' }}">
                                        {{ ucfirst($trx->source) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('transactions.edit', $trx) }}" class="btn btn-sm btn-label-primary"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="{{ route('transactions.destroy', $trx) }}" class="d-inline" onsubmit="return confirm('Hapus transaksi ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-label-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Belum ada transaksi</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transactions->hasPages())
                <div class="mt-3">
                    {{ $transactions->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
