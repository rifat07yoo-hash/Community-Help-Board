<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "community_db";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

$user_email = $_SESSION['user_email'];

// বর্তমান ইউজারের প্রোফাইল ডাটা লোড
$user_query = $conn->query("SELECT * FROM users WHERE email='$user_email'");
$user_data = $user_query->fetch_assoc();

// নতুন ইমার্জেন্সি পোস্ট হ্যান্ডেলার
if (isset($_POST['post_request'])) {
    $category = $conn->real_escape_string($_POST['category']);
    $priority = $conn->real_escape_string($_POST['priority']);
    $location = $conn->real_escape_string($_POST['location']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $description = $conn->real_escape_string($_POST['description']);
    $target_qty = intval($_POST['target_qty']);

    $sql = "INSERT INTO requests (email, category, priority, location, contact, description, target_qty) 
            VALUES ('$user_email', '$category', '$priority', '$location', '$contact', '$description', '$target_qty')";
    $conn->query($sql);
    header("Location: dashboard.php");
    exit();
}

// পোস্ট ডিলিট লজিক
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // নিশ্চিত করা যে ইউজার নিজের পোস্ট অথবা এডমিন পোস্টটি ডিলিট করছে
    if ($user_data['is_admin']) {
        $conn->query("DELETE FROM requests WHERE id=$delete_id");
    } else {
        $conn->query("DELETE FROM requests WHERE id=$delete_id AND email='$user_email'");
    }
    header("Location: dashboard.php");
    exit();
}

// সমস্ত পোস্ট রিড করা
$requests_result = $conn->query("SELECT requests.*, users.name as user_name FROM requests JOIN users ON requests.email = users.email ORDER BY requests.id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🤝 Community Help Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f1f5f9; }
        .glass-card { background: white; border: 1px solid rgba(15, 23, 42, 0.08); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .glass-input { background: #f8fafc; border: 1px solid #cbd5e1; }
        .glass-input:focus { border-color: #0d9488; outline: none; }
    </style>
</head>
<body class="p-6">

<div class="container mx-auto max-w-7xl space-y-6">
    <header class="glass-card rounded-3xl p-6 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-teal-600 text-white font-extrabold flex items-center justify-center text-xl uppercase">
                <?php echo substr($user_data['name'], 0, 1); ?>
            </div>
            <div>
                <h3 class="text-xl font-bold text-slate-900"><?php echo htmlspecialchars($user_data['name']); ?>
                    <?php if($user_data['is_volunteer']): ?><span class="text-[10px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-bold ml-2">🌟 Volunteer</span><?php endif; ?>
                    <?php if($user_data['is_admin']): ?><span class="text-[10px] bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-bold ml-2">🛡️ Admin</span><?php endif; ?>
                </h3>
                <p class="text-xs text-slate-500 mt-1">📍 <?php echo htmlspecialchars($user_data['location']); ?> | 🩸 <?php echo htmlspecialchars($user_data['blood']); ?></p>
            </div>
        </div>
        <div>
            <a href="login.php" class="bg-rose-100 hover:bg-rose-600 text-rose-700 hover:text-white font-bold py-2.5 px-5 rounded-xl text-xs transition">🚪 Sign Out</a>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="glass-card rounded-3xl p-6 sticky top-6">
                <h3 class="text-lg font-bold mb-4 text-teal-700 border-b pb-2">Post Emergency Request</h3>
                <form action="dashboard.php" method="POST" class="space-y-4">
                    <input type="hidden" name="post_request" value="1">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">CATEGORY</label>
                            <select name="category" class="glass-input w-full p-3 rounded-xl text-xs" required>
                                <option value="food">🍚 Food</option>
                                <option value="blood">🩸 Blood</option>
                                <option value="medical">💊 Medical</option>
                                <option value="shelter">🏠 Shelter</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">PRIORITY</label>
                            <select name="priority" class="glass-input w-full p-3 rounded-xl text-xs" required>
                                <option value="urgent">🔴 Urgent</option>
                                <option value="high">🟠 High</option>
                                <option value="medium">🟡 Medium</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">LOCATION & ADDRESS</label>
                        <input name="location" placeholder="Hospital or Street Address" class="glass-input w-full p-3 rounded-xl text-xs" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">TARGET QUANTITY</label>
                        <input type="number" name="target_qty" class="glass-input w-full p-3 rounded-xl text-xs" min="1" value="1">
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">CONTACT MOBILE</label>
                        <input name="contact" placeholder="Phone for coordination" class="glass-input w-full p-3 rounded-xl text-xs" required>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">DETAILS</label>
                        <textarea name="description" rows="3" placeholder="Explain clearly..." class="glass-input w-full p-3 rounded-xl text-xs resize-none" required></textarea>
                    </div>

                    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-3.5 rounded-xl text-sm transition shadow-md">Post Help Request</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <h3 class="text-lg font-bold text-slate-800">📢 Emergency Help Feed</h3>
            <div class="flex flex-col gap-4">
                <?php if ($requests_result->num_rows > 0): ?>
                    <?php while($row = $requests_result->fetch_assoc()): ?>
                        <div class="glass-card p-5 rounded-3xl bg-white border-t-4 border-teal-500">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <span class="px-2.5 py-1 text-[10px] rounded-lg font-bold uppercase bg-teal-100 text-teal-800"><?php echo htmlspecialchars($row['category']); ?></span>
                                    <span class="text-[10px] font-extrabold text-slate-500 ml-2"><?php echo htmlspecialchars($row['priority']); ?></span>
                                </div>
                                <?php if($row['email'] == $user_email || $user_data['is_admin']): ?>
                                    <a href="dashboard.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this post?')" class="text-xs text-rose-600 font-bold bg-rose-50 px-2.5 py-1 rounded-lg">Delete</a>
                                <?php endif; ?>
                            </div>
                            <h4 class="font-bold text-slate-800 text-sm mb-1"><?php echo htmlspecialchars($row['user_name']); ?></h4>
                            <p class="text-xs text-teal-600 font-bold mb-2">📍 <?php echo htmlspecialchars($row['location']); ?></p>
                            <p class="text-xs text-slate-600 mb-3"><?php echo htmlspecialchars($row['description']); ?></p>
                            <div class="bg-slate-50 p-2.5 rounded-xl text-teal-700 font-bold text-xs">📞 Contact: <?php echo htmlspecialchars($row['contact']); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-sm text-slate-500 italic">No active help requests found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
