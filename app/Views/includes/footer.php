<?php
// niflix_project/app/Views/includes/footer.php

// Pastikan base Path tersedia
$basePath = dirname($_SERVER['SCRIPT_NAME']);
if ($basePath === '/') {
    $basePath = '';
} else {
    $basePath = rtrim($basePath, '/');
}
?>
    </main> <footer>
        <p>&copy; 2025 Movie & Series Review</p>
    </footer>

    <script src="<?= $basePath ?>/assets/js/script.js"></script>

</body>
</html>