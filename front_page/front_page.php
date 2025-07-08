<?php
// Include necessary files
require_once('../includes/header.php');
require_once('../includes/navbar.php');
require_once('../includes/footer.php');
require_once('../includes/auth_check.php');
require_once('../includes/db_connection.php');

// Start the session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get featured cars for display
$featured_query = "SELECT c.car_id, c.car_name, c.price, c.image, c.image_url, b.brand_name 
                   FROM cars c 
                   JOIN brands b ON c.brand_id = b.brand_id 
                   ORDER BY c.car_id DESC LIMIT 4";
$featured_result = mysqli_query($con, $featured_query);
$featured_cars = [];

if ($featured_result && mysqli_num_rows($featured_result) > 0) {
    while ($car = mysqli_fetch_assoc($featured_result)) {
        $featured_cars[] = [
            'id' => $car['car_id'],
            'name' => $car['car_name'],
            'brand' => $car['brand_name'],
            'price' => $car['price'],
            'image' => $car['image_url'] ?? $car['image']
        ];
    }
}

// Generate the header with page-specific CSS
generate_header('Home', ['front_page_style.css', '../includes/car_data_styles.css']);
?>

<div class="main">
    <?php 
    // Generate the navbar with 'home' as the active page
    generate_navbar('home');
    
    // Display flash messages
    show_message();
    ?>
    
    <div class="image_text_main">
        <div class="image_main">
            <img src="https://carwale.onrender.com/static/media/hero.6a0537c7a6d7b4d04baf.png" alt="">
        </div>
        <div class="text_main">
            <h1><b>Choose Your <span id="dream_text">Dream</span> Car</b></h1>
            <p>Experience car buying like never before. CarWale offers an <br> extensive range of options,
                unbeatable deals, expert <br> guidance, and a hassle-free journey to your dream car. <br> Discover,
                compare, and drive with confidence.</p>
        </div>
    </div>
</div>

<!-- Featured Cars Section -->
<?php if (!empty($featured_cars)): ?>
<div class="container my-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold">Featured <span class="text-primary">Cars</span></h2>
        <p class="lead text-muted">Discover our latest and most popular vehicles</p>
    </div>
    
    <div class="car-grid">
        <?php foreach ($featured_cars as $car): ?>
            <div class="car-card">
                <div class="car-image-container">
                    <img src="<?php echo htmlspecialchars($car['image']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" class="car-image-element">
                </div>
                <div class="car-info">
                    <h5><?php echo htmlspecialchars($car['name']); ?></h5>
                    <p class="car-brand">
                        <span class="badge bg-light text-dark"><?php echo htmlspecialchars($car['brand']); ?></span>
                    </p>
                    <p class="car-price">₹<?php echo number_format($car['price']); ?></p>
                    <div class="d-flex gap-2">
                        <a href="../cars_page/car_details.php?id=<?php echo $car['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                        <button class="btn btn-sm btn-outline-success add-to-cart" data-car-id="<?php echo $car['id']; ?>">Add to Cart</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-4">
        <a href="../cars_page/cars_page.php" class="btn btn-outline-primary">View All Cars</a>
    </div>
</div>
<?php endif; ?>

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

<div class="feature_box">
    <div class="feature_box1">
        <h1>Our automated features</h1>
    </div>
    <div class="feature_box2">
        <div class="feature_box_subbox1">
            <img src="https://carwale.onrender.com/static/media/secure.b264c97d2b2c35a34492.gif" alt="">
            <h2><b>Secure Payment</b></h2><br>
            <p><b>We take your security seriously, and that's why we've implemented state-of-the-art secure payment
                    systems. Your financial information is safeguarded with the latest encryption technology,
                    ensuring
                    your transactions are always safe and secure.</b></p>
        </div>
        <div class="feature_box_subbox2">
            <img src="https://carwale.onrender.com/static/media/views.3eae1435d6726dc391a3.gif" alt="">
            <h2><b>360 Visualization</b></h2><br>
            <p><b>Get ready to explore every angle, every detail, and every curve of your dream car from the comfort
                    of your screen. Our cutting-edge technology brings the showroom to you, allowing you to
                    virtually step inside the driver's seat and truly immerse yourself.</b></p>
        </div>
        <div class="feature_box_subbox3">
            <img src="https://carwale.onrender.com/static/media/money.5072d5b5ff57890df244.gif" alt="">
            <h2><b>Fast and Secure</b></h2><br>
            <p><b>Our platform offers a seamless, lightning-fast, and secure interaction that redefines the car
                    buying experience. With our cutting-edge technology, you can effortlessly browse, compare, and
                    connect with sellers or dealers, all in real-time.</b></p>
        </div>
    </div>
</div>

<div class="ask_box_main">
    <div class="ask_box_main1">
        <h1><b>Frequently asked questions</b></h1>
    </div>
    <div class="ask_box_main2">
        <div class="ask_box_subbox1">
            <ol>
                <h4><b>1. Can I do car payment online?</b><span onclick="plus1()" id="mines1">+</span></h4>
                <hr>
                <p id="plus_box1"></p>
                <h4><b>2. Can I buy a car online? </b><span onclick="plus2()" id="mines2">+</span></h4>
                <hr>
                <p id="plus_box2"></p>
                <h4><b>3. How many cars are currently available at carwale?</b><span onclick="plus3()"
                        id="mines3">+</span></h4>
                <hr>
                <p id="plus_box3"></p>
                <h4><b>4. Do you offer express service? </b><span onclick="plus4()" id="mines4">+</span></h4>
                <hr>
                <p id="plus_box4"></p>
                <h4><b>5. How many brands are available at carwale?</b><span onclick="plus5()" id="mines5">+</span>
                </h4>
                <hr>
                <p id="plus_box5"></p>
            </ol>
        </div>
        <div class="ask_box_subbox2">
            <img src="https://carwale.onrender.com/static/media/search.665e36ea6e93e78bf865.png" alt="">
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
generate_footer(['logic_home.js', 'nav_logic.js']);
?>
<script>
    // Add to cart functionality for featured cars
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const carId = this.getAttribute('data-car-id');
            
            // Send AJAX request to add car to cart
            fetch('../cart_page/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `car_id=${carId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Car added to cart!');
                } else {
                    if (data.redirect) {
                        window.location.href = data.redirect_url;
                    } else {
                        alert(data.message || 'Error adding car to cart');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding car to cart');
            });
        });
    });
</script>