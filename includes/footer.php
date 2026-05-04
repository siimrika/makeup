</main>

<!-- Footer -->
<footer class="site-footer">
    <div class="footer-top container">
        <div class="footer-brand">
            <a href="<?= BASE_URL ?>index.php" class="logo footer-logo">
                <span class="logo-ms">M</span><span class="logo-text">akeup <em>Studio</em></span>
            </a>
            <p class="footer-desc">Your premium destination for curated beauty. Discover the world's most loved cosmetics brands — all in one place.</p>
            <div class="footer-social">
                <a href="#" aria-label="Instagram" class="social-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </a>
                <a href="#" aria-label="Facebook" class="social-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a href="#" aria-label="YouTube" class="social-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46A2.78 2.78 0 0 0 1.46 6.42 29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.95A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/></svg>
                </a>
            </div>
        </div>

        <div class="footer-nav-group">
            <h4>Shop</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>shop.php?cat=eyes">Eyes</a></li>
                <li><a href="<?= BASE_URL ?>shop.php?cat=face">Face</a></li>
                <li><a href="<?= BASE_URL ?>shop.php?cat=lips">Lips</a></li>
                <li><a href="<?= BASE_URL ?>brands.php">All Brands</a></li>
            </ul>
        </div>

        <div class="footer-nav-group">
            <h4>Brands</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>shop.php?brand=1">Maybelline</a></li>
                <li><a href="<?= BASE_URL ?>shop.php?brand=2">MARS</a></li>
                <li><a href="<?= BASE_URL ?>shop.php?brand=3">NARS</a></li>
                <li><a href="<?= BASE_URL ?>shop.php?brand=4">Huda Beauty</a></li>
            </ul>
        </div>

        <div class="footer-nav-group">
            <h4>Account</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>login.php">Login</a></li>
                <li><a href="<?= BASE_URL ?>register.php">Register</a></li>
                <li><a href="<?= BASE_URL ?>account.php">My Orders</a></li>
                <li><a href="<?= BASE_URL ?>cart.php">My Cart</a></li>
            </ul>
        </div>

        <div class="footer-newsletter">
            <h4>Stay Beautiful</h4>
            <p>Get the latest looks, drops & exclusive offers.</p>
            <form class="newsletter-form" onsubmit="subscribeNewsletter(event)">
                <input type="email" placeholder="your@email.com" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </div>

    <div class="footer-bottom container">
        <p>&copy; <?= date('Y') ?> Makeup Studio. All rights reserved. Made with 💄</p>
        <div class="payment-icons">
            <span class="pay-icon">VISA</span>
            <span class="pay-icon">MC</span>
            <span class="pay-icon">PayPal</span>
            <span class="pay-icon">COD</span>
        </div>
    </div>
</footer>

<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>
</html>
