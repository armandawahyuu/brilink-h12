@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Ringkasan transaksi BRILink hari ini')
@section('breadcrumb', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-xl-2 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Kas Masuk</p>
                        <h5 class="mb-0 text-success">Rp {{ number_format($summary['kas_in'], 0, ',', '.') }}</h5>
                    </div>
                    <div class="avatar avatar-sm avatar-label-success">
                        <i class="fas fa-arrow-down mt-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Kas Keluar</p>
                        <h5 class="mb-0 text-danger">Rp {{ number_format($summary['kas_out'], 0, ',', '.') }}</h5>
                    </div>
                    <div class="avatar avatar-sm avatar-label-danger">
                        <i class="fas fa-arrow-up mt-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Saldo Masuk</p>
                        <h5 class="mb-0 text-info">Rp {{ number_format($summary['saldo_in'], 0, ',', '.') }}</h5>
                    </div>
                    <div class="avatar avatar-sm avatar-label-info">
                        <i class="fas fa-plus mt-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Saldo Keluar</p>
                        <h5 class="mb-0 text-warning">Rp {{ number_format($summary['saldo_out'], 0, ',', '.') }}</h5>
                    </div>
                    <div class="avatar avatar-sm avatar-label-warning">
                        <i class="fas fa-minus mt-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Total Fee</p>
                        <h5 class="mb-0 text-primary">Rp {{ number_format($summary['total_fee'], 0, ',', '.') }}</h5>
                    </div>
                    <div class="avatar avatar-sm avatar-label-primary">
                        <i class="fas fa-coins mt-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <p class="text-muted mb-1">Transaksi</p>
                        <h5 class="mb-0">{{ $summary['count'] }}</h5>
                    </div>
                    <div class="avatar avatar-sm avatar-label-secondary">
                        <i class="fas fa-receipt mt-1"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-exchange-alt fs-14 text-muted"></i></div>
                <h4 class="card-title mb-0">Transaksi Terakhir</h4>
                <div class="card-addon">
                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-label-primary">Lihat Semua</a>
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
                                <th class="text-center">Kas</th>
                                <th class="text-center">Saldo</th>
                                <th class="text-end">Fee</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $trx)
                            <tr>
                                <td>{{ $trx->transaction_date->format('d/m/Y') }}</td>
                                <td>{{ str_replace('_', ' ', ucfirst($trx->type)) }}</td>
                                <td class="text-end">Rp {{ number_format($trx->amount * $trx->quantity, 0, ',', '.') }}</td>
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada transaksi</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-calendar-alt fs-14 text-muted"></i></div>
                <h4 class="card-title mb-0">Rekap Bulanan</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Bulan</th>
                                <th class="text-end">Fee</th>
                                <th class="text-end">Trx</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlySummary as $month)
                            <tr>
                                <td>{{ $month->month }}</td>
                                <td class="text-end text-primary fw-semibold">Rp {{ number_format($month->total_fee, 0, ',', '.') }}</td>
                                <td class="text-end">{{ $month->count }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
