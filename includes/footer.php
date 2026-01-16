<?php
// File footer umum
?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../assets/js/script.js"></script>
    
    <!-- Profile Dropdown Script -->
    <script>
        // Profile dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const userToggle = document.querySelector('.user-toggle');
            const userDropdown = document.querySelector('.user-dropdown');
            
            if (userToggle && userDropdown) {
                // Toggle dropdown
                userToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    userDropdown.classList.toggle('show');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                        userDropdown.classList.remove('show');
                    }
                });
                
                // Close dropdown on escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        userDropdown.classList.remove('show');
                    }
                });
                
                // Close dropdown when clicking on a link
                userDropdown.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function() {
                        userDropdown.classList.remove('show');
                    });
                });
            }
            
            // Add animation to cart icon
            const cartIcon = document.querySelector('.fa-shopping-cart');
            if (cartIcon) {
                cartIcon.parentElement.classList.add('floating-cart');
            }
            
            // Update user avatar with initials
            const userAvatar = document.querySelector('.user-avatar:not(.dropdown-avatar)');
            if (userAvatar && !userAvatar.innerHTML.trim()) {
                const userName = "<?php echo $_SESSION['full_name'] ?? 'User'; ?>";
                const initials = userName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                userAvatar.innerHTML = initials;
            }
            
            // Update dropdown avatar
            const dropdownAvatar = document.querySelector('.dropdown-avatar');
            if (dropdownAvatar && !dropdownAvatar.innerHTML.trim()) {
                const userName = "<?php echo $_SESSION['full_name'] ?? 'User'; ?>";
                const initials = userName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                dropdownAvatar.innerHTML = initials;
            }
        });
        
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
    
    <?php if (isset($custom_js)): ?>
    <script><?php echo $custom_js; ?></script>
    <?php endif; ?>
    
</body>
</html>