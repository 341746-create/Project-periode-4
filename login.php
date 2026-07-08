<?php
// ============================================================
//  Aurora Theater — Inloggen
// ============================================================
require_once __DIR__ . '/config/database.php';

$error  = null;
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';
    $password = isset($_POST['password']) ? $_POST['password']       : '';

    if (empty($email) || empty($password)) {
        $error = 'Vul a.u.b. alle velden in.';
    } else {
        try {
            $pdo  = db();
            $stmt = $pdo->prepare("SELECT * FROM gebruikers WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['wachtwoord_hash'])) {
                $expire = isset($_POST['remember']) ? time() + (86400 * 30) : 0;
                setcookie('gebruiker_id',   $user['id'],   $expire, '/');
                setcookie('gebruiker_naam', $user['naam'], $expire, '/');
                setcookie('gebruiker_rol',  $user['rol'],  $expire, '/');
                header('Location: /Homepaginamaken/index.php');
                exit;
            } else {
                $error = 'Ongeldig e-mailadres of wachtwoord.';
            }
        } catch (Exception $e) {
            $error = 'Database fout: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inloggen — Aurora Theater</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            background: #1a0000;
        }

        /* ── Achtergrond ── */
        .bg {
            position: fixed;
            inset: 0;
            background:
                url('https://images.unsplash.com/photo-1507924538820-ede94a04019d?auto=format&fit=crop&w=1600&q=80')
                center / cover no-repeat;
            filter: blur(6px) brightness(0.35) saturate(1.6);
            transform: scale(1.05);
            z-index: 0;
        }

        /* rode glow overlay */
        .bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 70% 40%, rgba(180,0,0,0.55) 0%, transparent 70%),
                        radial-gradient(ellipse at 20% 80%, rgba(120,0,0,0.4)  0%, transparent 60%);
        }

        /* ── Card ── */
        .card {
            position: relative;
            z-index: 1;
            width: min(460px, 92vw);
            background: rgba(12, 4, 4, 0.72);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 18px;
            padding: 48px 40px 40px;
            backdrop-filter: blur(22px);
            box-shadow: 0 30px 80px rgba(0,0,0,0.65);
            animation: fadeUp .5s ease both;
        }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(24px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* ── Heading ── */
        .card h1 {
            text-align: center;
            font-size: 2rem;
            font-weight: 800;
            color: #e53e3e;
            letter-spacing: -.5px;
            margin-bottom: 8px;
        }

        /* ── Logo boven de login ── */
         .card .logo {
             display: flex;
             align-items: center;
             justify-content: center;
             width: 64px;
             height: 64px;
             margin: 0 auto 18px;
             border-radius: 16px;
             background: rgba(229,197,38,0.12);
             border: 1px solid rgba(229,197,38,0.35);
             color: #e5c526;
             font-size: 1.8rem;
             text-decoration: none;
             transition: transform .25s ease, box-shadow .25s ease, background .25s ease, border-color .25s ease;
         }

         .card .logo:hover {
             transform: translateY(-3px) scale(1.05);
             background: rgba(229,197,38,0.22);
             border-color: rgba(229,197,38,0.7);
             box-shadow: 0 0 28px rgba(229,197,38,0.35), 0 8px 20px rgba(0,0,0,0.4);
             color: #f4d03f;
         }

        .card .subtitle {
            text-align: center;
            color: rgba(255,255,255,0.45);
            font-size: .9rem;
            margin-bottom: 32px;
        }

        /* ── Error ── */
        .alert {
            background: rgba(220,38,38,0.15);
            border: 1px solid rgba(220,38,38,0.35);
            color: #fca5a5;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: .88rem;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── Form groups ── */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: .85rem;
            font-weight: 600;
            color: rgba(255,255,255,0.8);
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap .icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.3);
            font-size: .9rem;
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(255,255,255,0.1);
            border-radius: 10px;
            padding: 13px 46px;
            color: #fff;
            font-size: .95rem;
            outline: none;
            transition: border-color .2s, background .2s;
        }

        .input-wrap input::placeholder {
            color: rgba(255,255,255,0.25);
        }

        .input-wrap input:focus {
            border-color: #e53e3e;
            background: rgba(229,62,62,0.06);
        }

        .toggle-eye {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255,255,255,0.3);
            cursor: pointer;
            font-size: .95rem;
            transition: color .15s;
            padding: 0;
        }

        .toggle-eye:hover { color: #fff; }

        /* ── Remember / Forgot ── */
        .row-extra {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
            font-size: .85rem;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255,255,255,0.6);
            cursor: pointer;
            user-select: none;
        }

        .remember input[type="checkbox"] {
            width: 15px;
            height: 15px;
            accent-color: #e53e3e;
            cursor: pointer;
        }

        .forgot {
            color: #fc8181;
            text-decoration: none;
            font-weight: 500;
            transition: color .15s;
        }

        .forgot:hover { color: #fff; }

        /* ── Submit ── */
        .btn-submit {
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, #c53030, #e53e3e);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: transform .2s, box-shadow .2s;
            letter-spacing: .3px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(197,48,48,0.45);
        }

        .btn-submit:active { transform: translateY(0); }

        /* ── Snel inloggen als beheerder ── */
        .btn-admin-quick {
            width: 100%;
            margin-top: 12px;
            padding: 12px;
            border-radius: 10px;
            border: 1.5px solid rgba(197,48,48,0.4);
            background: rgba(197,48,48,0.12);
            color: #fca5a5;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: .15s;
        }

        .btn-admin-quick:hover {
            background: rgba(197,48,48,0.25);
            color: #fff;
            border-color: rgba(229,62,62,0.7);
        }

        /* ── Back link ── */
        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 20px;
            color: rgba(255,255,255,0.35);
            font-size: .85rem;
            text-decoration: none;
            transition: color .15s;
        }

        .back-link:hover { color: rgba(255,255,255,0.75); }

        /* ── Demo ── */
        .demo-section {
            margin-top: 28px;
            padding-top: 22px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }

        .demo-label {
            text-align: center;
            font-size: .75rem;
            color: rgba(255,255,255,0.25);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        .demo-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .demo-btn {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 8px;
            padding: 10px;
            color: rgba(255,255,255,0.45);
            font-size: .8rem;
            cursor: pointer;
            transition: .15s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .demo-btn:hover {
            background: rgba(197,48,48,0.15);
            border-color: rgba(197,48,48,0.4);
            color: #fff;
        }
    </style>
</head>
<body>

<div class="bg"></div>

    <div class="card">
        <a href="/Homepaginamaken/index.php" class="logo">
            <i class="fa-solid fa-masks-theater"></i>
        </a>
        <h1>Welcome Back</h1>
    <p class="subtitle">Reserve your seat under the spotlight</p>

    <?php if ($error): ?>
        <div class="alert">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="/login.php">
        <div class="form-group">
            <label for="email">Email Address</label>
            <div class="input-wrap">
                <i class="fa-solid fa-envelope icon"></i>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    placeholder="e.g., name@example.com"
                    required
                    autocomplete="email"
                >
            </div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrap">
                <i class="fa-solid fa-lock icon"></i>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                >
                <button type="button" class="toggle-eye" onclick="togglePw()" id="eyeBtn">
                    <i class="fa-regular fa-eye" id="eyeIcon"></i>
                </button>
            </div>
        </div>

        <div class="row-extra">
            <label class="remember">
                <input type="checkbox" name="remember" id="remember">
                Remember me
            </label>
            <a href="#" class="forgot">Forgot Password?</a>
        </div>

        <button type="submit" class="btn-submit">
            Sign In <i class="fa-solid fa-arrow-right"></i>
        </button>

        <button type="button" class="btn-admin-quick" onclick="quickLogin('admin@aurora-theater.nl','Admin@2026')">
            <i class="fa-solid fa-user-shield"></i> Snel inloggen als beheerder
        </button>
    </form>

    <!-- Quick login voor demo -->
    <div class="demo-section">
        <p class="demo-label">Snel inloggen (Demo)</p>
        <div class="demo-grid">
            <button class="demo-btn" onclick="quickLogin('admin@aurora-theater.nl','Admin@2026')">
                <i class="fa-solid fa-user-shield"></i> Admin
            </button>
            <button class="demo-btn" onclick="quickLogin('demo@aurora-theater.nl','Admin@2026')">
                <i class="fa-solid fa-user"></i> Klant
            </button>
        </div>
    </div>
</div>

<script>
function togglePw() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

function quickLogin(email, password) {
    document.getElementById('email').value    = email;
    document.getElementById('password').value = password;
    document.querySelector('form').submit();
}
</script>

</body>
</html>
