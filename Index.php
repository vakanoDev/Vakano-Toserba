<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang di Vakano Toserba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: transparent; 
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        /* ==== CSS YOUTUBE BACKGROUND ==== */
        .youtube-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: -2;
            overflow: hidden;
            pointer-events: none;
        }

        .youtube-bg iframe {
            width: 100vw;
            height: 56.25vw;
            min-height: 100vh;
            min-width: 177.77vh;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .bg-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(11, 15, 25, 0.6); 
            z-index: -1;
            pointer-events: none;
        }

        .welcome-banner {
            text-align: center;
            padding: 15px 15px 5px 15px;
            animation: fadeInDown 1s ease-in-out;
        }
        .welcome-title {
            font-weight: 800;
            font-size: clamp(1.6rem, 3vw, 2.5rem);
            background: linear-gradient(to right, #ffd700, #ff8c00, #ff4500);
            -webkit-background-clip: text;
            background-clip: text; 
            -webkit-text-fill-color: transparent;
            text-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            letter-spacing: 1.5px;
            margin-bottom: 5px;
        }
        .welcome-subtitle {
            color: #e2e8f0;
            font-weight: 400;
            font-size: clamp(0.85rem, 1.3vw, 1.1rem);
            letter-spacing: 2px;
            text-transform: uppercase;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.4);
        }

        .hero-section {
            background: linear-gradient(rgba(255, 255, 255, 0.75), rgba(255, 255, 255, 0.75)), url('assets/img/FotoTengahBeranda.jpg');
            background-size: cover;
            background-position: center;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        .hero-section:hover {
            transform: translateY(-3px);
        }
        .btn-custom-login {
            background: linear-gradient(135deg, #ff7f50 0%, #ff6348 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 111, 97, 0.3);
        }
        .btn-custom-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(255, 111, 97, 0.5);
            color: white;
        }
        .btn-custom-reg {
            background: linear-gradient(135deg, #20bf6b 0%, #0fb9b1 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(32, 191, 107, 0.3);
        }
        .btn-custom-reg:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(32, 191, 107, 0.5);
            color: white;
        }
        .btn-custom-ceo {
            background: linear-gradient(135deg, #ffd700 0%, #ffb300 100%);
            border: none;
            color: #000;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }
        .btn-custom-ceo:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(255, 215, 0, 0.5);
            color: #000;
        }

        .store-title {
            color: #1e3c72;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <!-- HTML YOUTUBE BACKGROUND -->
    <div class="youtube-bg">
        <iframe src="https://www.youtube.com/embed/u_79vTsToz8?autoplay=1&mute=1&loop=1&playlist=u_79vTsToz8&controls=0&disablekb=1&modestbranding=1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
    </div>
    <div class="bg-overlay"></div>

    <!-- Header / Navbar -->
    <nav class="navbar navbar-dark bg-transparent py-2">
        <div class="container">
            <span class="navbar-brand mb-0 h1 fs-5"><i class="fa-solid fa-store text-warning me-2"></i> Vakano Toserba</span>
        </div>
    </nav>

    <!-- Sambutan Atas -->
    <div class="welcome-banner container">
        <h1 class="welcome-title">SELAMAT DATANG DI VAKANO TOSERBA</h1>
        <p class="welcome-subtitle">DAPATKAN BARANG YANG ANDA MAU</p>
    </div>

    <!-- Konten Utama -->
    <div class="container my-auto pb-3">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-7">
                <div class="hero-section p-4 text-center">
                    <div class="mb-3">
                        <img src="assets/img/LogoBerandaUner.png" alt="Logo Vakano Toserba" style="max-height: 75px; width: auto;" class="shadow-sm rounded-circle border border-2 border-primary p-1 bg-white">
                    </div>
                    <h3 class="store-title mb-1">Vakano Toserba</h3>
                    <p class="text-dark fw-bold small mb-4">Toko Yang Berusaha Jadi Terlengkap & Termurah</p>
                    
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                        <a href="login.php" class="btn btn-custom-login btn-lg px-4 fw-bold rounded-pill flex-fill">
                            <i class="fa-solid fa-right-to-bracket me-2"></i> Login
                        </a>
                        <a href="registrasi.php" class="btn btn-custom-reg btn-lg px-4 fw-bold rounded-pill flex-fill">
                            <i class="fa-solid fa-user-plus me-2"></i> Registrasi
                        </a>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center text-light py-3 border-top border-secondary border-opacity-25 mt-auto">
        <small class="text-white-50">&copy; 2026 Vakano Toserba &bull; PT Vakano Center </small>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>