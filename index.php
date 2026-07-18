<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🤝 Community Help Board & Emergency Network</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: radial-gradient(circle at top right, #f8fafc 0%, #e2e8f0 100%); }
        .glass-card { background: rgba(255,255,255,0.9); border: 1px solid rgba(15,23,42,0.08); }
        .glass-input { background: rgba(255,255,255,0.9); border: 1px solid rgba(15,23,42,0.12); color:#0f172a; }
        .glass-input:focus { border-color:#0d9488; outline:none; box-shadow:0 0 0 3px rgba(13,148,136,0.15); }
        .btn-glow { transition: all .2s ease; }
        .btn-glow:hover { transform: translateY(-1px); }
        .form-area { display:none; } .form-area.active { display:block; }
        #dashboard-section,#profile-page-section,#admin-panel-section { display:none; }
        .tab-btn.active { background:#0d9488; color:#fff; }
        .modal.flex { display:flex !important; }
    </style>
</head>
<body class="min-h-screen text-slate-800">

<!-- ============================= AUTH ============================= -->
<div id="login-section" class="flex-1 flex flex-col items-center justify-center p-4 min-h-screen">
    <div class="glass-card max-w-md w-full rounded-3xl p-8 shadow-2xl">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold bg-gradient-to-r from-teal-600 to-emerald-600 bg-clip-text text-transparent">Community Help Board</h1>
            <p class="text-sm text-slate-500 mt-1">Standing together for humanity</p>
        </div>

        <div class="flex p-1 bg-slate-100 border border-slate-200 rounded-2xl mb-6">
            <button class="tab-btn active flex-1 py-2.5 rounded-xl font-bold text-sm" onclick="showAuthTab('login')">Sign In</button>
            <button class="tab-btn flex-1 py-2.5 rounded-xl font-bold text-sm text-slate-500" onclick="showAuthTab('signup')">Register</button>
        </div>

        <div id="authMsg" class="mb-4 p-3.5 rounded-xl text-xs font-semibold hidden"></div>

        <div id="loginArea" class="form-area active space-y-4">
            <form id="loginForm" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Email</label>
                    <input type="email" id="loginEmail" class="glass-input w-full p-3.5 rounded-xl text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1.5 uppercase">Password</label>
                    <input type="password" id="loginPass" class="glass-input w-full p-3.5 rounded-xl text-sm" required>
                </div>
                <button type="submit" class="btn-glow w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-3.5 rounded-xl">Sign In</button>
            </form>
        </div>

        <div id="signupArea" class="form-area space-y-4">
            <form id="signupForm" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Name</label>
                        <input type="text" id="regName" class="glass-input w-full p-3 rounded-xl text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Phone</label>
                        <input type="tel" id="regPhone" class="glass-input w-full p-3 rounded-xl text-sm" pattern="[0-9]{11}" required>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Email</label>
                    <input type="email" id="regEmail" class="glass-input w-full p-3 rounded-xl text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Location</label>
                    <input type="text" id="regLocation" class="glass-input w-full p-3 rounded-xl text-sm" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">Blood Group</label>
                        <select id="regBlood" class="glass-input w-full p-3 rounded-xl text-sm">
                            <option value="Unknown">N/A</option>
                            <option>A+</option><option>A-</option><option>B+</option><option>B-</option>
                            <option>O+</option><option>O-</option><option>AB+</option><option>AB-</option>
                        </select>
                    </div>
                    <div class="flex items-center pt-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="regVolunteer" class="w-4 h-4 rounded accent-teal-600">
                            <span class="text-xs font-semibold text-slate-600">Volunteer 🌟</span>
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Password</label>
                    <input type="password" id="regPass" class="glass-input w-full p-3 rounded-xl text-sm" required>
                </div>
                <button type="submit" class="btn-glow w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-3.5 rounded-xl">Register Account</button>
            </form>
        </div>
    </div>
</div>

<!-- ============================= DASHBOARD ============================= -->
<div id="dashboard-section" class="container mx-auto max-w-7xl p-4 md:p-6 space-y-6">
    <header class="glass-card rounded-3xl p-6 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div id="avatarContainer"></div>
            <div>
                <h3 class="text-xl font-bold flex flex-wrap items-center gap-2">
                    <span id="profileName"></span>
                    <span id="volunteerBadge" class="hidden text-[10px] bg-amber-100 text-amber-700 border border-amber-300 px-2 py-0.5 rounded-full font-bold">🌟 Volunteer</span>
                    <span id="adminBadge" class="hidden text-[10px] bg-red-100 text-red-700 border border-red-300 px-2 py-0.5 rounded-full font-bold">🛡️ Admin</span>
                    <span id="profileBloodBadge" class="text-[10px] bg-teal-100 text-teal-700 border border-teal-300 px-2 py-0.5 rounded-full font-bold"></span>
                </h3>
                <p class="text-xs text-slate-500 mt-1">📍 <span id="profileLocation"></span></p>
                <div id="userRatingView" class="text-xs text-amber-600 mt-1 font-bold"></div>
            </div>
        </div>
        <div class="flex flex-wrap gap-2.5 justify-center">
            <button id="goToAdminBtn" onclick="navigateToPage('admin')" class="hidden bg-rose-100 hover:bg-rose-600 text-rose-700 hover:text-white border border-rose-300 px-4 py-2 rounded-xl font-bold text-xs">🛡️ Admin Dashboard</button>
            <button onclick="navigateToPage('profile')" class="btn-glow bg-teal-500/10 hover:bg-teal-600 text-teal-700 hover:text-white border border-teal-300 px-4 py-2 rounded-xl font-bold text-xs">👤 Settings & Profile</button>
            <button onclick="openInbox()" class="btn-glow bg-purple-500/10 hover:bg-purple-600 text-purple-700 hover:text-white border border-purple-300 px-4 py-2 rounded-xl font-bold text-xs relative">💬 Direct Chat <span id="unreadBadge" class="hidden absolute -top-1.5 -right-1.5 bg-rose-500 text-white text-[9px] w-5 h-5 rounded-full flex items-center justify-center font-bold"></span></button>
            <button onclick="openNotifications()" class="btn-glow bg-blue-500/10 hover:bg-blue-600 text-blue-700 hover:text-white border border-blue-300 px-4 py-2 rounded-xl font-bold text-xs relative">🔔 Alerts <span id="notifBadge" class="hidden absolute -top-1.5 -right-1.5 bg-rose-500 text-white text-[9px] w-5 h-5 rounded-full flex items-center justify-center font-bold"></span></button>
            <button onclick="logout()" class="btn-glow bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-xl font-bold text-xs">🚪 Sign Out</button>
        </div>
    </header>

    <div class="glass-card rounded-3xl p-6">
        <h3 class="font-bold text-teal-700 mb-4 text-sm uppercase">🔍 Filter & Locate Requests</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <input type="text" id="searchBox" placeholder="🔎 Search location or description..." class="glass-input p-3 rounded-xl text-sm w-full">
            <select id="filterCategory" class="glass-input p-3 rounded-xl text-sm w-full">
                <option value="">All Categories</option>
                <option value="food">🍚 Food</option><option value="blood">🩸 Blood Emergency</option>
                <option value="medical">💊 Medical Supplies</option><option value="shelter">🏠 Emergency Shelter</option>
                <option value="other">📦 Other donations</option>
            </select>
            <select id="filterPriority" class="glass-input p-3 rounded-xl text-sm w-full">
                <option value="">All Priorities</option>
                <option value="urgent">🔴 Urgent</option><option value="high">🟠 High</option><option value="medium">🟡 Medium</option>
            </select>
            <div class="flex flex-col justify-center gap-2 px-1">
                <label class="flex items-center gap-2.5 cursor-pointer"><input type="checkbox" id="hideResolved" class="w-4 h-4 rounded accent-teal-600"><span class="text-xs font-semibold text-slate-600">Hide Resolved</span></label>
                <label class="flex items-center gap-2.5 cursor-pointer"><input type="checkbox" id="filterVolunteerOnly" class="w-4 h-4 rounded accent-amber-500"><span class="text-xs font-bold text-amber-600">Volunteers Only 🌟</span></label>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
            <div class="glass-card rounded-3xl p-6 sticky top-6">
                <h3 id="formTitle" class="text-lg font-bold mb-4 text-teal-700 border-b border-slate-200 pb-2">Post Emergency Request</h3>
                <form id="helpForm" class="space-y-4">
                    <input type="hidden" id="editId" value="">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">CATEGORY</label>
                            <select id="category" class="glass-input w-full p-3 rounded-xl text-xs" required>
                                <option value="">Select</option>
                                <option value="food">🍚 Food</option><option value="blood">🩸 Blood</option>
                                <option value="medical">💊 Medical</option><option value="shelter">🏠 Shelter</option>
                                <option value="other">📦 Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-1">PRIORITY</label>
                            <select id="priority" class="glass-input w-full p-3 rounded-xl text-xs" required>
                                <option value="urgent">🔴 Urgent</option><option value="high">🟠 High</option><option value="medium" selected>🟡 Medium</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">LOCATION & ADDRESS</label>
                        <div class="flex gap-2">
                            <input id="userLocation" placeholder="Hospital or Street Address" class="glass-input flex-1 p-3 rounded-xl text-xs" required>
                            <button type="button" onclick="getCurrentLocation()" class="btn-glow bg-teal-500/10 hover:bg-teal-600 text-teal-700 hover:text-white border border-teal-300 px-3.5 rounded-xl font-bold text-xs" title="Get GPS Coordinates">📍 GPS</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div><label class="block text-[10px] font-bold text-slate-500 mb-1">TARGET QUANTITY</label><input type="number" id="targetQty" class="glass-input w-full p-3 rounded-xl text-xs" min="1" value="1"></div>
                        <div><label class="block text-[10px] font-bold text-slate-500 mb-1">COLLECTED</label><input type="number" id="collectedQty" class="glass-input w-full p-3 rounded-xl text-xs" min="0" value="0"></div>
                    </div>
                    <div><label class="block text-[10px] font-bold text-slate-500 mb-1">CONTACT MOBILE</label><input id="contact" placeholder="Phone for coordination" class="glass-input w-full p-3 rounded-xl text-xs" required></div>
                    <div><label class="block text-[10px] font-bold text-slate-500 mb-1">DETAILS & REQUIREMENT</label><textarea id="description" rows="3" placeholder="Explain what help you need clearly..." class="glass-input w-full p-3 rounded-xl text-xs resize-none" required></textarea></div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 mb-1">ATTACH IMAGE / PROOF</label>
                        <input type="file" id="postImage" accept="image/*" class="w-full text-xs text-slate-500">
                        <div id="imagePreviewContainer" class="hidden mt-3 relative">
                            <img id="imagePreview" src="" class="h-20 w-auto rounded-xl border border-slate-200">
                            <button type="button" onclick="clearImageInput()" class="absolute -top-2 left-16 bg-rose-600 text-white rounded-full w-5 h-5 text-[10px] font-bold">×</button>
                        </div>
                    </div>
                    <button type="submit" id="submitBtn" class="btn-glow w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-3.5 rounded-xl text-sm">Post Help Request</button>
                    <button type="button" id="cancelEditBtn" onclick="resetForm()" class="btn-glow w-full bg-slate-300 hover:bg-slate-400 text-slate-700 font-bold py-2 rounded-xl text-xs hidden">Cancel Update</button>
                </form>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div>
                <h3 class="text-lg font-bold text-slate-800 mb-4">📢 Emergency Help Feed <span class="bg-teal-500/10 text-teal-700 text-xs px-2.5 py-0.5 border border-teal-300 rounded-full" id="globalCount">0</span></h3>
                <div id="requestsList" class="flex flex-col gap-4"></div>
            </div>
            <div>
                <h3 class="text-lg font-bold text-rose-600 mb-4">🩸 Real-time Blood Donors</h3>
                <div id="donorList" class="flex gap-4 overflow-x-auto pb-3"></div>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800 mb-4">📝 My Personal Posts <span id="totalPosts" class="bg-teal-500/10 text-teal-700 text-xs px-2.5 py-0.5 border border-teal-300 rounded-full">0</span></h3>
                <div id="myRequestsList" class="flex flex-col gap-4"></div>
            </div>
        </div>
    </div>
</div>

<!-- ============================= PROFILE ============================= -->
<div id="profile-page-section" class="container mx-auto max-w-2xl p-4">
    <div class="glass-card rounded-3xl shadow-2xl overflow-hidden bg-white">
        <div class="bg-slate-50 border-b border-slate-200 p-6 flex items-center justify-between">
            <button onclick="navigateToPage('dashboard')" class="btn-glow bg-slate-200 hover:bg-slate-300 text-slate-700 border border-slate-300 px-4 py-2 rounded-xl font-bold text-xs">⬅️ Back Home</button>
            <h2 class="text-lg font-bold text-teal-700">👤 Profile & Account Settings</h2>
        </div>
        <form id="profileForm" class="p-6 space-y-5">
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 flex flex-col items-center gap-3 text-center">
                <div id="editAvatarContainer"></div>
                <input type="file" id="profileImageInput" accept="image/*" class="text-xs text-slate-500">
            </div>
            <div class="grid grid-cols-3 gap-3 text-center mb-2">
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100"><span class="block text-xs font-bold text-slate-400">REVIEWS</span><span id="profileReviewCount" class="text-base font-extrabold text-teal-700">0</span></div>
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100"><span class="block text-xs font-bold text-slate-400">AVG RATING</span><span id="profileRatingAvg" class="text-base font-extrabold text-amber-600">⭐ 0.0</span></div>
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100"><span class="block text-xs font-bold text-slate-400">BADGE</span><span id="profileStatusBadge" class="text-xs font-extrabold text-slate-700 block mt-1">Member</span></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-xs font-bold text-slate-500 mb-1">Full Name</label><input type="text" id="editProfileName" class="glass-input w-full p-3 rounded-xl text-sm" required></div>
                <div><label class="block text-xs font-bold text-slate-500 mb-1">Phone Number</label><input type="tel" id="editProfilePhone" pattern="[0-9]{11}" class="glass-input w-full p-3 rounded-xl text-sm" required></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-xs font-bold text-slate-500 mb-1">Location / Address</label><input type="text" id="editProfileLocation" class="glass-input w-full p-3 rounded-xl text-sm" required></div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">Blood Group</label>
                    <select id="editProfileBlood" class="glass-input w-full p-3 rounded-xl text-sm">
                        <option>A+</option><option>A-</option><option>B+</option><option>B-</option>
                        <option>O+</option><option>O-</option><option>AB+</option><option>AB-</option><option value="Unknown">Unknown</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-glow w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-3 rounded-xl text-sm">💾 Save Profile Changes</button>
            <div class="mt-6 border-t border-slate-200 pt-4">
                <h4 class="text-xs font-bold text-teal-700 uppercase mb-3">💬 Feedback & Experiences from Others</h4>
                <div id="myReviewsFeed" class="space-y-2.5 max-h-52 overflow-y-auto pr-1"></div>
            </div>
        </form>
    </div>
</div>

<!-- ============================= ADMIN ============================= -->
<div id="admin-panel-section" class="container mx-auto max-w-7xl p-4 md:p-6">
    <div class="glass-card rounded-3xl p-6 shadow-2xl bg-white">
        <div class="flex justify-between items-center border-b border-slate-200 pb-4 mb-6">
            <div>
                <h2 class="text-xl font-black text-rose-600">🛡️ System Administration Board</h2>
                <p class="text-xs text-slate-500">Moderation and core permission control panel</p>
            </div>
            <button onclick="navigateToPage('dashboard')" class="btn-glow bg-slate-200 hover:bg-slate-300 text-slate-700 border border-slate-300 px-4 py-2 rounded-xl font-bold text-xs">⬅️ Exit Board</button>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div>
                <h3 class="text-md font-bold text-rose-600 mb-3 border-b border-slate-100 pb-2">🚨 Reported / Public Posts (<span id="adminTotalPosts">0</span>)</h3>
                <div id="adminPostsList" class="space-y-3 max-h-[500px] overflow-y-auto pr-2"></div>
            </div>
            <div>
                <h3 class="text-md font-bold text-amber-600 mb-3 border-b border-slate-100 pb-2">👥 Platform Members (<span id="adminTotalUsers">0</span>)</h3>
                <div id="adminUsersList" class="space-y-3 max-h-[500px] overflow-y-auto pr-2"></div>
            </div>
        </div>
    </div>
</div>

<!-- ============================= MODALS ============================= -->
<div id="publicProfileModal" class="modal bg-slate-950/40 hidden fixed inset-0 z-[1000] items-center justify-center p-4">
    <div class="glass-card w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] bg-white">
        <div class="bg-teal-50 border-b border-slate-200 p-4 flex justify-between items-center">
            <h3 class="font-bold text-md text-teal-800">👤 User Public Profile</h3>
            <button onclick="closeModal('publicProfileModal')" class="text-2xl hover:bg-slate-100 w-8 h-8 rounded-full">×</button>
        </div>
        <div class="overflow-y-auto p-6 space-y-6 bg-slate-50">
            <div class="flex flex-col sm:flex-row items-center gap-4 bg-white p-4 rounded-2xl border border-slate-200">
                <div id="pubAvatarContainer"></div>
                <div class="text-center sm:text-left flex-1">
                    <h4 id="pubName" class="text-lg font-bold text-slate-800"></h4>
                    <p id="pubLocation" class="text-xs text-slate-500"></p>
                    <div class="flex flex-wrap gap-2 mt-2 justify-center sm:justify-start">
                        <span id="pubBlood" class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded text-[10px] font-bold"></span>
                        <span id="pubRating" class="bg-amber-100 text-amber-700 px-2 py-0.5 rounded text-[10px] font-bold"></span>
                    </div>
                </div>
            </div>
            <div>
                <h4 class="text-sm font-bold text-slate-700 uppercase mb-3">📋 Activity History & Emergency Posts</h4>
                <div id="pubPostsList" class="space-y-3"></div>
            </div>
        </div>
    </div>
</div>

<div id="messageModal" class="modal bg-slate-950/40 hidden fixed inset-0 z-[1000] items-center justify-center p-4">
    <div class="glass-card w-full max-w-md rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] bg-white">
        <div class="bg-teal-50 border-b border-slate-200 p-4 flex justify-between items-center">
            <h3 class="font-bold text-md text-teal-800">💬 Direct Coordination</h3>
            <button onclick="closeModal('messageModal')" class="text-2xl hover:bg-slate-100 w-8 h-8 rounded-full">×</button>
        </div>
        <div class="p-4 bg-slate-50 border-b border-slate-100 text-xs text-slate-600"><span class="opacity-60">Re: </span><span id="messagePostInfo" class="font-bold text-teal-600"></span></div>
        <div id="messageThread" class="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50" style="min-height:250px;max-height:400px;"></div>
        <div class="p-4 bg-white border-t border-slate-200">
            <form id="messageForm" class="flex gap-2">
                <input type="hidden" id="messagePostId">
                <textarea id="messageText" placeholder="Write a respectful coordination message..." class="glass-input flex-1 p-3 rounded-xl text-xs resize-none" rows="1" required></textarea>
                <button type="submit" class="bg-teal-600 hover:bg-teal-500 text-white px-4 rounded-xl text-xs font-bold">Send</button>
            </form>
        </div>
    </div>
</div>

<div id="inboxModal" class="modal bg-slate-950/40 hidden fixed inset-0 z-[1000] items-center justify-center p-4">
    <div class="glass-card w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] bg-white">
        <div class="bg-purple-50 border-b border-slate-200 p-4 flex justify-between items-center">
            <h3 class="font-bold text-md text-purple-700">📨 Direct Message Inbox</h3>
            <button onclick="closeModal('inboxModal')" class="text-2xl hover:bg-slate-100 w-8 h-8 rounded-full">×</button>
        </div>
        <div id="inboxList" class="flex-1 overflow-y-auto p-4 space-y-2 bg-slate-50"></div>
    </div>
</div>

<div id="notifModal" class="modal bg-slate-950/40 hidden fixed inset-0 z-[1000] items-center justify-center p-4">
    <div class="glass-card w-full max-w-md rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] bg-white">
        <div class="bg-blue-50 border-b border-slate-200 p-4 flex justify-between items-center">
            <h3 class="font-bold text-md text-blue-700">Alerts System 🔔</h3>
            <button onclick="closeModal('notifModal')" class="text-2xl hover:bg-slate-100 w-8 h-8 rounded-full">×</button>
        </div>
        <div id="notifList" class="flex-1 overflow-y-auto p-4 space-y-2 bg-slate-50"></div>
        <div class="p-3 text-center border-t border-slate-100"><button onclick="clearNotifications()" class="text-xs text-rose-600 font-bold hover:underline">Clear Notification Logs</button></div>
    </div>
</div>

<div id="ratingModal" class="modal bg-slate-950/40 hidden fixed inset-0 z-[1000] items-center justify-center p-4">
    <div class="glass-card w-full max-w-md rounded-3xl p-6 shadow-2xl space-y-4 bg-white">
        <h3 class="text-lg font-bold text-amber-600 border-b border-slate-200 pb-2">🌟 Submit Experience Rating</h3>
        <p class="text-xs text-slate-500">Your review helps maintain reliability in our helper community.</p>
        <div class="flex justify-center gap-2" id="starRatingSelector">
            <span class="text-2xl cursor-pointer text-slate-300" onclick="setRatingValue(1)">☆</span>
            <span class="text-2xl cursor-pointer text-slate-300" onclick="setRatingValue(2)">☆</span>
            <span class="text-2xl cursor-pointer text-slate-300" onclick="setRatingValue(3)">☆</span>
            <span class="text-2xl cursor-pointer text-slate-300" onclick="setRatingValue(4)">☆</span>
            <span class="text-2xl cursor-pointer text-slate-300" onclick="setRatingValue(5)">☆</span>
        </div>
        <textarea id="ratingReviewText" placeholder="Write a short feedback about this helper..." class="glass-input w-full p-3 rounded-xl text-xs resize-none" rows="2"></textarea>
        <div class="flex gap-2">
            <button onclick="submitRating()" class="flex-1 bg-teal-600 hover:bg-teal-500 text-white py-2.5 rounded-xl text-xs font-bold">Submit Review</button>
            <button onclick="closeModal('ratingModal')" class="flex-1 bg-slate-200 hover:bg-slate-300 text-slate-700 py-2.5 rounded-xl text-xs font-bold">Cancel</button>
        </div>
    </div>
</div>

<footer class="text-center py-6 text-slate-400 text-xs border-t border-slate-200 bg-slate-50">
    🛡️ Community Help Board — PHP + MySQL Edition (CSE 2208 DBMS Lab Project)
</footer>

<script src="assets/app.js"></script>
</body>
</html>
