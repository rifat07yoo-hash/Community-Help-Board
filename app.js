// =====================================================================
// Community Help Board — Frontend logic (PHP + MySQL backend)
// =====================================================================

const state = {
    user: null,
    requests: [],
    editingImageCleared: false,
    activeChatPostId: null,
    selectedRatingStars: 0,
    ratingTargetUserId: null,
    filters: { search: '', category: '', priority: '', hideResolved: false, volunteerOnly: false },
    pollTimer: null,
};

const priorityLabels = { urgent: '🔴 Urgent', high: '🟠 High', medium: '🟡 Medium' };
const categoryColors = {
    food: 'bg-emerald-100 text-emerald-700 border-emerald-300',
    blood: 'bg-rose-100 text-rose-700 border-rose-300',
    medical: 'bg-blue-100 text-blue-700 border-blue-300',
    shelter: 'bg-amber-100 text-amber-700 border-amber-300',
    other: 'bg-purple-100 text-purple-700 border-purple-300',
};

// ---------------------------------------------------------------------
// API helpers
// ---------------------------------------------------------------------
async function apiGet(url) {
    const res = await fetch(url, { credentials: 'same-origin' });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.error || 'Request failed');
    return data;
}

async function apiJson(url, method, body) {
    const res = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(body || {}),
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.error || 'Request failed');
    return data;
}

async function apiForm(url, formData) {
    const res = await fetch(url, { method: 'POST', credentials: 'same-origin', body: formData });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data.error || 'Request failed');
    return data;
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

function generateAvatarHTML(photoUrl, name, sizeClass) {
    sizeClass = sizeClass || 'w-9 h-9 text-xs';
    if (photoUrl) {
        return `<img src="${escapeHtml(photoUrl)}" class="${sizeClass.split(' ')[0]} ${sizeClass.split(' ')[1]} rounded-full border border-slate-200 shadow-sm object-cover">`;
    }
    const letter = name ? name.trim().charAt(0).toUpperCase() : 'U';
    return `<div class="${sizeClass} rounded-full bg-teal-600 text-white font-extrabold flex items-center justify-center border border-slate-200 shadow-sm uppercase">${letter}</div>`;
}

// ---------------------------------------------------------------------
// Init
// ---------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', async () => {
    wireStaticHandlers();
    try {
        const { user } = await apiGet('auth_api/session.php');
        if (user) {
            state.user = user;
            startDashboard();
        } else {
            navigateToPage('login');
        }
    } catch (e) {
        navigateToPage('login');
    }
});

function wireStaticHandlers() {
    document.getElementById('loginForm').addEventListener('submit', onLogin);
    document.getElementById('signupForm').addEventListener('submit', onSignup);
    document.getElementById('helpForm').addEventListener('submit', onSubmitRequest);
    document.getElementById('profileForm').addEventListener('submit', onUpdateProfile);
    document.getElementById('messageForm').addEventListener('submit', onSendMessage);

    document.getElementById('postImage').addEventListener('change', onPostImageChange);
    document.getElementById('profileImageInput').addEventListener('change', onProfileImageChange);

    document.getElementById('searchBox').addEventListener('input', debounce(e => { state.filters.search = e.target.value; loadRequests(); }, 350));
    document.getElementById('filterCategory').addEventListener('change', e => { state.filters.category = e.target.value; loadRequests(); });
    document.getElementById('filterPriority').addEventListener('change', e => { state.filters.priority = e.target.value; loadRequests(); });
    document.getElementById('hideResolved').addEventListener('change', e => { state.filters.hideResolved = e.target.checked; loadRequests(); });
    document.getElementById('filterVolunteerOnly').addEventListener('change', e => { state.filters.volunteerOnly = e.target.checked; loadRequests(); });
}

function debounce(fn, ms) {
    let t;
    return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), ms); };
}

// ---------------------------------------------------------------------
// Auth
// ---------------------------------------------------------------------
function showAuthTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(t => { t.classList.remove('active'); });
    document.querySelectorAll('.form-area').forEach(f => f.classList.remove('active'));
    if (tab === 'login') {
        document.querySelectorAll('.tab-btn')[0].classList.add('active');
        document.getElementById('loginArea').classList.add('active');
    } else {
        document.querySelectorAll('.tab-btn')[1].classList.add('active');
        document.getElementById('signupArea').classList.add('active');
    }
    document.getElementById('authMsg').classList.add('hidden');
}

function showAuthMsg(text, type) {
    const box = document.getElementById('authMsg');
    box.innerText = text;
    box.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'bg-green-100', 'text-green-700');
    box.classList.add(type === 'error' ? 'bg-red-100' : 'bg-green-100', type === 'error' ? 'text-red-700' : 'text-green-700');
}

async function onLogin(e) {
    e.preventDefault();
    const email = document.getElementById('loginEmail').value.trim();
    const password = document.getElementById('loginPass').value;
    try {
        const data = await apiJson('auth_api/login.php', 'POST', { email, password });
        state.user = data.user;
        startDashboard();
    } catch (err) {
        showAuthMsg(err.message, 'error');
    }
}

