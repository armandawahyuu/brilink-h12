@extends('layouts.app')

@section('title', 'Catat Transaksi')
@section('page-title', 'Catat Transaksi Baru')
@section('page-description', 'Input transaksi harian BRILink')
@section('breadcrumb', 'Catat Transaksi')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-plus-circle fs-14 text-muted"></i></div>
                <h4 class="card-title mb-0">Form Transaksi</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('transactions.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Jenis Transaksi <span class="text-danger">*</span></label>
                        <select name="type" id="type-select" required class="form-select @error('type') is-invalid @enderror">
                            <option value="">Pilih jenis...</option>
                            <option value="tarik_tunai" {{ old('type') == 'tarik_tunai' ? 'selected' : '' }}>Tarik Tunai</option>
                            <option value="setor_tunai" {{ old('type') == 'setor_tunai' ? 'selected' : '' }}>Setor Tunai</option>
                            <option value="transfer" {{ old('type') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                            <option value="topup_ewallet" {{ old('type') == 'topup_ewallet' ? 'selected' : '' }}>Topup E-Wallet</option>
                            <option value="pembayaran" {{ old('type') == 'pembayaran' ? 'selected' : '' }}>Pembayaran</option>
                            <option value="lainnya" {{ old('type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3" id="flow-override" style="{{ old('type') === 'lainnya' ? '' : 'display:none' }}">
                        <div class="row">
                            <div class="col-6">
                                <label class="form-label">Arus Kas</label>
                                <select name="kas_flow" class="form-select">
                                    <option value="none" {{ old('kas_flow') == 'none' ? 'selected' : '' }}>Tidak ada</option>
                                    <option value="in" {{ old('kas_flow') == 'in' ? 'selected' : '' }}>Masuk</option>
                                    <option value="out" {{ old('kas_flow') == 'out' ? 'selected' : '' }}>Keluar</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Arus Saldo BRILink</label>
                                <select name="saldo_flow" class="form-select">
                                    <option value="none" {{ old('saldo_flow') == 'none' ? 'selected' : '' }}>Tidak ada</option>
                                    <option value="in" {{ old('saldo_flow') == 'in' ? 'selected' : '' }}>Masuk</option>
                                    <option value="out" {{ old('saldo_flow') == 'out' ? 'selected' : '' }}>Keluar</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nominal (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" value="{{ old('amount') }}" required min="0" class="form-control @error('amount') is-invalid @enderror" placeholder="1000000">
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah Transaksi <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" value="{{ old('quantity', 1) }}" required min="1" class="form-control @error('quantity') is-invalid @enderror">
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fee (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="fee" value="{{ old('fee', 0) }}" required min="0" class="form-control @error('fee') is-invalid @enderror" placeholder="5000">
                        @error('fee') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required class="form-control @error('transaction_date') is-invalid @enderror">
                        @error('transaction_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <input type="text" name="note" value="{{ old('note') }}" class="form-control" placeholder="Catatan tambahan (opsional)">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-fill">Simpan Transaksi</button>
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
    document.getElementById('type-select').addEventListener('change', function() {
        document.getElementById('flow-override').style.display = this.value === 'lainnya' ? '' : 'none';
    });
</script>
@endpush
