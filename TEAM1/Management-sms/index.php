<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register - Restaurant</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: linear-gradient(135deg, #0f0c29, #1b1b1f, #04042c);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .card {
            background: rgba(65, 61, 61, 0.05);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .icon-box {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 30px;
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        h2 { color: #fff; font-size: 24px; margin-bottom: 5px; }
        p { color: #aaa; font-size: 14px; }
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 18px;
        }
        input {
            width: 100%;
            padding: 14px 14px 14px 45px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-size: 15px;
            transition: all 0.3s;
        }
        input::placeholder { color: #888; }
        input:focus {
            outline: none;
            border-color: #667eea;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="icon-box">
                <i class="bi bi-person-plus"></i>
            </div>
            <h2>Admin Register</h2>
            <p>Create new admin account</p>
        </div>
        
        <form action="database/backend/process.php" method="POST">
            <div class="input-group">
                <i class="bi bi-person"></i>
                <input type="text" name="full_name" placeholder="Full Name" required>
            </div>
            
            <div class="input-group">
                <i class="bi bi-at"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            
            <div class="input-group">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            
            <div class="input-group">
                <i class="bi bi-telephone"></i>
                <input type="tel" name="phone" placeholder="Phone Number" required>
            </div>
            
            <div class="input-group">
                <i class="bi bi-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit">
                <i class="bi bi-check-circle"></i> Register Admin
            </button>
        </form>
        
        <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
            <p style="color: #aaa; font-size: 14px;">Already have an account? <a href="#" style="color: #667eea; text-decoration: none;">Sign in</a></p>
        </div>
    </div>
</body>
</html>