async function onSignup(e) {
    e.preventDefault();
    const payload = {
        name: document.getElementById('regName').value.trim(),
        phone: document.getElementById('regPhone').value.trim(),
        email: document.getElementById('regEmail').value.trim(),
        location: document.getElementById('regLocation').value.trim(),
        blood_group: document.getElementById('regBlood').value,
        is_volunteer: document.getElementById('regVolunteer').checked,
        password: document.getElementById('regPass').value,
    };
    try {
        await apiJson('auth_api/register.php', 'POST', payload);
        showAuthMsg('Registration successful! Please sign in.', 'success');
        setTimeout(() => showAuthTab('login'), 1500);
    } catch (err) {
        showAuthMsg(err.message, 'error');
    }
}

async function logout() {
    try { await apiJson('auth_api/logout.php', 'POST', {}); } catch (e) {}
    state.user = null;
    if (state.pollTimer) clearInterval(state.pollTimer);
    location.reload();
}

// ---------------------------------------------------------------------
// Navigation
// ---------------------------------------------------------------------
function navigateToPage(page) {
    document.getElementById('login-section').style.display = 'none';
    document.getElementById('dashboard-section').style.display = 'none';
    document.getElementById('profile-page-section').style.display = 'none';
    document.getElementById('admin-panel-section').style.display = 'none';

    if (page === 'login') {
        document.getElementById('login-section').style.display = 'flex';
    } else if (page === 'dashboard') {
        document.getElementById('dashboard-section').style.display = 'block';
    } else if (page === 'profile') {
        setupProfileFormValues();
        document.getElementById('profile-page-section').style.display = 'block';
    } else if (page === 'admin') {
        if (!state.user || !state.user.is_admin) { alert('Access Denied!'); navigateToPage('dashboard'); return; }
        document.getElementById('admin-panel-section').style.display = 'block';
        loadAdminPanel();
    }
}

// ---------------------------------------------------------------------
// Dashboard bootstrap
// ---------------------------------------------------------------------
function startDashboard() {
    navigateToPage('dashboard');

    document.getElementById('profileName').innerText = state.user.name;
    document.getElementById('profileLocation').innerText = state.user.location || '';
    document.getElementById('profileBloodBadge').innerText = `🩸 ${state.user.blood_group || 'N/A'}`;
    document.getElementById('avatarContainer').innerHTML = generateAvatarHTML(state.user.profile_image, state.user.name, 'w-16 h-16 text-xl');
    document.getElementById('volunteerBadge').classList.toggle('hidden', !state.user.is_volunteer);
    document.getElementById('adminBadge').classList.toggle('hidden', !state.user.is_admin);
    document.getElementById('goToAdminBtn').classList.toggle('hidden', !state.user.is_admin);

    loadRequests();
    loadDonors();
    loadNotifications();
    loadUnreadCount();

    if (state.pollTimer) clearInterval(state.pollTimer);
    state.pollTimer = setInterval(() => {
        loadRequests();
        loadNotifications();
        loadUnreadCount();
    }, 10000);
}

// ---------------------------------------------------------------------
// Requests: load + render
// ---------------------------------------------------------------------
async function loadRequests() {
    const params = new URLSearchParams();
    if (state.filters.search) params.set('search', state.filters.search);
    if (state.filters.category) params.set('category', state.filters.category);
    if (state.filters.priority) params.set('priority', state.filters.priority);
    if (state.filters.hideResolved) params.set('hide_resolved', '1');
    if (state.filters.volunteerOnly) params.set('volunteer_only', '1');

    try {
        const data = await apiGet('api/requests.php?' + params.toString());
        state.requests = data.requests;
        renderRequests();
    } catch (e) { /* silent fail on poll */ }
}

function renderRequests() {
    const rList = document.getElementById('requestsList');
    const mList = document.getElementById('myRequestsList');
    rList.innerHTML = '';
    mList.innerHTML = '';

    let myCount = 0;

    state.requests.forEach(r => {
        const cardHTML = buildRequestCard(r);
        rList.insertAdjacentHTML('beforeend', cardHTML);
        if (r.user_id === state.user.id) {
            myCount++;
            mList.insertAdjacentHTML('beforeend', cardHTML);
        }
    });

    document.getElementById('globalCount').innerText = state.requests.length;
    document.getElementById('totalPosts').innerText = myCount;

    // wire per-card comment forms (delegation avoided for simplicity; re-attach each render)
    document.querySelectorAll('form[data-comment-form]').forEach(f => {
        f.addEventListener('submit', onSubmitComment);
    });
}

