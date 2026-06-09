@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan')
@section('page-description', 'Konfigurasi aplikasi dan integrasi')
@section('breadcrumb', 'Pengaturan')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#tab-integrasi" role="tab">
                            <i class="fas fa-plug me-1"></i> Integrasi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-umum" role="tab">
                            <i class="fas fa-user-shield me-1"></i> Akun
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#tab-saldo" role="tab">
                            <i class="fas fa-wallet me-1"></i> Saldo
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="tab-integrasi" role="tabpanel">
                        <form method="POST" action="{{ route('settings.update') }}">
                            @csrf
                            @method('PUT')

                            <h5 class="fw-semibold mb-3"><i class="fab fa-telegram text-info me-2"></i>Telegram Bot</h5>
                            <p class="text-muted mb-4">Bot induk dikelola dari server. User cukup chat ke bot, tanpa membuat bot masing-masing.</p>

                            <div class="alert {{ $telegram['configured'] ? 'alert-success' : 'alert-warning' }}">
                                <div class="fw-semibold">
                                    {{ $telegram['configured'] ? 'Bot induk aktif' : 'Bot induk belum dikonfigurasi di server' }}
                                </div>
                                @if($telegram['username'])
                                    <div>Username: <a href="https://t.me/{{ ltrim($telegram['username'], '@') }}" target="_blank" rel="noopener">{{ '@' . ltrim($telegram['username'], '@') }}</a></div>
                                @endif
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Allowed Chat ID</label>
                                <input type="text" name="telegram_chat_id" value="{{ old('telegram_chat_id', $settings['telegram_chat_id']) }}" class="form-control @error('telegram_chat_id') is-invalid @enderror" placeholder="123456789">
                                @error('telegram_chat_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Chat ID yang diizinkan kirim transaksi. Kirim <code>/start</code> ke bot, nanti bot akan balas Chat ID lo.</small>
                            </div>

                            <hr>
                            <h5 class="fw-semibold mb-3"><i class="fas fa-brain text-warning me-2"></i>AI OCR</h5>
                            <p class="text-muted mb-4">AI untuk baca struk dikelola dari server, jadi API key tidak perlu diisi di aplikasi.</p>

                            <div class="alert {{ $ai['configured'] ? 'alert-success' : 'alert-warning' }}">
                                <div class="fw-semibold">
                                    {{ $ai['configured'] ? 'AI OCR aktif' : 'AI OCR belum dikonfigurasi di server' }}
                                </div>
                                <div>Model: <code>{{ $ai['ocr_model'] }}</code></div>
                                <div>Endpoint: <code>{{ $ai['base_url'] }}</code></div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                            </div>
                        </form>

                        @if($telegram['configured'])
                        <hr>
                        <form method="POST" action="{{ route('settings.telegram.webhook') }}">
                            @csrf
                            <p class="text-muted mb-2">Webhook diarahkan ke domain aplikasi:</p>
                            <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-link me-1"></i> Set Webhook</button>
                        </form>
                        @endif
                    </div>

                    <div class="tab-pane" id="tab-umum" role="tabpanel">
                        <form method="POST" action="{{ route('settings.update.password') }}">
                            @csrf
                            @method('PUT')

                            <h5 class="fw-semibold mb-3"><i class="fas fa-key text-primary me-2"></i>Ganti Password Admin</h5>
                            <p class="text-muted mb-4">Gunakan password yang kuat dan hanya dibagikan ke orang yang memang mengelola pencatatan.</p>

                            <div class="mb-3">
                                <label class="form-label">Password Sekarang <span class="text-danger">*</span></label>
                                <input type="password" name="current_password" required autocomplete="current-password" class="form-control @error('current_password') is-invalid @enderror">
                                @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password Baru <span class="text-danger">*</span></label>
                                <input type="password" name="password" required autocomplete="new-password" class="form-control @error('password') is-invalid @enderror">
                                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Minimal 10 karakter, mengandung huruf dan angka.</small>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" required autocomplete="new-password" class="form-control">
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Ganti Password</button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane" id="tab-saldo" role="tabpanel">
                        <form method="POST" action="{{ route('settings.update.saldo') }}">
                            @csrf
                            @method('PUT')

                            <h5 class="fw-semibold mb-3"><i class="fas fa-wallet text-success me-2"></i>Saldo Awal</h5>
                            <p class="text-muted mb-4">Masukkan saldo awal untuk mulai pencatatan. Saldo ini digunakan sebagai dasar perhitungan di dashboard.</p>

                            <div class="mb-3">
                                <label class="form-label">Saldo Kas Awal (Rp)</label>
                                <input type="number" name="saldo_kas_awal" value="{{ old('saldo_kas_awal', $settings['saldo_kas_awal']) }}" class="form-control @error('saldo_kas_awal') is-invalid @enderror" placeholder="0" min="0">
                                @error('saldo_kas_awal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Uang fisik yang ada di tangan saat mulai pencatatan</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Saldo BRILink Awal (Rp)</label>
                                <input type="number" name="saldo_brilink_awal" value="{{ old('saldo_brilink_awal', $settings['saldo_brilink_awal']) }}" class="form-control @error('saldo_brilink_awal') is-invalid @enderror" placeholder="0" min="0">
                                @error('saldo_brilink_awal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Saldo di aplikasi BRILink saat mulai pencatatan</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanggal Mulai Pencatatan</label>
                                <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', $settings['tanggal_mulai']) }}" class="form-control @error('tanggal_mulai') is-invalid @enderror">
                                @error('tanggal_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Tanggal mulai pencatatan transaksi</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">Simpan Saldo Awal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <div class="card-icon"><i class="fas fa-info-circle fs-14 text-muted"></i></div>
                <h4 class="card-title mb-0">Panduan</h4>
            </div>
            <div class="card-body">
                <h6 class="fw-semibold">Cara Setup Telegram Bot:</h6>
                <ol class="text-muted ps-3">
                    <li>Buka Telegram dan cari bot induk aplikasi</li>
                    <li>Kirim <code>/start</code></li>
                    <li>Copy Chat ID yang dibalas bot</li>
                    <li>Masukkan Chat ID di form ini untuk membatasi akses</li>
                </ol>
                <hr>
                <p class="text-muted mb-0"><small>Setelah token disimpan, bot akan otomatis aktif menerima pesan untuk pencatatan transaksi.</small></p>
            </div>
        </div>
    </div>
</div>
@endsection
