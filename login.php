<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vakano Toserba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            /* Hapus background gradient karena kita pakai video */
            background-color: #000;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            font-family: 'Poppins', sans-serif;
            color: #fff;
            margin: 0;
            overflow: hidden; /* Mencegah scroll yang tidak perlu */
        }

        /* --- STYLING UNTUK FULLSCREEN YOUTUBE BACKGROUND --- */
        .video-background {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            min-width: 100vw;
            min-height: 100vh;
            width: 177.77vh; /* Aspect ratio 16:9 */
            height: 56.25vw;
            z-index: -2;
            pointer-events: none; /* Agar tidak bisa diklik */
        }
        
        /* Overlay gelap agar form login tetap terbaca jelas */
        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6); /* Transparansi hitam 60% */
            z-index: -1;
        }

        /* --- STYLING FORM LOGIN --- */
        .card-custom {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            color: #333;
            position: relative;
            z-index: 10;
        }
        .btn-custom {
            background: linear-gradient(135deg, #ff7f50 0%, #ff6348 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(255, 111, 97, 0.4);
            color: white;
        }
        
        /* Memastikan konten utama berada di atas video */
        .content-wrapper {
            z-index: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
    </style>
</head>
<body>

    <!-- YOUTUBE FULLSCREEN BACKGROUND -->
    <!-- Ganti VIDEO_ID_DISINI dengan ID video yang diinginkan -->
    <iframe class="video-background" 
            src="https://www.youtube.com/embed/u_79vTsToz8?autoplay=1&mute=1&loop=1&playlist=u_79vTsToz8&controls=0&showinfo=0&autohide=1" 
            frameborder="0" 
            allow="autoplay; encrypted-media" 
            allowfullscreen>
    </iframe>
    <!-- Overlay Gelap -->
    <div class="video-overlay"></div>

    <div class="content-wrapper">
        <nav class="navbar navbar-dark bg-transparent py-3">
            <div class="container">
                <a href="index.php" class="navbar-brand text-light text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Beranda
                </a>
            </div>
        </nav>

        <div class="container my-auto py-4">
            <div class="row justify-content-center">
                <div class="col-md-5 col-lg-4">
                    <div class="card-custom p-4 p-md-5">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-dark">Silakan Login</h3>
                            <p class="text-muted small">Masuk Vakano Toserba</p>
                        </div>

                        <form action="proses_login.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Email / Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                                    <input type="text" name="email" class="form-control" placeholder="Masukkan email/username" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" name="login" class="btn btn-custom btn-lg fw-bold rounded-pill">
                                    <i class="fa-solid fa-right-to-bracket me-2"></i> Masuk
                                </button>
                            </div>

                            <div class="text-center">
                                <small class="text-muted">Belum punya akun? <a href="registrasi.php" class="text-success text-decoration-none fw-bold">Daftar di sini</a></small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <footer class="text-center text-light py-3 border-top border-secondary border-opacity-25">
            <small class="text-white-50">&copy; 2026 Vakano Toserba &bull; PT Vakano Center</small>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>