function buildRequestCard(r) {
    const target = parseInt(r.target_qty) || 1;
    const collected = parseInt(r.collected_qty) || 0;
    const percentage = Math.min(Math.round((collected / target) * 100), 100);
    const isOwner = r.user_id === state.user.id;
    const avatarHTML = generateAvatarHTML(r.user_avatar, r.user_name, 'w-10 h-10 text-xs');
    const when = new Date(r.created_at).toLocaleString();

    return `
    <div class="glass-card p-5 rounded-3xl border-t-4 ${r.resolved ? 'border-emerald-500 opacity-80' : 'border-teal-500'} bg-white">
        <div class="flex justify-between items-start gap-2 mb-3">
            <div>
                <span class="px-2.5 py-1 text-[10px] rounded-lg font-bold uppercase border ${categoryColors[r.category]}">${r.category}</span>
                <span class="text-[10px] font-extrabold text-slate-500 mt-1.5 block">${priorityLabels[r.priority]}</span>
            </div>
            <div class="flex flex-col items-end gap-1.5">
                <div class="flex gap-1.5">
                    ${r.resolved ? '<span class="bg-emerald-100 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full font-bold text-[9px]">Resolved</span>' : ''}
                    ${r.message_count > 0 ? `<span class="bg-purple-100 text-purple-700 border border-purple-200 px-2 py-0.5 rounded-full font-bold text-[9px]">${r.message_count} Messages</span>` : ''}
                    ${r.unread_count > 0 ? '<span class="bg-rose-600 text-white px-2 py-0.5 rounded-full font-bold text-[9px]">New</span>' : ''}
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
            <div class="flex items-center gap-3">
                <div class="cursor-pointer" onclick="viewPublicProfile(${r.user_id})">${avatarHTML}</div>
                <div>
                    <h4 class="font-bold text-slate-800 text-sm leading-tight cursor-pointer hover:underline hover:text-teal-600" onclick="viewPublicProfile(${r.user_id})">${escapeHtml(r.user_name)}</h4>
                    <p class="text-[9px] text-slate-400">${when}</p>
                </div>
            </div>
            ${!isOwner ? `<button onclick="reportPost(${r.id})" class="btn-glow text-[10px] text-rose-600 hover:text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-xl font-bold">⚠️ Report${r.reported_by_me ? 'ed' : ''}</button>` : ''}
        </div>

        <p class="text-xs text-teal-600 font-bold mb-2">📍 ${escapeHtml(r.location)}</p>
        ${r.image ? `<img src="${escapeHtml(r.image)}" class="w-full h-44 object-cover rounded-2xl my-2.5 border border-slate-200">` : ''}
        <p class="text-xs text-slate-600 my-3 leading-relaxed">${escapeHtml(r.description)}</p>

        <div class="mb-4 bg-slate-100 rounded-full h-4.5 relative overflow-hidden">
            <div class="bg-teal-600 h-full transition-all duration-500" style="width:${percentage}%"></div>
            <span class="absolute inset-0 flex items-center justify-center text-[10px] font-extrabold text-slate-700">${collected} / ${target} (${percentage}%)</span>
        </div>

        <div class="bg-slate-50 p-2.5 rounded-xl text-teal-700 font-bold text-xs mb-4 border border-slate-200">📞 Coordinator: ${escapeHtml(r.contact)}</div>

        <div class="flex flex-wrap gap-2 mb-4">
            ${!isOwner || r.message_count > 0 ? `<button onclick="openMessageModal(${r.id})" class="btn-glow bg-purple-600 hover:bg-purple-500 text-white px-3 py-1.5 rounded-xl text-xs font-bold">💬 Message Chat</button>` : ''}
            <button onclick="toggleMap(this, '${encodeURIComponent(r.location)}')" class="btn-glow bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-xl text-xs border border-slate-200 font-bold">🗺️ Map</button>
            <button onclick="navigateToLocation('${encodeURIComponent(r.location)}')" class="btn-glow bg-teal-600 hover:bg-teal-500 text-white px-3 py-1.5 rounded-xl text-xs font-bold">🧭 Direct GPS</button>
            <button onclick="sharePost(${JSON.stringify(r.description)}, '${encodeURIComponent(r.location)}')" class="btn-glow bg-slate-100 text-slate-600 px-3 py-1.5 rounded-xl text-xs border border-slate-200 font-bold">🔗 Copy Share Info</button>
            ${isOwner ? `
                <button onclick="editPost(${r.id})" class="btn-glow bg-amber-100 text-amber-700 border border-amber-200 px-3 py-1.5 rounded-xl text-xs font-bold hover:bg-amber-600 hover:text-white">Edit</button>
                <button onclick="toggleResolve(${r.id})" class="btn-glow bg-emerald-600 text-white px-3 py-1.5 rounded-xl text-xs font-bold">${r.resolved ? 'Reopen Request' : 'Resolve Request'}</button>
                <button onclick="deletePost(${r.id})" class="btn-glow bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white px-3 py-1.5 border border-rose-200 rounded-xl text-xs font-bold">Delete</button>
            ` : ''}
        </div>
        <div class="map-container hidden transition-all duration-300"></div>

        <div class="border-t border-slate-100 pt-4 mt-4 space-y-3">
            <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider">💬 Discussion & Updates (${r.comment_count})</h5>
            <div id="comments-${r.id}" class="space-y-2 max-h-40 overflow-y-auto pr-1">
                <p class="text-[11px] text-slate-400 italic">Loading comments…</p>
            </div>
            <form data-comment-form data-request-id="${r.id}" class="flex gap-2 pt-1.5">
                <input type="text" placeholder="Write a public comment or update..." class="glass-input flex-1 p-2.5 rounded-xl text-xs" required>
                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white text-xs px-4 rounded-xl font-bold">Comment</button>
            </form>
        </div>
    </div>`;
}

// Lazy-load comments after render (keeps initial card render fast)
function loadCommentsFor(requestId) {
    apiGet('api/comments.php?request_id=' + requestId).then(data => {
        document.querySelectorAll(`#comments-${requestId}`).forEach(el => {
            el.innerHTML = renderCommentsHTML(requestId, data.comments);
        });
    }).catch(() => {});
}

