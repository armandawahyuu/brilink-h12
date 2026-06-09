@extends('layouts.app')

@section('title', 'Edit Transaksi')
@section('page-title', 'Edit Transaksi')
@section('page-description', 'Ubah data transaksi')
@section('breadcrumb', 'Edit Transaksi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-edit fs-14 text-muted"></i></div>
                <h4 class="card-title mb-0">Edit Transaksi</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('transactions.update', $transaction) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Jenis Transaksi <span class="text-danger">*</span></label>
                        <select name="type" id="type-select" required class="form-select @error('type') is-invalid @enderror">
                            <option value="tarik_tunai" {{ old('type', $transaction->type) == 'tarik_tunai' ? 'selected' : '' }}>Tarik Tunai</option>
                            <option value="setor_tunai" {{ old('type', $transaction->type) == 'setor_tunai' ? 'selected' : '' }}>Setor Tunai</option>
                            <option value="transfer" {{ old('type', $transaction->type) == 'transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="topup_ewallet" {{ old('type', $transaction->type) == 'topup_ewallet' ? 'selected' : '' }}>Topup E-Wallet</option>
                            <option value="pembayaran" {{ old('type', $transaction->type) == 'pembayaran' ? 'selected' : '' }}>Pembayaran</option>
                            <option value="lainnya" {{ old('type', $transaction->type) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="payment_method" id="method-select" required class="form-select @error('payment_method') is-invalid @enderror">
                            <option value="tunai" {{ old('payment_method', $transaction->payment_method) == 'tunai' ? 'selected' : '' }}>Tunai (pelanggan bayar cash)</option>
                            <option value="edc" {{ old('payment_method', $transaction->payment_method) == 'edc' ? 'selected' : '' }}>Kartu / EDC (gesek, dari saldo pelanggan)</option>
                        </select>
                        <small class="text-muted">Tunai: kas & saldo BRILink bergerak. EDC: hanya fee yang dicatat.</small>
                        @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3" id="flow-override" style="{{ old('type', $transaction->type) === 'lainnya' ? '' : 'display:none' }}">
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label">Arus Kas</label>
                                <select name="kas_flow" class="form-select">
                                    <option value="none" {{ old('kas_flow', $transaction->kas_flow) == 'none' ? 'selected' : '' }}>Tidak ada</option>
                                    <option value="in" {{ old('kas_flow', $transaction->kas_flow) == 'in' ? 'selected' : '' }}>Masuk</option>
                                    <option value="out" {{ old('kas_flow', $transaction->kas_flow) == 'out' ? 'selected' : '' }}>Keluar</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Arus Saldo BRILink</label>
                                <select name="saldo_flow" class="form-select">
                                    <option value="none" {{ old('saldo_flow', $transaction->saldo_flow) == 'none' ? 'selected' : '' }}>Tidak ada</option>
                                    <option value="in" {{ old('saldo_flow', $transaction->saldo_flow) == 'in' ? 'selected' : '' }}>Masuk</option>
                                    <option value="out" {{ old('saldo_flow', $transaction->saldo_flow) == 'out' ? 'selected' : '' }}>Keluar</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" value="{{ old('amount', $transaction->amount) }}" required min="0" class="form-control @error('amount') is-invalid @enderror">
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah Transaksi <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" value="{{ old('quantity', $transaction->quantity) }}" required min="1" class="form-control @error('quantity') is-invalid @enderror">
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fee (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="fee" value="{{ old('fee', $transaction->fee) }}" required min="0" class="form-control @error('fee') is-invalid @enderror">
                        @error('fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="transaction_date" value="{{ old('transaction_date', $transaction->transaction_date->format('Y-m-d')) }}" required class="form-control @error('transaction_date') is-invalid @enderror">
                        @error('transaction_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="note" value="{{ old('note', $transaction->note) }}" class="form-control" placeholder="Catatan tambahan (opsional)">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">Update Transaksi</button>
                        <a href="{{ route('transactions.index') }}" class="btn btn-label-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function refreshFlowOverride() {
        var type = document.getElementById('type-select').value;
        var method = document.getElementById('method-select').value;
        var show = (type === 'lainnya' && method === 'tunai');
        document.getElementById('flow-override').style.display = show ? '' : 'none';
    }
    document.getElementById('type-select').addEventListener('change', refreshFlowOverride);
    document.getElementById('method-select').addEventListener('change', refreshFlowOverride);
    refreshFlowOverride();
</script>
@endpush
