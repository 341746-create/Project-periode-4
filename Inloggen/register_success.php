<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .bg {
            position: fixed;
            inset: 0;
            background: url('defocused-background-luxurious-private-ci...') center/cover no-repeat;
            filter: brightness(0.4);
            z-index: 0;
        }

        .card {
            position: relative;
            z-index: 1;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 24px;
            padding: 60px 50px;
            text-align: center;
            max-width: 480px;
            width: 90%;
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
            animation: fadeUp 0.8s ease;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .checkmark {
            width: 90px;
            height: 90px;
            background: linear-gradient(135deg, #f5a623, #e8142d);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 40px;
            box-shadow: 0 0 40px rgba(245,166,35,0.5);
            animation: pop 0.6s 0.3s both;
        }

        @keyframes pop {
            0%   { transform: scale(0); }
            70%  { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        h1 {
            font-family: 'Playfair Display', serif;
            color: #fff;
            font-size: 2rem;
            margin-bottom: 12px;
        }

        p {
            color: rgba(255,255,255,0.7);
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 35px;
        }

        .btn {
            display: inline-block;
            padding: 14px 40px;
            background: linear-gradient(135deg, #f5a623, #e8142d);
            color: #fff;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 8px 25px rgba(232,20,45,0.4);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(232,20,45,0.6);
        }

        .stars {
            color: #f5a623;
            font-size: 1.2rem;
            margin-bottom: 20px;
            letter-spacing: 4px;
        }
    </style>
</head>
<body>
    <div class="bg"></div>
    <div class="card">
        <div class="checkmark">✓</div>
        <div class="stars">★ ★ ★ ★ ★</div>
        <h1>Welcome to the Theater!</h1>
        <p>Your account has been created successfully.<br>The spotlight is ready for you.</p>
        <a href="index.php" class="btn">Sign In Now →</a>
    </div>
</body>
</html>