function renderCommentsHTML(requestId, comments) {
    if (!comments.length) return '<p class="text-[11px] text-slate-400 italic">No comments yet. Start the conversation!</p>';
    return comments.map(c => {
        const isOwner = c.user_id === state.user.id;
        return `
        <div class="bg-slate-100 p-2.5 rounded-xl text-xs border border-slate-200/60">
            <div class="flex justify-between items-center mb-1">
                <span class="font-bold text-slate-700 cursor-pointer hover:underline" onclick="viewPublicProfile(${c.user_id})">${escapeHtml(c.author)}</span>
                <div class="flex items-center gap-2">
                    <span class="text-[9px] text-slate-400">${new Date(c.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})}</span>
                    ${isOwner ? `
                        <button onclick="editComment(${c.id}, ${requestId}, ${JSON.stringify(c.text)})" class="text-[10px] text-amber-600 font-bold">Edit</button>
                        <button onclick="deleteComment(${c.id}, ${requestId})" class="text-[10px] text-rose-600 font-bold">Delete</button>
                    ` : ''}
                </div>
            </div>
            <p class="text-slate-600 break-words">${escapeHtml(c.text)}</p>
        </div>`;
    }).join('');
}

async function onSubmitComment(e) {
    e.preventDefault();
    const form = e.target;
    const requestId = form.dataset.requestId;
    const input = form.querySelector('input');
    const text = input.value.trim();
    if (!text) return;
    try {
        await apiJson('api/comments.php', 'POST', { request_id: parseInt(requestId), text });
        input.value = '';
        loadCommentsFor(requestId);
        bumpCommentCount(requestId, 1);
    } catch (err) { alert(err.message); }
}

function bumpCommentCount(requestId, delta) {
    const r = state.requests.find(x => String(x.id) === String(requestId));
    if (r) r.comment_count = Math.max(0, (r.comment_count || 0) + delta);
}

async function editComment(id, requestId, currentText) {
    const newText = prompt('Edit your comment:', currentText);
    if (newText === null) return;
    const trimmed = newText.trim();
    if (!trimmed) { alert('Comment cannot be empty!'); return; }
    try {
        await apiJson('api/comments.php', 'PUT', { id, text: trimmed });
        loadCommentsFor(requestId);
    } catch (err) { alert(err.message); }
}

async function deleteComment(id, requestId) {
    if (!confirm('Are you sure you want to delete this comment?')) return;
    try {
        await fetch('api/comments.php?id=' + id, { method: 'DELETE', credentials: 'same-origin' });
        loadCommentsFor(requestId);
        bumpCommentCount(requestId, -1);
    } catch (err) { alert('Error deleting comment.'); }
}

// After every render, kick off comment loads (called from a MutationObserver-free hook)
const originalRenderRequests = renderRequests;
renderRequests = function () {
    originalRenderRequests();
    state.requests.forEach(r => loadCommentsFor(r.id));
};

// ---------------------------------------------------------------------
// Create / Edit / Delete / Resolve request
// ---------------------------------------------------------------------
function onPostImageChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = ev => {
        document.getElementById('imagePreview').src = ev.target.result;
        document.getElementById('imagePreviewContainer').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

function clearImageInput() {
    document.getElementById('postImage').value = '';
    document.getElementById('imagePreviewContainer').classList.add('hidden');
    state.editingImageCleared = true;
}

function getCurrentLocation() {
    if (!navigator.geolocation) { alert('GPS Geolocation unsupported by this browser.'); return; }
    navigator.geolocation.getCurrentPosition(pos => {
        document.getElementById('userLocation').value = `${pos.coords.latitude.toFixed(5)}, ${pos.coords.longitude.toFixed(5)}`;
    }, () => alert('Geolocation disabled. Enter location manually.'));
}

async function onSubmitRequest(e) {
    e.preventDefault();
    const id = document.getElementById('editId').value;
    const fd = new FormData();
    fd.append('action', id ? 'update' : 'create');
    if (id) fd.append('id', id);
    fd.append('category', document.getElementById('category').value);
    fd.append('priority', document.getElementById('priority').value);
    fd.append('location', document.getElementById('userLocation').value);
    fd.append('contact', document.getElementById('contact').value);
    fd.append('description', document.getElementById('description').value);
    fd.append('target_qty', document.getElementById('targetQty').value || 1);
    fd.append('collected_qty', document.getElementById('collectedQty').value || 0);
    const fileInput = document.getElementById('postImage');
    if (fileInput.files[0]) fd.append('image', fileInput.files[0]);

    try {
        await apiForm('api/requests.php', fd);
        resetForm();
        loadRequests();
    } catch (err) { alert(err.message); }
}

function editPost(id) {
    const r = state.requests.find(x => x.id === id);
    if (!r) return;
    document.getElementById('editId').value = id;
    document.getElementById('category').value = r.category;
    document.getElementById('priority').value = r.priority;
    document.getElementById('userLocation').value = r.location;
    document.getElementById('contact').value = r.contact;
    document.getElementById('description').value = r.description;
    document.getElementById('targetQty').value = r.target_qty;
    document.getElementById('collectedQty').value = r.collected_qty;

    if (r.image) {
        document.getElementById('imagePreview').src = r.image;
        document.getElementById('imagePreviewContainer').classList.remove('hidden');
    } else {
        document.getElementById('imagePreviewContainer').classList.add('hidden');
    }

    document.getElementById('formTitle').innerText = 'Update Help Request Info';
    document.getElementById('submitBtn').innerText = 'Apply Information Changes';
    document.getElementById('cancelEditBtn').classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('helpForm').reset();
    document.getElementById('editId').value = '';
    document.getElementById('formTitle').innerText = 'Post Emergency Request';
    document.getElementById('submitBtn').innerText = 'Post Help Request';
    document.getElementById('cancelEditBtn').classList.add('hidden');
    document.getElementById('imagePreviewContainer').classList.add('hidden');
}

async function toggleResolve(id) {
    try { await apiForm('api/requests.php', formDataFrom({ action: 'resolve', id })); loadRequests(); }
    catch (err) { alert(err.message); }
}

async function deletePost(id) {
    if (!confirm('Discard this post permanently?')) return;
    try { await apiForm('api/requests.php', formDataFrom({ action: 'delete', id })); loadRequests(); }
    catch (err) { alert(err.message); }
}

async function reportPost(id) {
    try {
        await apiJson('api/reports.php', 'POST', { request_id: id });
        alert('Post successfully reported.');
        loadRequests();
    } catch (err) { alert(err.message); }
}

function formDataFrom(obj) {
    const fd = new FormData();
    Object.entries(obj).forEach(([k, v]) => fd.append(k, v));
    return fd;
}

// ---------------------------------------------------------------------
// Map / GPS / Share helpers
// ---------------------------------------------------------------------
function toggleMap(btn, loc) {
    const container = btn.parentElement.nextElementSibling;
    if (container.innerHTML === '') {
        const mapUrl = 'https://maps.google.com/maps?q=' + loc + '&t=&z=14&ie=UTF8&iwloc=&output=embed';
        container.innerHTML = `<iframe class="w-full h-44 rounded-2xl mt-3 border border-slate-200 shadow-inner" src="${mapUrl}"></iframe>`;
    }
    container.classList.toggle('hidden');
}

function navigateToLocation(loc) {
    window.open('https://www.google.com/maps/search/?api=1&query=' + loc, '_blank');
}

function sharePost(desc, loc) {
    const text = `Community Help Urgent Alert!\nRequirement: ${desc}\nArea details: ${decodeURIComponent(loc)}`;
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => alert('Copied to clipboard!')).catch(() => fallbackCopy(text));
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    const ta = document.createElement('textarea');
    ta.value = text; ta.style.position = 'fixed'; ta.style.left = '-9999px';
    document.body.appendChild(ta); ta.focus(); ta.select();
    try { document.execCommand('copy'); alert('Copied to clipboard!'); } catch (e) { alert('Unable to copy automatically.'); }
    document.body.removeChild(ta);
}

