// ============================================================
// MAKEUP STUDIO — Main JavaScript
// ============================================================

document.addEventListener('DOMContentLoaded', () => {

    // --- Sticky header on scroll ---
    const header = document.getElementById('site-header');
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 40);
        }, { passive: true });
    }

    // --- Hamburger menu ---
    const hamburger = document.getElementById('hamburger');
    const mainNav   = document.getElementById('main-nav');
    if (hamburger && mainNav) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('open');
            mainNav.classList.toggle('open');
        });
        // Mobile: tap nav items with dropdown to toggle
        mainNav.querySelectorAll('.has-dropdown > .nav-link').forEach(link => {
            link.addEventListener('click', e => {
                if (window.innerWidth < 768) {
                    e.preventDefault();
                    link.closest('.has-dropdown').classList.toggle('open');
                }
            });
        });
        // Close on outside click
        document.addEventListener('click', e => {
            if (!hamburger.contains(e.target) && !mainNav.contains(e.target)) {
                hamburger.classList.remove('open');
                mainNav.classList.remove('open');
            }
        });
    }

    // --- Cart badge update ---
    updateCartBadge();

    // --- Brand strip duplicate for infinite scroll ---
    const stripInner = document.querySelector('.brands-strip-inner');
    if (stripInner) {
        stripInner.innerHTML += stripInner.innerHTML;
    }

    // --- Product qty controls ---
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.closest('.qty-control').querySelector('.qty-input');
            let val = parseInt(input.value) || 1;
            if (btn.dataset.action === 'plus')  val = Math.min(val + 1, 99);
            if (btn.dataset.action === 'minus') val = Math.max(val - 1, 1);
            input.value = val;
        });
    });

    // --- Cart item qty update (cart page) ---
    document.querySelectorAll('.cart-qty-input').forEach(input => {
        input.addEventListener('change', () => {
            const id  = input.dataset.id;
            const qty = parseInt(input.value) || 1;
            fetch('process/update_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `product_id=${id}&qty=${qty}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    updateCartBadge(data.count);
                    location.reload();
                }
            });
        });
    });

    // --- Remove from cart ---
    document.querySelectorAll('.cart-remove').forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.id;
            fetch('process/update_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `product_id=${id}&qty=0`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) location.reload();
            });
        });
    });

    // --- Shop filters auto-submit ---
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        filterForm.querySelectorAll('input[type="checkbox"], select').forEach(el => {
            el.addEventListener('change', () => filterForm.submit());
        });
    }

    // --- Intersection observer for fade-in ---
    const fadeEls = document.querySelectorAll('.product-card, .category-card, .brand-card, .stat-card');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        fadeEls.forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity .5s ease, transform .5s ease';
            io.observe(el);
        });
    }
});

// --- Quick Add to Cart ---
function quickAddToCart(event, productId) {
    event.preventDefault();
    event.stopPropagation();
    fetch('process/add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}&qty=1`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('✨ Added to your beauty bag!');
            updateCartBadge(data.count);
        } else {
            showToast(data.message || 'Something went wrong.', 'error');
        }
    })
    .catch(() => showToast('Connection error. Try again.', 'error'));
}

// --- Update cart badge ---
function updateCartBadge(count) {
    const badge = document.getElementById('cart-count');
    if (!badge) return;
    if (count !== undefined) {
        badge.textContent = count;
        badge.style.transform = 'scale(1.4)';
        setTimeout(() => badge.style.transform = 'scale(1)', 300);
        return;
    }
    fetch('process/add_to_cart.php?action=count')
        .then(r => r.json())
        .then(data => { if (badge) badge.textContent = data.count || 0; });
}

// --- Toast notification ---
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.textContent = message;
    toast.style.borderLeftColor = type === 'error' ? '#e53935' : '#c2185b';
    toast.classList.add('show');
    clearTimeout(toast._timer);
    toast._timer = setTimeout(() => toast.classList.remove('show'), 3500);
}

// --- Newsletter subscribe ---
function subscribeNewsletter(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button');
    btn.textContent = '✓ Subscribed!';
    btn.style.background = '#4caf50';
    setTimeout(() => {
        btn.textContent = 'Subscribe';
        btn.style.background = '';
        e.target.reset();
    }, 3000);
}

// --- Admin: confirm delete ---
function confirmDelete(msg) {
    return confirm(msg || 'Are you sure you want to delete this?');
}
