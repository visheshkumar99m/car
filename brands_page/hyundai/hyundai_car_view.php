<?php
require_once('connection.php');
$sql="SELECT * FROM parts_name_hyundai";
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
    <link rel="stylesheet" href="hyundai_car_style.css">
    <link rel="stylesheet" href="hyundai_car_style_view.css">
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
    <div class="view_box">
        <?php
            if($result->num_rows>0)
            {
                while($row=mysqli_fetch_array($result))
                {
                    if($row['name']=='Hyundai Venue')
                    {
        ?>
        <div class="view_subbox1">
            <?php 
                $img=$row['image'];
                $imgurl="image/".$img;
            ?>
            <img src="<?php echo $imgurl;?>" alt="">
        </div>
        <div class="view_subbox2">
            <h2><b><?php echo $row['name'];?></b></h2><br>
            <h3><b>Hyundai Venue Description :</b></h3>
            <p><?php echo $row['describtion']; ?></p>
            <h3><b><?php echo $row['price'];?></b></h3>
            <h3><b>Released At :<?php echo $row['date'];?></b></h3><br>
            <form action="">
                <button type="submit" class="add_cart">Add to cart</button>&nbsp&nbsp&nbsp
                <button type="submit" class="view_cart">View Cart</button>
            </form><br><br>
            <table>
                <tr>
                    <td>
                        <img src="https://cdn-icons-png.flaticon.com/128/1505/1505581.png" alt="" width="13%";>
                        <span style="color:gray; font-size:25px;"><b>:Fuel Type</b></span><br>
                        <span style="font-size:18px;"><b><?php echo $row['fuel'];?></b></span>
                    </td>
                    <td>
                        <img src="https://cdn-icons-png.flaticon.com/128/84/84570.png" alt="" width="13%";>
                        <span style="color:gray; font-size:25px;"><b>:Milege</b></span><br>
                        <span style="font-size:18px;"><b><?php echo $row['milege'];?></b></span>
                    </td>
                    <td>
                        <img src="https://cdn-icons-png.flaticon.com/128/9732/9732828.png" alt="" width="13%";>
                        <span style="color:gray; font-size:25px;"><b>:Rating</b></span><br>
                        <span style="font-size:18px;"><b><?php echo $row['rating'];?></b></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="https://cdn-icons-png.flaticon.com/128/9048/9048789.png" alt="" width="13%";>
                        <span style="color:gray; font-size:25px;"><b>:Warranty</b></span><br>
                        <span style="font-size:18px;"><b><?php echo $row['warranty'];?></b></span>
                    </td>
                    <td>
                        <img src="https://cdn-icons-png.flaticon.com/128/566/566235.png" alt="" width="13%";>
                        <span style="color:gray; font-size:25px;"><b>:Seat</b></span><br>
                        <span style="font-size:18px;"><b><?php echo $row['seat'];?></b></span>
                    </td>
                    <td>
                        <img src="https://cdn-icons-png.flaticon.com/128/3170/3170545.png" alt="" width="13%";>
                        <span style="color:gray; font-size:25px;"><b>:Fuel Tank</b></span><br>
                        <span style="font-size:18px;"><b><?php echo $row['tank'];?></b></span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <img src="https://cdn-icons-png.flaticon.com/128/14954/14954571.png" alt="" width="13%";>
                        <span style="color:gray; font-size:25px;"><b>:Engine Size</b></span><br>
                        <span style="font-size:18px;"><b><?php echo $row['engine_size'];?></b></span>
                    </td>
                    <td>
                        <img src="https://cdn-icons-png.flaticon.com/128/1148/1148942.png" alt="" width="13%";>
                        <span style="color:gray; font-size:25px;"><b>:Transmission</b></span><br>
                        <span style="font-size:18px;"><b><?php echo $row['transmission'];?></b></span>
                    </td>
                </tr>
            </table>
        </div>
        <?php }}}?>
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