// ---------------------------------------------------------------------
// Donors
// ---------------------------------------------------------------------
async function loadDonors() {
    try {
        const { donors } = await apiGet('api/donors.php');
        const el = document.getElementById('donorList');
        el.innerHTML = '';
        if (!donors.length) { el.innerHTML = '<p class="text-xs text-slate-500">No donor registered yet.</p>'; return; }
        donors.forEach(u => {
            const avatarHTML = generateAvatarHTML(u.profile_image, u.name, 'w-11 h-11 text-xs');
            const ratingStr = u.rating_count > 0 ? `⭐ ${u.rating_average}` : '⭐ No Rating';
            el.insertAdjacentHTML('beforeend', `
                <div class="glass-card min-w-[280px] p-4 rounded-2xl flex items-center gap-3.5 border-l-4 border-rose-500 bg-white shadow-sm">
                    <div class="cursor-pointer" onclick="viewPublicProfile(${u.id})">${avatarHTML}</div>
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-800 text-sm cursor-pointer hover:underline hover:text-teal-600" onclick="viewPublicProfile(${u.id})">${escapeHtml(u.name)}</h4>
                        <p class="text-[10px] text-slate-500">📍 ${escapeHtml(u.location || '')}</p>
                        <div class="flex gap-2 items-center mt-1">
                            <span class="bg-rose-100 text-rose-700 border border-rose-200 px-1.5 py-0.5 rounded text-[10px] font-bold">🩸 Group: ${u.blood_group}</span>
                            <span class="text-[10px] text-amber-600 font-bold">${ratingStr}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <a href="tel:${escapeHtml(u.phone || '')}" class="btn-glow bg-teal-600 hover:bg-teal-500 text-white px-2.5 py-1.5 rounded-xl font-bold text-xs text-center">📞 Call</a>
                        <button onclick="triggerRatingModal(${u.id})" class="btn-glow bg-amber-100 hover:bg-amber-600 text-amber-700 hover:text-white border border-amber-200 px-2 py-1 rounded-xl text-[10px] font-bold">Review</button>
                    </div>
                </div>`);
        });
    } catch (e) {}
}

