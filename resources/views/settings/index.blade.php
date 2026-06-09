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
                            <i class="fas fa-cog me-1"></i> Umum
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
                            <p class="text-muted mb-4">Hubungkan Telegram Bot untuk input transaksi via chat. Buat bot di <strong>@BotFather</strong> lalu masukkan token dan username di bawah.</p>

                            <div class="mb-3">
                                <label class="form-label">Bot Token</label>
                                <input type="text" name="telegram_bot_token" value="{{ old('telegram_bot_token', $settings['telegram_bot_token']) }}" class="form-control @error('telegram_bot_token') is-invalid @enderror" placeholder="123456789:ABCdefGHIjklMNOpqrsTUVwxyz">
                                @error('telegram_bot_token') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Dapatkan dari @BotFather di Telegram</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Bot Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
                                    <input type="text" name="telegram_bot_username" value="{{ old('telegram_bot_username', $settings['telegram_bot_username']) }}" class="form-control @error('telegram_bot_username') is-invalid @enderror" placeholder="brilink_h12_bot">
                                </div>
                                @error('telegram_bot_username') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Allowed Chat ID</label>
                                <input type="text" name="telegram_chat_id" value="{{ old('telegram_chat_id', $settings['telegram_chat_id']) }}" class="form-control @error('telegram_chat_id') is-invalid @enderror" placeholder="123456789">
                                @error('telegram_chat_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Chat ID yang diizinkan kirim transaksi. Kirim <code>/start</code> ke bot, nanti bot akan balas Chat ID lo.</small>
                            </div>

                            <hr>
                            <h5 class="fw-semibold mb-3"><i class="fas fa-brain text-warning me-2"></i>AI OCR (Groq)</h5>
                            <p class="text-muted mb-4">Untuk fitur baca struk otomatis via foto. Dapatkan API key dari <strong>console.groq.com</strong></p>

                            <div class="mb-3">
                                <label class="form-label">Groq API Key</label>
                                <input type="password" name="groq_api_key" value="{{ old('groq_api_key', $settings['groq_api_key']) }}" class="form-control @error('groq_api_key') is-invalid @enderror" placeholder="gsk_...">
                                @error('groq_api_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Dapatkan dari console.groq.com</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                            </div>
                        </form>

                        @if(\App\Models\Setting::get('telegram_bot_token'))
                        <hr>
                        <form method="POST" action="{{ route('settings.telegram.webhook') }}">
                            @csrf
                            <p class="text-muted mb-2">Setelah token disimpan, aktifkan webhook agar bot bisa menerima pesan:</p>
                            <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-link me-1"></i> Set Webhook</button>
                        </form>
                        @endif
                    </div>

                    <div class="tab-pane" id="tab-umum" role="tabpanel">
                        <p class="text-muted">Pengaturan umum akan tersedia di versi selanjutnya.</p>
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
                    <li>Buka Telegram, cari <strong>@BotFather</strong></li>
                    <li>Kirim <code>/newbot</code></li>
                    <li>Ikuti instruksi, beri nama bot</li>
                    <li>Copy token yang diberikan</li>
                    <li>Paste token di form ini</li>
                    <li>Isi username bot (tanpa @)</li>
                    <li>Klik Simpan</li>
                </ol>
                <hr>
                <p class="text-muted mb-0"><small>Setelah token disimpan, bot akan otomatis aktif menerima pesan untuk pencatatan transaksi.</small></p>
            </div>
        </div>
    </div>
</div>
@endsection
