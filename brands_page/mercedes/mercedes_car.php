<?php
require_once('connection.php');
$sql="SELECT * from parts_name_mercedes";
$result=mysqli_query($con,$sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>hyundai</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="mercedes_car_style.css">
</head>

<body>
    <div class="main">
        <nav class="navbar navbar-expand-lg" id="color_change">
            <img id="car_icon" src="https://cdn-icons-png.flaticon.com/128/18585/18585546.png" alt="">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page"
                        href="http://localhost/coding/car%20sellinng%20and%20buying/home_page/front_page/front_page.php"><b>Home</b></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" aria-current="page"
                        href="http://localhost/coding/car%20sellinng%20and%20buying/home_page/about_page/about_page.php"><b>About</b></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" aria-current="page"
                        href="http://localhost/coding/car%20sellinng%20and%20buying/home_page/brands_page/brands_page.php"><b>Brands</b></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="#"><b>Cars</b></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="#"><b>Cart</b></a>
                </li>
            </ul>
            <form action="" class="d-flex">
                <a href="http://localhost/coding/car%20sellinng%20and%20buying/home_page/login_page/login_page.php"
                    class="login_btn"><b>Login</b></a>
                <a href="http://localhost/coding/car%20sellinng%20and%20buying/home_page/register_page/register_page.php"
                    class="register_btn"><b>Register</b></a>
            </form>
        </nav>
    </div>
    <div class="cars_box">
        <div class="cars_subbox1"><h2><b>Mercedes Cars</b></h2></div>
        <div class="cars_subbox2">
            <?php if($result->num_rows>0)
                {
                    while($row=mysqli_fetch_array($result))
                    { ?>
                    <div class="cars_subbox2_items">
                        <?php
                        $img=$row['image'];
                        $imgurl="image/".$img;
                        $name=$row['name'];
                        $price=$row['price'];
                        $rating=$row['rating'];
                        $seat=$row['seat'];
                        $fuel=$row['fuel'];
                        ?>
                        <img src="<?php echo $imgurl;?>" alt="">
                        <h2><b><?php echo $name?></b></h2>
                        <div>
                            <h6 class="price">price: <?php echo $price ?></h6>
                            <h6 class="fuel">fuel: <?php echo $fuel ?></h6>
                        </div>
                        <div>
                            <h6 class="rating">Rating:<?php echo $rating?></h6>
                            <h6 class="seat">seat: <?php echo $seat ?></h6>
                        </div><br>
                        <form action="">
                            <button type="submit" class="view">View</button>
                            <button type="submit" class="add">Add to Cart</button>
                        </form>
                    </div>
            <?php } } ?>
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
            <a href="www.linkedin.com/in/vishesh-kumar-42ba58266"><img
                    src="https://images.rawpixel.com/image_png_800/czNmcy1wcml2YXRlL3Jhd3BpeGVsX2ltYWdlcy93ZWJzaXRlX2NvbnRlbnQvbHIvdjk4Mi1kMS0xMC5wbmc.png"
                    alt=""></a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="nav_logic.js"></script>
</body>

</html>