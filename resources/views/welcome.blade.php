<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EWS - Early Education Management System</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #fafce8;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        header {
            position: fixed;
            top: 0;
            width: 100%;
            background: white;
            border-bottom: 4px solid #d51616;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            z-index: 50;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo img {
            width: 48px;
            height: 48px;
        }

        .logo-text {
            color: #16a34a;
            font-size: 1.5rem;
            font-weight: bold;
            margin: 0;
        }

        .logo-subtitle {
            color: #666;
            font-size: 0.75rem;
            margin: 0;
        }

        .auth-links {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            display: inline-block;
        }

        .btn-primary {
            background-color: #16a34a;
            color: white;
        }

        .btn-primary:hover {
            background-color: #15803d;
        }

        .btn-danger {
            background-color: #d51616;
            color: white;
            box-shadow: 0 4px 6px rgba(213, 22, 22, 0.3);
        }

        .btn-danger:hover {
            background-color: #b91c1c;
        }

        section {
            padding: 2rem;
        }

        .hero {
            padding-top: 8rem;
            padding-bottom: 5rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }

        h1 {
            font-size: 3.5rem;
            font-weight: bold;
            color: #4d7c0f;
            line-height: 1.2;
            margin: 0 0 1rem 0;
        }

        h1 span {
            color: #d51616;
        }

        p {
            color: #555;
            line-height: 1.6;
            margin: 0 0 1rem 0;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin: 1rem 0;
        }

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1rem;
        }

        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1.5rem;
            padding-top: 2rem;
            border-top: 2px solid #dcfce7;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
        }

        .stat-label {
            color: #555;
            margin: 0;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 2px solid;
        }

        .card.green {
            border-color: #16a34a;
        }

        .card.red {
            border-color: #d51616;
        }

        .card.blue {
            border-color: #1e40af;
        }

        .card.dark-green {
            border-color: #4d7c0f;
        }

        .card-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .card-icon.green {
            background-color: #16a34a;
        }

        .card-icon.red {
            background-color: #d51616;
        }

        .card-icon.blue {
            background-color: #1e40af;
        }

        .card-icon.dark-green {
            background-color: #4d7c0f;
        }

        .card-icon svg {
            width: 1.5rem;
            height: 1.5rem;
            color: white;
            stroke: currentColor;
            fill: none;
        }

        .card h3 {
            font-weight: bold;
            font-size: 1.125rem;
            margin: 0 0 0.5rem 0;
        }

        .card.green h3 {
            color: #16a34a;
        }

        .card.red h3 {
            color: #d51616;
        }

        .card.blue h3 {
            color: #1e40af;
        }

        .card.dark-green h3 {
            color: #4d7c0f;
        }

        .card p {
            font-size: 0.875rem;
            margin: 0;
        }

        #features {
            background-color: #dcfce7;
            border-top: 4px solid #d51616;
        }

        .features-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .features-header h2 {
            font-size: 3rem;
            font-weight: bold;
            color: #4d7c0f;
            margin: 0 0 1rem 0;
        }

        .features-header h2 span {
            color: #d51616;
        }

        .features-header p {
            font-size: 1.25rem;
            color: #555;
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 2px solid;
        }

        .feature-card.green {
            border-color: #16a34a;
        }

        .feature-card.red {
            border-color: #d51616;
        }

        .feature-card.blue {
            border-color: #1e40af;
        }

        .feature-card.dark-green {
            border-color: #4d7c0f;
        }

        .feature-card.dark-red {
            border-color: #991b1b;
        }

        .feature-card.dark-blue {
            border-color: #1e3a8a;
        }

        .feature-card-icon {
            width: 4rem;
            height: 4rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .feature-card.green .feature-card-icon {
            background-color: #16a34a;
        }

        .feature-card.red .feature-card-icon {
            background-color: #d51616;
        }

        .feature-card.blue .feature-card-icon {
            background-color: #1e40af;
        }

        .feature-card.dark-green .feature-card-icon {
            background-color: #4d7c0f;
        }

        .feature-card.dark-red .feature-card-icon {
            background-color: #991b1b;
        }

        .feature-card.dark-blue .feature-card-icon {
            background-color: #1e3a8a;
        }

        .feature-card-icon svg {
            width: 2rem;
            height: 2rem;
            color: white;
            stroke: currentColor;
            fill: none;
        }

        .feature-card h3 {
            font-weight: bold;
            font-size: 1.25rem;
            margin: 0 0 0.75rem 0;
        }

        .feature-card.green h3 {
            color: #16a34a;
        }

        .feature-card.red h3 {
            color: #d51616;
        }

        .feature-card.blue h3 {
            color: #1e40af;
        }

        .feature-card.dark-green h3 {
            color: #4d7c0f;
        }

        .feature-card.dark-red h3 {
            color: #991b1b;
        }

        .feature-card.dark-blue h3 {
            color: #1e3a8a;
        }

        .feature-card p {
            color: #555;
            margin: 0;
        }

        .cta-section {
            text-align: center;
            padding: 3rem;
            background-color: #d51616;
            border-radius: 2rem;
            box-shadow: 0 8px 16px rgba(213, 22, 22, 0.2);
        }

        .cta-section h2 {
            font-size: 2.25rem;
            font-weight: bold;
            color: white;
            margin: 0 0 1.5rem 0;
        }

        .cta-section p {
            font-size: 1.25rem;
            color: #fecdd3;
            margin: 0 0 2rem 0;
        }

        footer {
            border-top: 4px solid #16a34a;
            padding: 3rem 2rem;
            background-color: #fafce8;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .footer-col h4 {
            font-weight: bold;
            color: #16a34a;
            margin: 0 0 1rem 0;
        }

        .footer-col ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-col li {
            margin: 0.5rem 0;
        }

        .footer-col a {
            color: #555;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-col a:hover {
            color: #16a34a;
        }

        .footer-bottom {
            border-top: 2px solid #dcfce7;
            padding-top: 2rem;
            text-align: center;
            color: #555;
        }

        @media (max-width: 768px) {

            .hero,
            .features-grid,
            .footer-grid {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 2.5rem;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn-lg {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img src="{{ asset('img/logo.png') }}" alt="EWS Logo">
                    <div>
                        <p class="logo-text">EWS</p>
                        <p class="logo-subtitle">Early Education Management</p>
                    </div>
                </div>
                <div class="auth-links">
                    <a href="{{ route('login') }}" class="btn btn-danger">Login</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section>
        <div class="container">
            <div class="hero">
                <div>
                    <h1>Sistem Manajemen <span>Pendidikan Dini</span> Terpercaya</h1>
                    <p>Platform digital terintegrasi untuk mengelola semua aspek pendidikan anak usia dini. Dari data
                        siswa, guru, hingga laporan perkembangan, semua dalam satu sistem yang mudah digunakan.</p>
                    <div class="btn-group">
                        <a href="{{ route('login') }}" class="btn btn-danger btn-lg">
                            Mulai Sekarang
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-lg"
                            style="background-color: #dcfce7; color: #16a34a; border: 2px solid #16a34a;">Login untuk
                            Lihat
                            Fitur</a>
                    </div>
                    <div class="stats">
                        <div>
                            <p class="stat-value" style="color: #d51616;">100+</p>
                            <p class="stat-label">Sekolah Active</p>
                        </div>
                        <div>
                            <p class="stat-value" style="color: #16a34a;">10K+</p>
                            <p class="stat-label">Pengguna Terdaftar</p>
                        </div>
                        <div>
                            <p class="stat-value" style="color: #1e40af;">99.9%</p>
                            <p class="stat-label">Uptime</p>
                        </div>
                    </div>
                </div>
                <div class="cards-grid">
                    <div class="card green">
                        <div class="card-icon green"><svg viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 8.646 4 4 0 010-8.646M9 21H3v-2a6 6 0 0112 0v2h-6z"></path>
                            </svg></div>
                        <h3>Manajemen Siswa</h3>
                        <p>Data lengkap semua siswa dengan mudah</p>
                    </div>
                    <div class="card red">
                        <div class="card-icon red"><svg viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                                </path>
                            </svg></div>
                        <h3>Konfigurasi Fleksibel</h3>
                        <p>Sesuaikan dengan kebutuhan sekolah Anda</p>
                    </div>
                    <div class="card blue">
                        <div class="card-icon blue"><svg viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg></div>
                        <h3>Laporan Lengkap</h3>
                        <p>Analisis data perkembangan siswa</p>
                    </div>
                    <div class="card dark-green">
                        <div class="card-icon dark-green"><svg viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg></div>
                        <h3>Keamanan Terjamin</h3>
                        <p>Data terlindungi dengan enkripsi</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features">
        <div class="container">
            <div class="features-header">
                <h2>Fitur Unggulan <span>EWS</span></h2>
                <p>Semua fitur yang Anda butuhkan untuk mengelola pendidikan anak dengan efisien</p>
            </div>
            <div class="features-grid">
                <div class="feature-card green">
                    <div class="feature-card-icon"><svg viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg></div>
                    <h3>Data Siswa Terpusat</h3>
                    <p>Kelola semua informasi siswa dari satu dashboard. Termasuk biodata, perkembangan, dan riwayat
                        akademis.</p>
                </div>
                <div class="feature-card red">
                    <div class="feature-card-icon"><svg viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM9 19c-4.3 0-8-1.343-8-3s3.7-3 8-3 8 1.343 8 3-3.7 3-8 3z">
                            </path>
                        </svg></div>
                    <h3>Manajemen Guru & Staf</h3>
                    <p>Atur jadwal, tugas, dan evaluasi kinerja guru dengan mudah. Kelola data seluruh staf sekolah.</p>
                </div>
                <div class="feature-card blue">
                    <div class="feature-card-icon"><svg viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg></div>
                    <h3>Laporan & Analitik</h3>
                    <p>Dapatkan insights mendalam tentang perkembangan siswa dengan laporan visual dan data real-time.
                    </p>
                </div>
                <div class="feature-card dark-green">
                    <div class="feature-card-icon"><svg viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg></div>
                    <h3>Transparansi Pembayaran</h3>
                    <p>Kelola pembayaran SPP, biaya operasional, dan cicilan dengan transparan dan terlapor.</p>
                </div>
                <div class="feature-card dark-red">
                    <div class="feature-card-icon"><svg viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                            </path>
                        </svg></div>
                    <h3>Keamanan Data</h3>
                    <p>Enkripsi tingkat enterprise untuk melindungi semua data sensitif siswa dan sekolah Anda.</p>
                </div>
                <div class="feature-card dark-blue">
                    <div class="feature-card-icon"><svg viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg></div>
                    <h3>Support 24/7</h3>
                    <p>Tim support kami siap membantu Anda kapan saja. Respon cepat dan solusi efektif dijamin.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section style="padding: 5rem 2rem; text-align: center;">
        <div class="container" style="max-width: 900px;">
            <div class="cta-section">
                <h2>Siap Transformasi Pendidikan Anda?</h2>
                <p>Bergabunglah dengan ratusan sekolah yang telah mempercayai EWS untuk mengelola pendidikan mereka.</p>
                <a href="{{ route('login') }}" class="btn btn-lg"
                    style="background-color: white; color: #d51616; display: inline-block;">Masuk Sekarang</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                        <img src="{{ asset('img/logo.png') }}" alt="EWS" style="width: 32px; height: 32px;">
                        <span style="color: #16a34a; font-weight: bold; font-size: 1.125rem; margin: 0;">EWS</span>
                    </div>
                    <p style="color: #555; margin: 0;">Sistem manajemen pendidikan terpercaya.</p>
                </div>
                <div class="footer-col">
                    <h4>Produk</h4>
                    <ul>
                        <li><a href="{{ route('login') }}">Dashboard</a></li>
                        <li><a href="{{ route('login') }}">Fitur</a></li>
                        <li><a href="{{ route('login') }}">Harga</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Perusahaan</h4>
                    <ul>
                        <li><a href="{{ route('login') }}">Tentang</a></li>
                        <li><a href="{{ route('login') }}">Blog</a></li>
                        <li><a href="{{ route('login') }}">Kontak</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="{{ route('login') }}">Privacy</a></li>
                        <li><a href="{{ route('login') }}">Terms</a></li>
                        <li><a href="{{ route('login') }}">Security</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 EWS - Early Education Management System. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>
</body>

</html>
