<?php
// Include the database connection
require_once('../includes/db_connection.php');
require_once('../includes/navbar.php');

session_start();

// Check if an ID was provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect to the cars page if no ID was provided
    header('Location: cars_page.php');
    exit;
}

// Get the car ID from the URL
$car_id = intval($_GET['id']);

// Query to get the car details
$query = "SELECT c.*, b.brand_name, b.description as brand_description 
          FROM cars c 
          JOIN brands b ON c.brand_id = b.brand_id 
          WHERE c.car_id = ?";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $car_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Check if the car exists
if (mysqli_num_rows($result) === 0) {
    // Redirect to the cars page if the car doesn't exist
    header('Location: cars_page.php');
    exit;
}

// Fetch the car details
$car = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($car['car_name']); ?> - Car Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../includes/navbar_styles.css">
    <style>
        .main {
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .car-image {
            max-height: 400px;
            object-fit: cover;
            border-radius: 10px;
        }
        .car-details {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .car-price {
            font-size: 2rem;
            font-weight: bold;
            color: #d32f2f;
        }
        .car-specs {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .spec-item {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .spec-value {
            font-weight: bold;
            font-size: 1.2rem;
            margin-top: 5px;
        }
        .action-buttons {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="main">
        <?php generate_navbar('cars'); ?>
        
        <div class="container my-5">
            <div class="row mb-4">
                <div class="col-12">
                    <a href="cars_page.php" class="btn btn-outline-secondary">&larr; Back to Cars</a>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <img src="<?php echo htmlspecialchars(!empty($car['image_url']) ? $car['image_url'] : $car['image']); ?>" alt="<?php echo htmlspecialchars($car['car_name']); ?>" class="img-fluid car-image">
                </div>
                
                <div class="col-lg-6">
                    <div class="car-details">
                        <h1><?php echo htmlspecialchars($car['car_name']); ?></h1>
                        <p class="text-muted"><?php echo htmlspecialchars($car['brand_name']); ?> | <?php echo $car['year']; ?></p>
                        
                        <div class="car-price mb-4">₹<?php echo number_format($car['price']); ?></div>
                        
                        <h4>Description</h4>
                        <p><?php echo htmlspecialchars($car['description']); ?></p>
                        
                        <h4>Specifications</h4>
                        <div class="car-specs">
                            <div class="spec-item">
                                <div>Type</div>
                                <div class="spec-value"><?php echo htmlspecialchars($car['type']); ?></div>
                            </div>
                            
                            <div class="spec-item">
                                <div>Fuel</div>
                                <div class="spec-value"><?php echo htmlspecialchars($car['fuel']); ?></div>
                            </div>
                            
                            <div class="spec-item">
                                <div>Seats</div>
                                <div class="spec-value"><?php echo $car['seats']; ?></div>
                            </div>
                            
                            <div class="spec-item">
                                <div>Rating</div>
                                <div class="spec-value"><?php echo $car['rating']; ?>/5</div>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button class="btn btn-primary btn-lg" id="add-to-cart" data-car-id="<?php echo $car['car_id']; ?>">Add to Cart</button>
                            <a href="contact_dealer.php?car_id=<?php echo $car['car_id']; ?>" class="btn btn-outline-primary btn-lg ms-2">Contact Dealer</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="col-12">
                    <h3>About <?php echo htmlspecialchars($car['brand_name']); ?></h3>
                    <p><?php echo htmlspecialchars($car['brand_description']); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script>
        document.getElementById('add-to-cart').addEventListener('click', function() {
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
                    alert(data.message || 'Error adding car to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding car to cart');
            });
        });
    </script>
</body>
</html> 