<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Video Archival Solution</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #141E30, #243B55);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
            padding: 40px;
            border-radius: 16px;
            text-align: center;
            width: 380px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        }
        .login-card img {
            width: 100px;
            margin-bottom: 20px;
        }
        .login-card input {
            margin-bottom: 15px;
        }
        #errorMessage {
            display: none;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255, 99, 99, 0.9);
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div id="errorMessage"></div>
    <div class="login-card">
        <img src="src/assets/images/round.png" alt="Logo">
        <h2 class="mb-4">Video Archival Portal</h2>

        <form id="loginForm">
            <input type="email" name="email" class="form-control" placeholder="Enter email" required>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>

    <script>
        document.getElementById("loginForm").addEventListener("submit", function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch("checkLogin.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = "uploads/view";
                } else {
                    showError(data.message);
                }
            })
            .catch(() => showError("An unexpected error occurred. Please try again."));
        });

        function showError(message) {
            const errorDiv = document.getElementById("errorMessage");
            errorDiv.textContent = message;
            errorDiv.style.display = "block";
            setTimeout(() => { errorDiv.style.display = "none"; }, 4000);
        }
    </script>
</body>
</html>