// ---------------------------------------------------------------------
// Public profile modal
// ---------------------------------------------------------------------
async function viewPublicProfile(userId) {
    try {
        const { profile, posts, rating_average, rating_count } = await apiGet('api/profile.php?id=' + userId);
        document.getElementById('pubName').innerText = profile.name;
        document.getElementById('pubLocation').innerText = `📍 ${profile.location || 'N/A'}`;
        document.getElementById('pubBlood').innerText = `🩸 Group: ${profile.blood_group || 'N/A'}`;
        document.getElementById('pubRating').innerText = rating_count > 0 ? `⭐ ${rating_average} (${rating_count} reviews)` : '⭐ No Ratings Yet';
        document.getElementById('pubAvatarContainer').innerHTML = generateAvatarHTML(profile.profile_image, profile.name, 'w-20 h-20 text-2xl');

        const listContainer = document.getElementById('pubPostsList');
        listContainer.innerHTML = posts.length ? posts.map(p => `
            <div class="bg-white p-3 rounded-xl border border-slate-200 text-xs shadow-sm">
                <div class="flex justify-between font-bold text-teal-700 mb-1">
                    <span>${p.category.toUpperCase()} (${priorityLabels[p.priority]})</span>
                    <span class="text-slate-400 font-normal text-[10px]">${new Date(p.created_at).toLocaleDateString()}</span>
                </div>
                <p class="text-slate-600 font-medium mb-1">📍 ${escapeHtml(p.location)}</p>
                <p class="text-slate-500 truncate">${escapeHtml(p.description)}</p>
            </div>`).join('') : '<p class="text-xs text-slate-400 italic">No public emergency posts on record.</p>';

        openModal('publicProfileModal');
    } catch (e) { alert('Could not load profile.'); }
}

function openModal(id) { document.getElementById(id).classList.remove('hidden'); document.getElementById(id).classList.add('flex'); }
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
    if (id === 'messageModal') state.activeChatPostId = null;
}

// ---------------------------------------------------------------------
// Messages / Chat
// ---------------------------------------------------------------------
async function openMessageModal(requestId) {
    state.activeChatPostId = requestId;
    try {
        const { request, messages } = await apiGet('api/messages.php?request_id=' + requestId);
        document.getElementById('messagePostId').value = requestId;
        document.getElementById('messagePostInfo').innerText = `${request.category.toUpperCase()} at ${request.location}`;

        const thread = document.getElementById('messageThread');
        thread.innerHTML = messages.map(m => {
            const isMine = m.sender_id === state.user.id;
            const align = isMine ? 'ml-auto bg-teal-600 text-white rounded-br-none' : 'mr-auto bg-slate-200 text-slate-800 rounded-bl-none';
            return `
            <div class="p-3 rounded-2xl max-w-[85%] ${align} shadow-sm text-xs">
                <div class="flex justify-between items-center gap-4 mb-1">
                    <span class="font-extrabold text-[9px] opacity-60">${escapeHtml(m.sender_name)}</span>
                    ${isMine ? `<button onclick="deleteChatMessage(${m.id}, ${requestId})" class="text-[10px] text-rose-300 hover:text-rose-100 font-bold ml-auto">✕</button>` : ''}
                </div>
                <p class="break-words">${escapeHtml(m.text)}</p>
                <p class="text-[8px] text-right mt-1 opacity-40">${new Date(m.created_at).toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'})} ${m.is_read ? '✔✔' : '✔'}</p>
            </div>`;
        }).join('');

        openModal('messageModal');
        loadUnreadCount();
        loadRequests();
    } catch (e) { alert(e.message); }
}

async function onSendMessage(e) {
    e.preventDefault();
    const requestId = parseInt(document.getElementById('messagePostId').value);
    const text = document.getElementById('messageText').value.trim();
    if (!text) return;
    try {
        await apiJson('api/messages.php', 'POST', { request_id: requestId, text });
        document.getElementById('messageText').value = '';
        openMessageModal(requestId);
    } catch (err) { alert(err.message); }
}

async function deleteChatMessage(id, requestId) {
    if (!confirm('Are you sure you want to delete this message?')) return;
    try {
        await fetch('api/messages.php?id=' + id, { method: 'DELETE', credentials: 'same-origin' });
        openMessageModal(requestId);
    } catch (e) { alert('Error deleting message.'); }
}

async function openInbox() {
    try {
        const { threads } = await apiGet('api/messages.php?inbox=1');
        const el = document.getElementById('inboxList');
        el.innerHTML = threads.length ? '' : '<p class="text-center text-xs text-slate-500 p-6">No communication matches on file.</p>';
        threads.forEach(t => {
            const unread = t.unread_count > 0;
            el.insertAdjacentHTML('beforeend', `
                <div onclick="openMessageModal(${t.request_id}); closeModal('inboxModal');" class="p-4 rounded-2xl border cursor-pointer hover:bg-slate-100 ${unread ? 'bg-purple-50 border-purple-300' : 'bg-slate-50 border-slate-200'}">
                    <p class="font-bold text-teal-600 text-xs flex items-center justify-between">
                        <span>${escapeHtml(t.owner_name)} — ${escapeHtml(t.location)}</span>
                        ${unread ? '<span class="bg-rose-500 w-2.5 h-2.5 rounded-full inline-block shadow"></span>' : ''}
                    </p>
                    <p class="text-xs truncate text-slate-600 mt-1">${escapeHtml(t.last_message || '')}</p>
                </div>`);
        });
        openModal('inboxModal');
    } catch (e) { alert('Could not load inbox.'); }
}

async function loadUnreadCount() {
    try {
        const { threads } = await apiGet('api/messages.php?inbox=1');
        const count = threads.reduce((sum, t) => sum + (t.unread_count || 0), 0);
        const badge = document.getElementById('unreadBadge');
        if (count > 0) { badge.classList.remove('hidden'); badge.innerText = count; } else { badge.classList.add('hidden'); }
    } catch (e) {}
}

