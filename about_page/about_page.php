<?php
// Include necessary files
require_once('../includes/header.php');
require_once('../includes/navbar.php');
require_once('../includes/footer.php');
require_once('../includes/auth_check.php');

// Start the session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Generate the header with page-specific CSS
generate_header('About Us', ['about_page_style.css']);
?>

<div class="main">
    <?php
    // Generate the navbar with 'about' as the active page
    generate_navbar('about');
    
    // Display flash messages
    show_message();
    ?>
    
    <div class="about_box1">
        <div class="about_box_subbox1">
            <div>
                <p>1</p>
            </div> <br>
            <h1><b>CarWale, where your car <br> buying journey begins</b></h1>
            <p><b>
                    With a passion for cars and a commitment to helping you find the perfect ride,
                    we've built a platform that simplifies the car buying experience. Our
                    extensive inventory, expert reviews, and user-friendly tools empower you to
                    make informed decisions. Whether you're in search of a fuel-efficient
                    compact or a high-performance luxury vehicle, CarWale has you covered. We
                    believe that buying a car should be exciting, not stressful, and that's why
                    we're here to guide you every step of the way. Join us on the journey to
                    finding your ideal car, and let's drive your dreams together.
                </b></p>
        </div>
        <div class="about_box_subbox2">
            <img src="https://carwale.onrender.com/static/media/aboutUs.ee62b108417a5eba4710.png" alt="">
        </div>
        <div class="about_box_subbox3">
            <img src="https://carwale.onrender.com/static/media/aboutUs2.3e765f95c66909b93310.png" alt="">
        </div>
        <div class="about_box_subbox4">
            <div>
                <p>2</p>
            </div>
            <br>
            <h1><b>The best car buying company, we <br> understand your needs</b></h1>
            <p><b>
                    We're more than just a website; we're your trusted partner in finding the perfect vehicle. With a
                    passion for automobiles and a dedication to your satisfaction, we've curated a vast selection of
                    cars to suit every need and budget. Our mission is to simplify the car-buying process, providing you
                    with the tools and resources you need to make informed decisions. Our team of experts is here to
                    guide you, offering valuable insights and advice along the way.
                </b></p>
        </div>
    </div>
</div>

<div class="footer_box">
    <div class="footer_subbox1">
        <h2>carwale</h2>
        <p>At CarWale, we're dedicated to making your car buying experience as smooth as the road ahead. With a wide
            range of brands, expert guidance, secure transactions, and innovative features, we're your trusted
            partner on your journey to finding the perfect ride. Drive your dreams with CarWale, where your
            satisfaction is our ultimate destination.</p>
    </div>
    <div class="footer_subbox2">
        <h2>Contact</h2>
        <p>vishesh1426@gmail.com</p>
        <p>Teerthanker Mahaveer university moradabad</p>
        <p>uttar pradesh, India</p>
    </div>
    <div class="footer_subbox3">
        <h2>Social Media</h2>
        <a href="www.linkedin.com/in/vishesh-kumar-42ba58266"><img src="https://images.rawpixel.com/image_png_800/czNmcy1wcml2YXRlL3Jhd3BpeGVsX2ltYWdlcy93ZWJzaXRlX2NvbnRlbnQvbHIvdjk4Mi1kMS0xMC5wbmc.png"
                alt=""></a>
    </div>
</div>

<?php 
// Generate the footer with page-specific scripts
generate_footer(['nav_logic.js']);
?>