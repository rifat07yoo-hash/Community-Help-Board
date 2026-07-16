<?php
session_start();

// ডাটাবেজ কানেকশন
$host = "localhost";
$db_user = "root"; // আপনার ডাটাবেজ ইউজারনেম
$db_pass = "";     // আপনার ডাটাবেজ পাসওয়ার্ড
$db_name = "community_db";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$msg = "";
$msg_type = "";

// রেজিস্ট্রেশন প্রসেস
if (isset($_POST['register'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = strtolower($conn->real_escape_string(trim($_POST['email'])));
    $location = $conn->real_escape_string($_POST['location']);
    $blood = $conn->real_escape_string($_POST['blood']);
    $is_volunteer = isset($_POST['volunteer']) ? 1 : 0;
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // ইমেইল চেক
    $check = $conn->query("SELECT email FROM users WHERE email='$email'");
    if ($check->num_rows > 0) {
        $msg = "Email already registered!";
        $msg_type = "error";
    } else {
        $sql = "INSERT INTO users (email, name, phone, location, blood, is_volunteer, password) 
                VALUES ('$email', '$name', '$phone', '$location', '$blood', '$is_volunteer', '$password')";
        if ($conn->query($sql)) {
            $msg = "Registration Successful! Please sign in.";
            $msg_type = "success";
        } else {
            $msg = "Registration failed! Try again.";
            $msg_type = "error";
        }
    }
}

// লগইন প্রসেস
if (isset($_POST['login'])) {
    $email = strtolower($conn->real_escape_string(trim($_POST['email'])));
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($user['is_banned']) {
            $msg = "⛔ Access Denied! Your account has been suspended.";
            $msg_type = "error";
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: dashboard.php");
            exit();
        } else {
            $msg = "Invalid password!";
            $msg_type = "error";
        }
    } else {
        $msg = "Credentials invalid or unrecognized!";
        $msg_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🤝 Pro-Community Help Board & Emergency Network</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brandTeal: { 50: '#f0fdfa', 100: '#ccfbf1', 500: '#0d9488', 600: '#0d7e61', 700: '#0f766e', 900: '#115e59' }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: radial-gradient(circle at top right, #f8fafc 0%, #e2e8f0 100%); }
        .glass-card { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(16px); border: 1px solid rgba(15, 23, 42, 0.08); }
        .glass-input { background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(15, 23, 42, 0.12); color: #0f172a; }
        .glass-input:focus { background: #ffffff; border-color: #0d9488; outline: none; box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15); }
        .form-area { display: none; }
        .form-area.active { display: block; }
        .tab-btn.active { background: #0d9488; color: white; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3); }
    </style>
</head>
<body class="min-h-screen text-slate-800 flex flex-col justify-between">

<div class="flex-1 flex items-center justify-center p-4">
    <div class="glass-card max-w-md w-full rounded-3xl p-8 shadow-2xl relative overflow-hidden">
        <div class="text-center mb-8">
            <span class="text-4xl">🤝</span>
            <h1 class="text-2xl font-extrabold mt-2 bg-gradient-to-r from-teal-600 to-emerald-600 bg-clip-text text-transparent">Community Help Board</h1>
            <p class="text-sm text-slate-500 mt-1">Standing together for humanity</p>
        </div>

        <div class="flex p-1 bg-slate-100 border border-slate-200 rounded-2xl mb-6">
            <button class="tab-btn active flex-1 py-2.5 rounded-xl font-bold text-sm" onclick="showAuthTab('login')">Sign In</button>
            <button class="tab-btn flex-1 py-2.5 rounded-xl font-bold text-sm text-slate-500" onclick="showAuthTab('signup')">Register</button>
        </div>

        <?php if(!empty($msg)): ?>
            <div class="mb-4 p-3.5 rounded-xl text-xs font-semibold <?php echo $msg_type == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div id="loginArea" class="form-area active space-y-4">
            <form action="login.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Email Address</label>
                    <input type="email" name="email" class="glass-input w-full p-3.5 rounded-xl text-sm" placeholder="name@domain.com" required>
                </div>
                <div class="relative">
                    <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Password</label>
                    <input type="password" id="loginPass" name="password" class="glass-input w-full p-3.5 rounded-xl text-sm" placeholder="••••••••" required>
                </div>
                <button type="submit" name="login" class="w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-3.5 rounded-xl shadow-lg transition">Sign In Securely</button>
            </form>
        </div>

        <div id="signupArea" class="form-area space-y-4">
            <form action="login.php" method="POST" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Name</label>
                        <input type="text" name="name" class="glass-input w-full p-3 rounded-xl text-sm" placeholder="John Doe" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Phone</label>
                        <input type="tel" name="phone" class="glass-input w-full p-3 rounded-xl text-sm" placeholder="01712345678" required>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Email Address</label>
                    <input type="email" name="email" class="glass-input w-full p-3 rounded-xl text-sm" placeholder="yourname@domain.com" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Address / Location</label>
                    <input type="text" name="location" class="glass-input w-full p-3 rounded-xl text-sm" placeholder="Dhaka, Bangladesh" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Blood Group</label>
                        <select name="blood" class="glass-input w-full p-3 rounded-xl text-sm" required>
                            <option value="A+">A+</option><option value="A-">A-</option>
                            <option value="B+">B+</option><option value="B-">B-</option>
                            <option value="O+">O+</option><option value="O-">O-</option>
                            <option value="AB+">AB+</option><option value="AB-">AB-</option>
                        </select>
                    </div>
                    <div class="flex items-center pt-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="volunteer" class="w-4 h-4 rounded accent-teal-600">
                            <span class="text-xs font-semibold text-slate-600">Volunteer 🌟</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Password</label>
                    <input type="password" name="password" class="glass-input w-full p-3 rounded-xl text-sm" placeholder="••••••••" required>
                </div>
                <button type="submit" name="register" class="w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-3.5 rounded-xl transition shadow-lg">Register Account</button>
            </form>
        </div>
    </div>
</div>

<script>
    function showAuthTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active', 'text-white'));
        document.querySelectorAll('.form-area').forEach(f => f.classList.remove('active'));
        if(tab === 'login') {
            document.querySelectorAll('.tab-btn')[0].classList.add('active', 'text-white');
            document.getElementById('loginArea').classList.add('active');
        } else {
            document.querySelectorAll('.tab-btn')[1].classList.add('active', 'text-white');
            document.getElementById('signupArea').classList.add('active');
        }
    }
</script>
</body>
</html>