// ---------------------------------------------------------------------
// Notifications
// ---------------------------------------------------------------------
async function loadNotifications() {
    try {
        const { notifications } = await apiGet('api/notifications.php');
        const badge = document.getElementById('notifBadge');
        const unread = notifications.filter(n => !n.is_read).length;
        if (unread > 0) { badge.classList.remove('hidden'); badge.innerText = unread; } else { badge.classList.add('hidden'); }
        window._latestNotifications = notifications;
    } catch (e) {}
}

function openNotifications() {
    const list = document.getElementById('notifList');
    const notifications = window._latestNotifications || [];
    list.innerHTML = notifications.length ? notifications.map(n => `
        <div class="p-3 bg-slate-50 border-l-4 border-teal-500 rounded-xl text-xs">
            <p class="font-medium text-slate-700">${escapeHtml(n.text)}</p>
            <span class="text-[9px] text-slate-400 block mt-1">${new Date(n.created_at).toLocaleString()}</span>
        </div>`).join('') : '<p class="text-center text-xs text-slate-500 p-6">Your Notification logs are fully cleared.</p>';
    apiJson('api/notifications.php', 'PUT', {}).catch(() => {});
    openModal('notifModal');
}

async function clearNotifications() {
    try {
        await fetch('api/notifications.php', { method: 'DELETE', credentials: 'same-origin' });
        window._latestNotifications = [];
        closeModal('notifModal');
        loadNotifications();
    } catch (e) {}
}

// ---------------------------------------------------------------------
// Ratings
// ---------------------------------------------------------------------
function triggerRatingModal(targetUserId) {
    if (targetUserId === state.user.id) { alert('You cannot rate yourself!'); return; }
    state.ratingTargetUserId = targetUserId;
    state.selectedRatingStars = 0;
    document.getElementById('ratingReviewText').value = '';
    setRatingValue(0);
    openModal('ratingModal');
}

function setRatingValue(val) {
    state.selectedRatingStars = val;
    const stars = document.getElementById('starRatingSelector').children;
    for (let i = 0; i < stars.length; i++) {
        if (i < val) { stars[i].innerText = '★'; stars[i].classList.add('text-amber-500'); stars[i].classList.remove('text-slate-300'); }
        else { stars[i].innerText = '☆'; stars[i].classList.add('text-slate-300'); stars[i].classList.remove('text-amber-500'); }
    }
}

async function submitRating() {
    if (state.selectedRatingStars === 0) { alert('Please select at least 1 star!'); return; }
    const review = document.getElementById('ratingReviewText').value.trim();
    try {
        await apiJson('api/ratings.php', 'POST', { target_user_id: state.ratingTargetUserId, score: state.selectedRatingStars, review });
        alert('Your experience rating has been saved!');
        closeModal('ratingModal');
        loadDonors();
    } catch (err) { alert(err.message); }
}

// ---------------------------------------------------------------------
// Profile page
// ---------------------------------------------------------------------
function setupProfileFormValues() {
    document.getElementById('editProfileName').value = state.user.name;
    document.getElementById('editProfileLocation').value = state.user.location || '';
    document.getElementById('editProfilePhone').value = state.user.phone || '';
    document.getElementById('editProfileBlood').value = state.user.blood_group || 'Unknown';
    document.getElementById('editAvatarContainer').innerHTML = generateAvatarHTML(state.user.profile_image, state.user.name, 'w-24 h-24 text-3xl');

    const badgeEl = document.getElementById('profileStatusBadge');
    if (state.user.is_admin) { badgeEl.innerText = 'Admin 🛡️'; badgeEl.className = 'text-xs font-extrabold text-red-600 block mt-1'; }
    else if (state.user.is_volunteer) { badgeEl.innerText = 'Volunteer 🌟'; badgeEl.className = 'text-xs font-extrabold text-amber-600 block mt-1'; }
    else { badgeEl.innerText = 'Member 👤'; badgeEl.className = 'text-xs font-extrabold text-slate-500 block mt-1'; }

    apiGet('api/ratings.php?user_id=' + state.user.id).then(({ ratings, average, count }) => {
        document.getElementById('profileReviewCount').innerText = count;
        document.getElementById('profileRatingAvg').innerText = `⭐ ${average}`;
        const feed = document.getElementById('myReviewsFeed');
        feed.innerHTML = ratings.length ? ratings.map(r => `
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
                <div class="flex justify-between items-center mb-1">
                    <span class="font-bold text-slate-700">${escapeHtml(r.reviewer_name)}</span>
                    <span class="text-amber-500 font-bold">${'⭐'.repeat(r.score)}</span>
                </div>
                <p class="text-slate-600 italic">"${escapeHtml(r.review || 'Highly reliable community member.')}"</p>
            </div>`).join('') : '<p class="text-xs text-slate-400 italic">You don\'t have any reviews yet.</p>';
    }).catch(() => {});
}

let pendingProfileImage = null;
function onProfileImageChange(e) {
    const file = e.target.files[0];
    if (!file) return;
    pendingProfileImage = file;
    const reader = new FileReader();
    reader.onload = ev => { document.getElementById('editAvatarContainer').innerHTML = `<img src="${ev.target.result}" class="w-24 h-24 rounded-full border border-slate-200 object-cover">`; };
    reader.readAsDataURL(file);
}

