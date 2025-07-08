<?php
/**
 * Standard header to be included across all pages
 * 
 * @param string $title Page title
 * @param array $extra_css Additional CSS files to include
 * @return void Outputs the header HTML
 */
function generate_header($title = 'CarWale', $extra_css = []) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?> - CarWale</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Global styles - must be included on all pages -->
    <link rel="stylesheet" href="../includes/global_styles.css">
    
    <!-- Additional page-specific CSS files -->
    <?php foreach ($extra_css as $css_file): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($css_file); ?>">
    <?php endforeach; ?>
</head>
<body>
<?php
}
?> 