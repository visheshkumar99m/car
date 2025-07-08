<?php
// Include necessary files
require_once('../includes/header.php');
require_once('../includes/footer.php');
require_once('../includes/db_connection.php');
require_once('../includes/auth_check.php');

// Require admin privileges
require_admin();

// Initialize variables
$car_name = $price = $image = $year = $type = $fuel = $seats = $rating = $description = '';
$brand_id = 0;
$errors = [];

// Get all brands for dropdown
$brand_query = "SELECT brand_id, brand_name FROM brands ORDER BY brand_name";
$brand_result = mysqli_query($con, $brand_query);
$brands = [];
while ($row = mysqli_fetch_assoc($brand_result)) {
    $brands[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate car name
    if (empty(trim($_POST['car_name']))) {
        $errors['car_name'] = 'Car name is required';
    } else {
        $car_name = trim($_POST['car_name']);
    }
    
    // Validate brand
    if (empty($_POST['brand_id']) || !is_numeric($_POST['brand_id'])) {
        $errors['brand_id'] = 'Please select a valid brand';
    } else {
        $brand_id = intval($_POST['brand_id']);
    }
    
    // Validate price
    if (empty(trim($_POST['price'])) || !is_numeric($_POST['price'])) {
        $errors['price'] = 'Valid price is required';
    } else {
        $price = floatval($_POST['price']);
    }
    
    // Process image - either from URL or file upload
    $image = '';
    $image_updated = false;
    
    // Process file upload if exists
    if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['car_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        
        // Verify file extension
        if (!in_array(strtolower($filetype), $allowed)) {
            $errors['car_image'] = 'Only JPG, JPEG, PNG, GIF and WEBP files are allowed for car images';
        } else {
            // Create upload directory if it doesn't exist
            $upload_dir = '../uploads/car_images/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate a unique filename
            $new_filename = 'car_' . uniqid() . '.' . $filetype;
            $upload_path = $upload_dir . $new_filename;
            
            // Move the uploaded file
            if (move_uploaded_file($_FILES['car_image']['tmp_name'], $upload_path)) {
                $image = '../uploads/car_images/' . $new_filename;
                $image_updated = true;
            } else {
                $errors['car_image'] = 'Failed to upload image';
            }
        }
    } 
    // If no file uploaded but URL is provided
    elseif (!empty(trim($_POST['image']))) {
        $image = trim($_POST['image']);
        $image_updated = true;
    } 
    // Neither file nor URL provided
    elseif (!$image_updated) {
        $errors['image'] = 'Either an image URL or file upload is required';
    }
    
    // Validate year
    if (empty($_POST['year']) || !is_numeric($_POST['year'])) {
        $errors['year'] = 'Valid year is required';
    } else {
        $year = intval($_POST['year']);
    }
    
    // Get other fields
    $type = trim($_POST['type']);
    $fuel = trim($_POST['fuel']);
    $seats = is_numeric($_POST['seats']) ? intval($_POST['seats']) : 0;
    $rating = is_numeric($_POST['rating']) ? floatval($_POST['rating']) : 0;
    $description = trim($_POST['description']);
    
    // If no errors, insert into database
    if (empty($errors)) {
        $insert_query = "INSERT INTO cars (brand_id, car_name, price, image, image_url, year, type, fuel, seats, rating, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $insert_query);
        
        mysqli_stmt_bind_param($stmt, "isdsssssisd", 
            $brand_id, 
            $car_name, 
            $price, 
            $image,
            $image, // Use the same value for image_url
            $year, 
            $type, 
            $fuel, 
            $seats, 
            $rating, 
            $description
        );
        
        if (mysqli_stmt_execute($stmt)) {
            set_message('Car added successfully!', 'success');
            // Redirect to cars list
            header('Location: cars.php');
            exit;
        } else {
            set_message('Error adding car: ' . mysqli_error($con), 'error');
        }
    }
}

// Generate the header
generate_header('Add New Car');
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block admin-sidebar">
            <div class="d-flex align-items-center p-3 mb-3">
                <img src="https://cdn-icons-png.flaticon.com/128/18585/18585546.png" alt="CarWale Logo" width="40" class="me-2">
                <span class="fs-4 text-white">Admin</span>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="cars.php">
                        <i class="bi bi-car-front me-2"></i> Cars
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="brands.php">
                        <i class="bi bi-badge-tm me-2"></i> Brands
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="bi bi-people me-2"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">
                        <i class="bi bi-bag me-2"></i> Orders
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link" href="../front_page/front_page.php">
                        <i class="bi bi-box-arrow-left me-2"></i> Back to Website
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            <!-- Page header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2">Add New Car</h1>
                <a href="cars.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Cars
                </a>
            </div>
            
            <!-- Flash messages -->
            <?php show_message(); ?>
            
            <!-- Add car form -->
            <div class="card admin-card">
                <div class="card-body">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" class="row g-3" enctype="multipart/form-data">
                        <div class="col-md-6">
                            <label for="car_name" class="form-label">Car Name*</label>
                            <input type="text" class="form-control <?php echo isset($errors['car_name']) ? 'is-invalid' : ''; ?>" id="car_name" name="car_name" value="<?php echo htmlspecialchars($car_name); ?>" required>
                            <?php if (isset($errors['car_name'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['car_name']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="brand_id" class="form-label">Brand*</label>
                            <select class="form-select <?php echo isset($errors['brand_id']) ? 'is-invalid' : ''; ?>" id="brand_id" name="brand_id" required>
                                <option value="">Select Brand</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo $brand['brand_id']; ?>" <?php echo ($brand_id == $brand['brand_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($brand['brand_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['brand_id'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['brand_id']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="price" class="form-label">Price*</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" min="0" step="0.01" required>
                            </div>
                            <?php if (isset($errors['price'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['price']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="year" class="form-label">Year*</label>
                            <input type="number" class="form-control <?php echo isset($errors['year']) ? 'is-invalid' : ''; ?>" id="year" name="year" value="<?php echo htmlspecialchars($year); ?>" min="1900" max="<?php echo date('Y') + 1; ?>" required>
                            <?php if (isset($errors['year'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['year']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-12">
                            <label for="image" class="form-label">Image URL</label>
                            <input type="url" class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" id="image" name="image" value="<?php echo htmlspecialchars($image); ?>">
                            <div class="form-text">Enter a URL or upload an image below</div>
                            <?php if (isset($errors['image'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['image']; ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-12 mt-2">
                            <label for="car_image" class="form-label">Or Upload Image</label>
                            <input type="file" class="form-control <?php echo isset($errors['car_image']) ? 'is-invalid' : ''; ?>" id="car_image" name="car_image" accept="image/*">
                            <?php if (isset($errors['car_image'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['car_image']; ?></div>
                            <?php endif; ?>
                            <div class="mt-2" id="image-preview-container" style="display: none;">
                                <p>Image Preview:</p>
                                <img src="" alt="Car Preview" class="img-thumbnail" style="max-height: 100px;" id="image-preview">
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type">
                                <option value="">Select Type</option>
                                <option value="Sedan" <?php echo ($type === 'Sedan') ? 'selected' : ''; ?>>Sedan</option>
                                <option value="SUV" <?php echo ($type === 'SUV') ? 'selected' : ''; ?>>SUV</option>
                                <option value="Hatchback" <?php echo ($type === 'Hatchback') ? 'selected' : ''; ?>>Hatchback</option>
                                <option value="Convertible" <?php echo ($type === 'Convertible') ? 'selected' : ''; ?>>Convertible</option>
                                <option value="Coupe" <?php echo ($type === 'Coupe') ? 'selected' : ''; ?>>Coupe</option>
                                <option value="Truck" <?php echo ($type === 'Truck') ? 'selected' : ''; ?>>Truck</option>
                                <option value="Van" <?php echo ($type === 'Van') ? 'selected' : ''; ?>>Van</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label for="fuel" class="form-label">Fuel Type</label>
                            <select class="form-select" id="fuel" name="fuel">
                                <option value="">Select Fuel Type</option>
                                <option value="Petrol" <?php echo ($fuel === 'Petrol') ? 'selected' : ''; ?>>Petrol</option>
                                <option value="Diesel" <?php echo ($fuel === 'Diesel') ? 'selected' : ''; ?>>Diesel</option>
                                <option value="Electric" <?php echo ($fuel === 'Electric') ? 'selected' : ''; ?>>Electric</option>
                                <option value="Hybrid" <?php echo ($fuel === 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                                <option value="Petrol/Diesel" <?php echo ($fuel === 'Petrol/Diesel') ? 'selected' : ''; ?>>Petrol/Diesel</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label for="seats" class="form-label">Seats</label>
                            <input type="number" class="form-control" id="seats" name="seats" value="<?php echo htmlspecialchars($seats); ?>" min="1" max="10">
                        </div>
                        
                        <div class="col-md-3">
                            <label for="rating" class="form-label">Rating</label>
                            <input type="number" class="form-control" id="rating" name="rating" value="<?php echo htmlspecialchars($rating); ?>" min="0" max="5" step="0.1">
                        </div>
                        
                        <div class="col-md-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                        </div>
                        
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary">Add Car</button>
                            <a href="cars.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <footer class="mt-5 text-center">
                <p>&copy; 2023 CarWale Admin Panel</p>
            </footer>
        </div>
    </div>
</div>

<script>
    // Image URL preview
    document.getElementById('image').addEventListener('input', function() {
        const imageUrl = this.value;
        const previewContainer = document.getElementById('image-preview-container');
        const previewImg = document.getElementById('image-preview');
        
        if (imageUrl) {
            previewContainer.style.display = 'block';
            previewImg.src = imageUrl;
        } else {
            previewContainer.style.display = 'none';
        }
    });
    
    // File upload preview
    document.getElementById('car_image').addEventListener('change', function(e) {
        const previewContainer = document.getElementById('image-preview-container');
        const previewImg = document.getElementById('image-preview');
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                previewContainer.style.display = 'block';
                previewImg.src = e.target.result;
            };
            
            reader.readAsDataURL(this.files[0]);
        } else {
            previewContainer.style.display = 'none';
        }
    });
</script>

<?php generate_footer(); ?> 