async function onUpdateProfile(e) {
    e.preventDefault();
    const fd = new FormData();
    fd.append('name', document.getElementById('editProfileName').value.trim());
    fd.append('location', document.getElementById('editProfileLocation').value.trim());
    fd.append('phone', document.getElementById('editProfilePhone').value.trim());
    fd.append('blood_group', document.getElementById('editProfileBlood').value);
    if (pendingProfileImage) fd.append('profile_image', pendingProfileImage);

    try {
        await apiForm('api/profile.php', fd);
        const { user } = await apiGet('auth_api/session.php');
        state.user = user;
        pendingProfileImage = null;
        alert('Profile successfully updated!');
        navigateToPage('dashboard');
        startDashboard();
    } catch (err) { alert(err.message); }
}

// ---------------------------------------------------------------------
// Admin
// ---------------------------------------------------------------------
async function loadAdminPanel() {
    try {
        const { posts, users } = await apiGet('api/admin.php');
        document.getElementById('adminTotalPosts').innerText = posts.length;
        document.getElementById('adminPostsList').innerHTML = posts.map(p => `
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col sm:flex-row justify-between gap-3 text-xs">
                <div class="truncate max-w-[70%]">
                    <p class="font-bold text-slate-800 flex items-center gap-1.5">${escapeHtml(p.user_name)} <span class="bg-rose-100 text-rose-700 px-2 py-0.5 rounded text-[9px] font-black uppercase">Reports: ${p.report_count}</span></p>
                    <p class="text-[10px] text-slate-500 mt-1 truncate">${escapeHtml(p.description)}</p>
                </div>
                <div class="flex gap-2 self-center">
                    <button onclick="adminDeletePost(${p.id})" class="btn-glow bg-rose-600 hover:bg-rose-500 text-white px-3 py-1.5 rounded-xl font-bold">Delete Post</button>
                    ${p.report_count > 0 ? `<button onclick="dismissReports(${p.id})" class="btn-glow bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-1.5 rounded-xl font-bold">Clear Flags</button>` : ''}
                </div>
            </div>`).join('');

        document.getElementById('adminTotalUsers').innerText = users.length;
        document.getElementById('adminUsersList').innerHTML = users.map(u => `
            <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col sm:flex-row justify-between gap-3 text-xs">
                <div>
                    <p class="font-bold text-slate-800 flex items-center gap-1.5">${escapeHtml(u.name)}
                        ${u.is_admin ? '<span class="text-[9px] bg-teal-100 text-teal-700 border border-teal-300 px-1.5 py-0.5 rounded font-bold">Admin</span>' : ''}
                        ${u.is_volunteer ? '<span class="text-[9px] bg-amber-100 text-amber-700 border border-amber-300 px-1.5 py-0.5 rounded font-bold">Volunteer</span>' : ''}
                        ${u.is_banned ? '<span class="text-[9px] bg-rose-100 text-rose-700 border border-rose-300 px-1.5 py-0.5 rounded font-bold uppercase">Banned</span>' : ''}
                    </p>
                    <p class="text-[10px] text-slate-500 mt-1">✉️ ${escapeHtml(u.email)} | 📞 ${escapeHtml(u.phone || 'N/A')}</p>
                </div>
                <div class="flex gap-2 self-center">
                    ${!u.is_admin ? `
                        <button onclick="toggleBanUser(${u.id}, ${u.is_banned ? 1 : 0})" class="btn-glow ${u.is_banned ? 'bg-teal-600 text-white' : 'bg-rose-100 text-rose-700 hover:bg-rose-600 hover:text-white border border-rose-200'} px-2.5 py-1.5 rounded-xl font-bold">${u.is_banned ? 'Unban User' : 'Ban User'}</button>
                        <button onclick="adminDeleteUser(${u.id})" class="btn-glow bg-rose-600 hover:bg-rose-500 text-white px-2.5 py-1.5 rounded-xl font-bold">Delete</button>
                    ` : ''}
                </div>
            </div>`).join('');
    } catch (e) { alert(e.message); }
}

async function adminDeletePost(id) {
    if (!confirm('🚨 CRITICAL ACTION: Remove this request post permanently?')) return;
    try { await apiJson('api/admin.php', 'POST', { action: 'delete_post', request_id: id }); loadAdminPanel(); }
    catch (err) { alert(err.message); }
}

async function dismissReports(id) {
    if (!confirm('Dismiss all warnings and clear reported flags for this post?')) return;
    try { await apiJson('api/admin.php', 'POST', { action: 'clear_reports', request_id: id }); loadAdminPanel(); }
    catch (err) { alert(err.message); }
}

async function toggleBanUser(userId, currentBanned) {
    const actionText = currentBanned ? 'Unban' : 'Ban';
    if (!confirm(`🚨 Are you sure you want to ${actionText} this user?`)) return;
    try {
        await apiJson('api/admin.php', 'POST', { action: currentBanned ? 'unban_user' : 'ban_user', user_id: userId });
        loadAdminPanel();
    } catch (err) { alert(err.message); }
}

async function adminDeleteUser(userId) {
    if (!confirm('⚠️ CRITICAL SECURITY WARNING: Wipe this user profile?')) return;
    try { await apiJson('api/admin.php', 'POST', { action: 'delete_user', user_id: userId }); loadAdminPanel(); }
    catch (err) { alert(err.message); }
}
