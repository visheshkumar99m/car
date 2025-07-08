<?php
/**
 * Standard footer to be included across all pages
 * 
 * @param array $extra_scripts Additional JavaScript files to include
 * @return void Outputs the footer HTML
 */
function generate_footer($extra_scripts = []) {
?>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Additional page-specific script files -->
    <?php foreach ($extra_scripts as $script): ?>
        <script src="<?php echo htmlspecialchars($script); ?>"></script>
    <?php endforeach; ?>
</body>
</html>
<?php
}
?> 