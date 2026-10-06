<!-- Footer -->
<footer class="site-footer font-headline-sm">
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="brand">
                    <div class="logo font-headline-sm">K</div>
                    <h2>Kids Avon</h2>
                </div>
                <p class="desc font-body-md">Empowering young cyclists to explore, achieve, and stay active together.</p>
                <div class="support">
                    <span class="label font-label-sm">Help &amp; Support</span>
                    <a class="font-body-md" href="mailto:{{ $supportEmail }}">
                        <span class="material-symbols-outlined icon">mail</span>
                        {{ $supportEmail }}
                    </a>
                </div>
            </div>

            <div class="footer-col">
                <h3>
                    <span class="material-symbols-outlined icon-secondary">health_and_safety</span>
                    Safety First
                </h3>
                <ul class="font-body-md">
                    <li>• Wear a properly fitted helmet</li>
                    <li>• Stay on designated bike paths</li>
                    <li>• Use hand signals when turning</li>
                    <li>• Ride with a buddy or adult</li>
                </ul>
            </div>

            <div class="footer-col">
                <h3>
                    <span class="material-symbols-outlined icon-tertiary">family_restroom</span>
                    For Parents
                </h3>
                <p class="text-block font-body-md">Guardian consent is required. We prioritize safety with moderated
                    features and private tracking.</p>
                <p class="text-block font-body-md"><span class="highlight">Activity:</span> Stay hydrated and check
                    tire pressure before every ride!</p>
            </div>

            <div class="footer-col">
                <h3>
                    <span class="material-symbols-outlined icon-neutral">quiz</span> Quick Links
                </h3>
                <nav class="font-body-md">
                    @foreach (config('kidsavon.footer_links.'.$footerVariant, config('kidsavon.footer_links.default')) as $link)
                        <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            </div>
        </div>

        <div class="footer-bottom">
            <p class="copyright font-label-sm">© {{ now()->year }} Kids Avon. All rights reserved.</p>
            <div class="legal-links font-label-sm">
                <a href="#">Terms of Service</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Cookie Settings</a>
            </div>
        </div>
    </div>
</footer>
