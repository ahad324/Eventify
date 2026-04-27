import { API } from './api.js';

export async function initAdminLayout() {
    // Check Auth
    try {
        const admin = await API.get('admin/me');
        if (!admin || !admin.id) throw new Error();
    } catch (err) {
        // Only redirect if not already on login page
        if (!window.location.pathname.endsWith('admin/index.html')) {
            window.location.href = 'index.html';
        }
        return;
    }

    const sidebar = document.querySelector('.sidebar');
    if (!sidebar) return;

    // Remove any existing toggle to avoid duplicates
    const existingToggle = document.querySelector('.mobile-toggle');
    if (existingToggle) existingToggle.remove();

    const toggle = document.createElement('button');
    toggle.className = 'mobile-toggle';
    toggle.id = 'menu-toggle';
    toggle.innerHTML = 'MENU';
    document.body.appendChild(toggle);

    toggle.onclick = () => {
        sidebar.classList.toggle('active');
        toggle.innerHTML = sidebar.classList.contains('active') ? 'CLOSE' : 'MENU';
    };

    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.onclick = async (e) => {
            e.preventDefault();
            try {
                await API.get('admin/logout');
            } catch (err) {
                // Logout failed
            }
            window.location.href = 'index.html';
        };
    }

    // Add tagline if not present
    const brand = document.querySelector('.nav-brand-container');
    if (brand && !brand.querySelector('.nav-tagline')) {
        const tagline = document.createElement('span');
        tagline.className = 'nav-tagline';
        tagline.textContent = 'Objective Event Management System';
        brand.appendChild(tagline);
    }
}
