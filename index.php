<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🤝 Community Help Board & Emergency Network</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at top right, #f8fafc 0%, #e2e8f0 100%);
            transition: background .35s ease, color .35s ease;
        }
        .glass-card { background: rgba(255,255,255,0.9); border: 1px solid rgba(15,23,42,0.08); transition: background .3s ease, border-color .3s ease; }
        .glass-input { background: rgba(255,255,255,0.9); border: 1px solid rgba(15,23,42,0.12); color:#0f172a; transition: background .3s ease, border-color .3s ease, color .3s ease; }
        .glass-input:focus { border-color:#0d9488; outline:none; box-shadow:0 0 0 3px rgba(13,148,136,0.15); }
        .btn-glow { transition: transform .2s ease, background-color .2s ease, color .2s ease, box-shadow .2s ease; }
        .btn-glow:hover { transform: translateY(-2px) scale(1.045); box-shadow: 0 8px 20px -8px rgba(13,148,136,0.35); }
        .btn-glow:active { transform: translateY(0) scale(0.96); }
        .form-area { display:none; } .form-area.active { display:block; }
        #dashboard-section,#profile-page-section,#admin-panel-section { display:none; }
        .tab-btn { transition: background-color .25s ease, color .25s ease; }
        .tab-btn.active { background:#0d9488; color:#fff; }
        .admin-tab-btn.active { background:#e11d48; color:#fff; }
        .modal.flex { display:flex !important; }

        /* ---------------- Dark mode ---------------- */
        html.dark body { background: radial-gradient(circle at top right, #0f172a 0%, #020617 100%); color:#e2e8f0; }
        html.dark .glass-card { background: rgba(15,23,42,0.85); border: 1px solid rgba(148,163,184,0.15); color:#e2e8f0; }
        html.dark .glass-input { background: rgba(30,41,59,0.9); border: 1px solid rgba(148,163,184,0.25); color:#e2e8f0; }
        html.dark .glass-input::placeholder { color:#94a3b8; }
        html.dark footer { background:#020617 !important; border-color:rgba(148,163,184,0.15) !important; color:#64748b !important; }
        html.dark .bg-slate-50 { background-color:#1e293b !important; }
        html.dark .bg-slate-100 { background-color:#1e293b !important; }
        html.dark .bg-slate-200 { background-color:#334155 !important; }
        html.dark .border-slate-100, html.dark .border-slate-200 { border-color: rgba(148,163,184,0.2) !important; }
        html.dark .text-slate-800, html.dark .text-slate-700, html.dark .text-slate-600 { color:#e2e8f0 !important; }
        html.dark .text-slate-500, html.dark .text-slate-400 { color:#94a3b8 !important; }

        /* ---------------- Animations ---------------- */
        @keyframes fadeUp { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        @keyframes wave { 0%,100% { transform: rotate(0deg); } 15% { transform: rotate(18deg); } 30% { transform: rotate(-12deg); } 45% { transform: rotate(14deg); } 60% { transform: rotate(-6deg); } 75% { transform: rotate(4deg); } }
        @keyframes softPulse { 0%,100% { box-shadow: 0 0 0 0 rgba(13,148,136,0.35); } 50% { box-shadow: 0 0 0 10px rgba(13,148,136,0); } }
        @keyframes spinSlow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        .anim-fade-up { animation: fadeUp .5s ease both; }
        .header-actions > * { animation: fadeUp .45s ease both; }
        .header-actions > *:nth-child(1) { animation-delay: .05s; }
        .header-actions > *:nth-child(2) { animation-delay: .1s; }
        .header-actions > *:nth-child(3) { animation-delay: .15s; }
        .header-actions > *:nth-child(4) { animation-delay: .2s; }
        .header-actions > *:nth-child(5) { animation-delay: .25s; }

        .hand-logo { display:inline-block; transform-origin: 70% 70%; animation: wave 2.4s ease-in-out infinite; }
        .theme-toggle-btn { transition: transform .3s ease, background-color .2s ease; }
        .theme-toggle-btn:hover { transform: rotate(20deg) scale(1.1); }
        .theme-toggle-btn:active { transform: scale(0.9); }
        .theme-icon-sun, .theme-icon-moon { transition: opacity .25s ease, transform .4s ease; }

        /* ---------------- Page switch transition ---------------- */
        .page-fade { animation: fadeUp .4s ease both; }

        /* ---------------- Dashboard card entrance + hover ---------------- */
        @keyframes cardPop { from { opacity:0; transform: translateY(16px) scale(.98); } to { opacity:1; transform: translateY(0) scale(1); } }
        .card-pop { animation: cardPop .45s cubic-bezier(.22,1,.36,1) both; }
        #requestsList .glass-card, #myRequestsList .glass-card, #donorList .glass-card {
            transition: transform .25s ease, box-shadow .25s ease;
        }
        #requestsList .glass-card:hover, #myRequestsList .glass-card:hover, #donorList .glass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 28px -12px rgba(15,23,42,0.18);
        }
        html.dark #requestsList .glass-card:hover, html.dark #myRequestsList .glass-card:hover, html.dark #donorList .glass-card:hover {
            box-shadow: 0 14px 28px -12px rgba(0,0,0,0.55);
        }

        /* ---------------- Progress bar fill animation ---------------- */
        @keyframes fillBar { from { width: 0%; } }
        .progress-fill { animation: fillBar .8s ease-out both; }

        /* ---------------- Badge pulse for "New" / unread counters ---------------- */
        .badge-pulse { animation: softPulse 1.8s ease-in-out infinite; }

        /* ---------------- Filter panel entrance ---------------- */
        #dashboard-section .glass-card.p-6 > div.grid > * { animation: fadeUp .4s ease both; }

        /* ---------------- Password strength meter ---------------- */
        .pw-strength-bar { width: 0%; background-color: #cbd5e1; transition: width .35s cubic-bezier(.22,1,.36,1), background-color .35s ease; }
        .pw-strength-weak { background-color: #ef4444 !important; }
        .pw-strength-medium { background-color: #f59e0b !important; }
        .pw-strength-strong { background-color: #10b981 !important; }
        @keyframes shakeInvalid { 0%,100% { transform: translateX(0); } 20%,60% { transform: translateX(-4px); } 40%,80% { transform: translateX(4px); } }
        .shake-invalid { animation: shakeInvalid .35s ease; }
        @keyframes pwCheckIn { from { opacity:0; transform: translateY(4px); } to { opacity:1; transform: translateY(0); } }
        .pw-check-item { animation: pwCheckIn .25s ease both; transition: color .2s ease; }
        .pw-check-icon { display:inline-block; transition: transform .25s cubic-bezier(.22,1.4,.36,1), color .2s ease; }
        .pw-check-item.pw-met .pw-check-icon { transform: scale(1.25); color:#10b981; }
        .pw-check-item.pw-met { color:#0f766e; }

        /* ---------------- Admin panel: stat cards + tab switch ---------------- */
        @keyframes adminStatIn { from { opacity:0; transform: translateY(10px) scale(.96); } to { opacity:1; transform: translateY(0) scale(1); } }
        .admin-stat-card { animation: adminStatIn .4s cubic-bezier(.22,1,.36,1) both; transition: transform .2s ease, box-shadow .2s ease; }
        .admin-stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 24px -12px rgba(15,23,42,0.18); }
        .admin-tab-btn { transition: all .25s cubic-bezier(.22,1,.36,1); }
        .admin-tab-panel { animation: fadeUp .35s ease both; }

        /* ---------------- Modal open animation ---------------- */
        @keyframes modalPop { from { opacity:0; transform: scale(.94) translateY(8px); } to { opacity:1; transform: scale(1) translateY(0); } }
        .modal.flex > div { animation: modalPop .3s cubic-bezier(.22,1,.36,1) both; }

        /* ---------------- Profile stat cards + activity timeline ---------------- */
        @keyframes statPop { from { opacity:0; transform: scale(.9); } to { opacity:1; transform: scale(1); } }
        .stat-pop { animation: statPop .4s cubic-bezier(.22,1,.36,1) both; }
        @keyframes activityIn { from { opacity:0; transform: translateX(-8px); } to { opacity:1; transform: translateX(0); } }
        .activity-item { animation: activityIn .35s ease both; transition: transform .2s ease, box-shadow .2s ease; }
        .activity-item:hover { transform: translateX(2px); }

        /* ---------------- Press feedback for every dashboard/profile/admin button ---------------- */
        #dashboard-section button, #profile-page-section button, #admin-panel-section button {
            transition: transform .18s ease, background-color .2s ease, color .2s ease, box-shadow .2s ease;
        }
        #dashboard-section button:active, #profile-page-section button:active, #admin-panel-section button:active {
            transform: scale(0.95);
        }
    </style>
</head>
<body class="min-h-screen text-slate-800">

<!-- Floating dark-mode toggle, visible on every page -->
<button id="themeToggleBtn" onclick="toggleDarkMode()"
    class="theme-toggle-btn fixed top-4 right-4 z-[1100] w-11 h-11 rounded-full glass-card shadow-lg flex items-center justify-center text-lg"
    title="Toggle dark mode">
    <span id="themeIconSun" class="theme-icon-sun">☀️</span>
    <span id="themeIconMoon" class="theme-icon-moon hidden">🌙</span>
</button>

<!-- ============================= AUTH ============================= -->
<div id="login-section" class="flex-1 flex flex-col items-center justify-center p-4 min-h-screen">
    <div class="glass-card max-w-md w-full rounded-3xl p-8 shadow-2xl">
        <div class="text-center mb-6 anim-fade-up">
            <div class="w-20 h-20 mx-auto mb-3 rounded-3xl bg-gradient-to-br from-teal-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-teal-500/30">
                <span class="hand-logo text-4xl">🤝</span>
            </div>
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
                    <div class="relative">
                        <input type="password" id="regPass" minlength="6" placeholder="Minimum 6 characters" class="glass-input w-full p-3 pr-10 rounded-xl text-sm" required autocomplete="new-password">
                        <button type="button" id="regPassToggle" onclick="toggleRegPassVisibility()" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-teal-600 text-sm" tabindex="-1">👁️</button>
                    </div>
                    <div class="mt-2">
                        <div class="h-1.5 w-full bg-slate-200 rounded-full overflow-hidden">
                            <div id="pwStrengthBar" class="h-full rounded-full pw-strength-bar" style="width:0%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-1">
                            <span id="pwStrengthLabel" class="text-[10px] font-bold text-slate-400">Enter a password</span>
                        </div>
                        <div id="pwChecklist" class="grid grid-cols-2 gap-x-3 gap-y-1 mt-2 text-[10px]">
                            <span class="pw-check-item flex items-center gap-1 text-slate-400" data-rule="len"><span class="pw-check-icon">○</span> 6+ characters</span>
                            <span class="pw-check-item flex items-center gap-1 text-slate-400" data-rule="case"><span class="pw-check-icon">○</span> Upper &amp; lower case</span>
                            <span class="pw-check-item flex items-center gap-1 text-slate-400" data-rule="num"><span class="pw-check-icon">○</span> A number</span>
                            <span class="pw-check-item flex items-center gap-1 text-slate-400" data-rule="special"><span class="pw-check-icon">○</span> A symbol</span>
                        </div>
                    </div>
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
        <div class="header-actions flex flex-wrap gap-2.5 justify-center">
            <button id="goToAdminBtn" onclick="navigateToPage('admin')" class="btn-glow hidden bg-rose-100 hover:bg-rose-600 text-rose-700 hover:text-white border border-rose-300 px-4 py-2 rounded-xl font-bold text-xs">🛡️ Admin Dashboard</button>
            <button onclick="navigateToPage('profile')" class="btn-glow bg-teal-500/10 hover:bg-teal-600 text-teal-700 hover:text-white border border-teal-300 px-4 py-2 rounded-xl font-bold text-xs">👤 Settings & Profile</button>
            <button onclick="openInbox()" class="btn-glow bg-purple-500/10 hover:bg-purple-600 text-purple-700 hover:text-white border border-purple-300 px-4 py-2 rounded-xl font-bold text-xs relative">💬 Direct Chat <span id="unreadBadge" class="hidden badge-pulse absolute -top-1.5 -right-1.5 bg-rose-500 text-white text-[9px] w-5 h-5 rounded-full flex items-center justify-center font-bold"></span></button>
            <button onclick="openInsights()" class="btn-glow bg-indigo-500/10 hover:bg-indigo-600 text-indigo-700 hover:text-white border border-indigo-300 px-4 py-2 rounded-xl font-bold text-xs">📊 Insights</button>
            <button onclick="openNotifications()" class="btn-glow bg-blue-500/10 hover:bg-blue-600 text-blue-700 hover:text-white border border-blue-300 px-4 py-2 rounded-xl font-bold text-xs relative">🔔 Alerts <span id="notifBadge" class="hidden badge-pulse absolute -top-1.5 -right-1.5 bg-rose-500 text-white text-[9px] w-5 h-5 rounded-full flex items-center justify-center font-bold"></span></button>
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

    <div id="searchMatches" class="hidden glass-card rounded-3xl p-4"></div>

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
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center justify-between">
                    <span>📢 Emergency Help Feed <span class="bg-teal-500/10 text-teal-700 text-xs px-2.5 py-0.5 border border-teal-300 rounded-full" id="globalCount">0</span></span>
                    <button onclick="refreshBoard(this)" class="btn-glow bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 px-3 py-1.5 rounded-xl text-xs font-bold">🔄 Refresh</button>
                </h3>
                <div id="requestsList" class="flex flex-col gap-4"></div>
            </div>
            <div>
                <h3 class="text-lg font-bold text-rose-600 mb-4">🩸 Real-time Blood Donors</h3>
                <div id="donorList" class="flex gap-4 overflow-x-auto pb-3"></div>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2 flex-wrap">📝 My Personal Posts <span id="totalPosts" class="bg-teal-500/10 text-teal-700 text-xs px-2.5 py-0.5 border border-teal-300 rounded-full">0</span>
                    <button onclick="window.location.href='api/export.php'" class="btn-glow bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 px-3 py-1 rounded-xl text-[11px] font-bold ml-auto">⬇️ Export CSV</button>
                </h3>
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
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center mb-2">
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 stat-pop"><span class="block text-xs font-bold text-slate-400">REVIEWS</span><span id="profileReviewCount" class="text-base font-extrabold text-teal-700">0</span></div>
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 stat-pop" style="animation-delay:.05s"><span class="block text-xs font-bold text-slate-400">AVG RATING</span><span id="profileRatingAvg" class="text-base font-extrabold text-amber-600">⭐ 0.0</span></div>
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 stat-pop" style="animation-delay:.1s"><span class="block text-xs font-bold text-slate-400">HELPED</span><span id="profileHelpCount" class="text-base font-extrabold text-emerald-600">0</span></div>
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-100 stat-pop" style="animation-delay:.15s"><span class="block text-xs font-bold text-slate-400">BADGE</span><span id="profileStatusBadge" class="text-xs font-extrabold text-slate-700 block mt-1">Member</span></div>
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
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Bio <span class="text-slate-400 font-normal normal-case">(tell the community about yourself)</span></label>
                <textarea id="editProfileBio" rows="3" maxlength="500" placeholder="e.g. Volunteer nurse, happy to help coordinate blood donations in Dhanmondi..." class="glass-input w-full p-3 rounded-xl text-sm resize-none"></textarea>
                <p class="text-[10px] text-slate-400 mt-1 text-right"><span id="bioCharCount">0</span>/500</p>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Social / Portfolio Link</label>
                <input type="text" id="editProfileSocialLink" placeholder="e.g. facebook.com/yourname" class="glass-input w-full p-3 rounded-xl text-sm">
            </div>
            <button type="submit" class="btn-glow w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-3 rounded-xl text-sm">💾 Save Profile Changes</button>
            <div class="mt-6 border-t border-slate-200 pt-4">
                <h4 class="text-xs font-bold text-teal-700 uppercase mb-3">💬 Feedback & Experiences from Others</h4>
                <div id="myReviewsFeed" class="space-y-2.5 max-h-52 overflow-y-auto pr-1"></div>
            </div>
            <div class="mt-6 border-t border-slate-200 pt-4">
                <h4 class="text-xs font-bold text-indigo-700 uppercase mb-3">🕐 My Activity Timeline</h4>
                <div id="myActivityLog" class="space-y-2 max-h-64 overflow-y-auto pr-1"></div>
            </div>
        </form>

        <div class="border-t border-slate-200 p-6">
            <h4 class="text-xs font-bold text-rose-600 uppercase mb-3">🔒 Change Password</h4>
            <form id="passwordForm" class="space-y-3">
                <input type="password" id="currentPassword" placeholder="Current password" class="glass-input w-full p-3 rounded-xl text-sm" required>
                <div class="grid grid-cols-2 gap-3">
                    <input type="password" id="newPassword" placeholder="New password" class="glass-input w-full p-3 rounded-xl text-sm" required>
                    <input type="password" id="confirmPassword" placeholder="Confirm new password" class="glass-input w-full p-3 rounded-xl text-sm" required>
                </div>
                <button type="submit" class="btn-glow w-full bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white border border-rose-200 font-bold py-2.5 rounded-xl text-sm">Update Password</button>
            </form>
        </div>

        <div class="border-t border-slate-200 p-6">
            <h4 class="text-xs font-bold text-slate-500 uppercase mb-3">🚪 Account</h4>
            <button onclick="logout()" class="btn-glow w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl text-sm">🚪 Sign Out of Account</button>
        </div>
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

        <!-- Stat overview cards -->
        <div id="adminStatsGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6"></div>

        <!-- Tab switcher -->
        <div class="flex p-1 bg-slate-100 border border-slate-200 rounded-2xl mb-5 max-w-xs">
            <button id="adminTabBtnPosts" class="admin-tab-btn active flex-1 py-2 rounded-xl font-bold text-xs" onclick="switchAdminTab('posts')">🚨 Posts</button>
            <button id="adminTabBtnUsers" class="admin-tab-btn flex-1 py-2 rounded-xl font-bold text-xs text-slate-500" onclick="switchAdminTab('users')">👥 Members</button>
        </div>

        <div id="adminTabPosts" class="admin-tab-panel">
            <div class="flex items-center justify-between gap-3 mb-3 border-b border-slate-100 pb-2">
                <h3 class="text-md font-bold text-rose-600">🚨 Reported / Public Posts (<span id="adminTotalPosts">0</span>)</h3>
                <input id="adminSearchPosts" type="text" placeholder="🔎 Search posts..." class="glass-input text-xs px-3 py-1.5 rounded-xl w-40 sm:w-56">
            </div>
            <div id="adminPostsList" class="space-y-3 max-h-[500px] overflow-y-auto pr-2"></div>
        </div>

        <div id="adminTabUsers" class="admin-tab-panel hidden">
            <div class="flex items-center justify-between gap-3 mb-3 border-b border-slate-100 pb-2">
                <h3 class="text-md font-bold text-amber-600">👥 Platform Members (<span id="adminTotalUsers">0</span>)</h3>
                <input id="adminSearchUsers" type="text" placeholder="🔎 Search members..." class="glass-input text-xs px-3 py-1.5 rounded-xl w-40 sm:w-56">
            </div>
            <div id="adminUsersList" class="space-y-3 max-h-[500px] overflow-y-auto pr-2"></div>
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
                        <span id="pubHelpCount" class="bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded text-[10px] font-bold"></span>
                    </div>
                    <a id="pubSocialLink" href="#" target="_blank" rel="noopener noreferrer" class="hidden text-[11px] text-indigo-600 font-bold hover:underline mt-2 inline-block">🔗 Visit social/portfolio link</a>
                </div>
            </div>
            <p id="pubBio" class="hidden bg-white p-3.5 rounded-2xl border border-slate-200 text-xs text-slate-600 leading-relaxed italic"></p>
            <div>
                <h4 class="text-sm font-bold text-slate-700 uppercase mb-3">📋 Activity History & Emergency Posts</h4>
                <div id="pubPostsList" class="space-y-3"></div>
            </div>
            <div>
                <h4 class="text-sm font-bold text-indigo-700 uppercase mb-3">🕐 Recent Activity Timeline</h4>
                <div id="pubActivityList" class="space-y-2 max-h-56 overflow-y-auto pr-1"></div>
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

<div id="helpersModal" class="modal bg-slate-950/40 hidden fixed inset-0 z-[1000] items-center justify-center p-4">
    <div class="glass-card w-full max-w-md rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] bg-white">
        <div class="bg-teal-50 border-b border-slate-200 p-4 flex justify-between items-center">
            <h3 class="font-bold text-md text-teal-800">🤝 কারা সাহায্য করেছেন</h3>
            <button onclick="closeModal('helpersModal')" class="text-2xl hover:bg-slate-100 w-8 h-8 rounded-full">×</button>
        </div>
        <div id="helpersList" class="flex-1 overflow-y-auto p-4 space-y-2 bg-slate-50"></div>
        <div class="p-4 bg-white border-t border-slate-200 space-y-2">
            <form id="contributeForm" class="flex gap-2">
                <input type="hidden" id="contributeRequestId">
                <input type="number" min="1" id="contributeQty" placeholder="পরিমাণ" class="glass-input w-24 p-2.5 rounded-xl text-xs" required>
                <button type="submit" class="flex-1 bg-teal-600 hover:bg-teal-500 text-white text-xs px-4 rounded-xl font-bold">আমি সাহায্য করেছি</button>
            </form>
        </div>
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

<div id="insightsModal" class="modal bg-slate-950/40 hidden fixed inset-0 z-[1000] items-center justify-center p-4">
    <div class="glass-card w-full max-w-3xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh] bg-white">
        <div class="bg-indigo-50 border-b border-slate-200 p-4 flex justify-between items-center">
            <h3 class="font-bold text-md text-indigo-700">📊 Community Insights</h3>
            <button onclick="closeModal('insightsModal')" class="text-2xl hover:bg-slate-100 w-8 h-8 rounded-full">×</button>
        </div>
        <div id="insightsBody" class="overflow-y-auto p-6 space-y-6 bg-slate-50"></div>
    </div>
</div>

<footer class="text-center py-6 text-slate-400 text-xs border-t border-slate-200 bg-slate-50">
    🛡️ Community Help Board — PHP + MySQL Edition (CSE 2208 DBMS Lab Project)
</footer>

<script src="assets/app.js"></script>
</body>
</html>
