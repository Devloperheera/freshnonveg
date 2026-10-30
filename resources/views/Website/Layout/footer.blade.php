<footer class="pt-5 pb-3">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="logo"><img src="{{ asset('website_assets/image/logo.png') }}" alt="Fresh Non Veg" height="48" style="max-width:220px;height:48px;object-fit:contain"></div>
                <p>Only Fresh, No Frozen – Direct From Meat Shop</p>
                <p>📍 A35, Behind HDFC Bank, Shramik Kunj, Sector 110, Noida 201304<br>📞 9717156362<br>✉️
                    eatfreshnonveg@gmail.com</p>
            </div>
            <div class="col-6 col-md-3">
                <h5>Quick Links</h5>
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('home') }}#shop">Shop</a>
                <a href="{{ route('home') }}#faq">FAQ</a>
                <a href="{{ route('pages.show', 'blog') }}">Blog</a>
                <a href="{{ route('pages.show', 'contact') }}">Contact</a>
            </div>
            <div class="col-6 col-md-3">
                <h5>We Deliver In</h5><a href="#">Noida</a><a href="#">Greater Noida</a><a
                    href="#">Greater Noida West</a><a href="#">Ghaziabad</a><a href="#">Gautam Buddha
                    Nagar</a>
            </div>
            <div class="col-md-3">
                <h5>Legal</h5>
                <a href="{{ route('pages.show', 'terms-conditions') }}">Terms &amp; Conditions</a>
                <a href="{{ route('pages.show', 'privacy-policy') }}">Privacy Policy</a>
                <a href="{{ route('pages.show', 'refund-policy') }}">Refund Policy</a>
                <a href="{{ route('account.delete') }}">Delete Account</a>
            </div>
        </div>
        <p class="text-center small mt-4 pt-3" style="border-top:1px solid #334155">© 2026 Fresh Non Veg. All rights
            reserved. | Freshnonveg.com is owned by SET INDIA BUSINESS PVT LTD</p>
    </div>
</footer>
