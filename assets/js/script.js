// script.js - fully self-contained, no syntax errors
document.addEventListener("DOMContentLoaded", function () {
  /* -------------------------
       Helper: safe query
       ------------------------- */
  function q(sel, parent) {
    return (parent || document).querySelector(sel);
  }
  function qa(sel, parent) {
    return Array.from((parent || document).querySelectorAll(sel));
  }

  /* -------------------------
       Navbar scroll effect
       ------------------------- */
  const header = q(".header");
  function onScrollHeader() {
    if (!header) return;
    if (window.scrollY > 60) header.classList.add("scrolled");
    else header.classList.remove("scrolled");
  }
  window.addEventListener("scroll", onScrollHeader);
  onScrollHeader();

  /* -------------------------
       Smooth scroll for anchors (account header height)
       ------------------------- */
  qa('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const href = this.getAttribute("href");
      if (!href || href === "#") return;
      const target = document.querySelector(href);
      if (!target) return;
      e.preventDefault();
      const headerHeight = header ? header.offsetHeight : 80;
      const top =
        target.getBoundingClientRect().top +
        window.pageYOffset -
        headerHeight -
        8;
      window.scrollTo({ top, behavior: "smooth" });

      // update active nav link
      qa(".navbar-nav .nav-link").forEach((n) => n.classList.remove("active"));
      if (this.classList) this.classList.add("active");
    });
  });

  /* -------------------------
       Hero slider
       ------------------------- */
  const slides = qa(".slide");
  let currentSlide = 0;
  if (slides.length) {
    function showSlide(index) {
      slides.forEach((s, i) => s.classList.toggle("active", i === index));
    }
    showSlide(0);
    setInterval(function () {
      currentSlide = (currentSlide + 1) % slides.length;
      showSlide(currentSlide);
    }, 5000);
  }

  /* -------------------------
       Service items: hover gradient & click show only selected detail
       ------------------------- */
  const featureItems = qa(".feature-item");
  const featureDetails = qa(".feature-detail");
  featureDetails.forEach((fd) => fd.classList.remove("active"));
  featureItems.forEach((item) => {
    item.addEventListener("mouseenter", function () {
      this.classList.add("hovered");
    });
    item.addEventListener("mouseleave", function () {
      this.classList.remove("hovered");
    });
    item.addEventListener("keydown", function (ev) {
      if (ev.key === "Enter" || ev.key === " ") {
        ev.preventDefault();
        this.click();
      }
    });
    item.addEventListener("click", function () {
      const targetId = this.getAttribute("data-target");
      if (!targetId) return;
      featureItems.forEach((fi) => fi.classList.remove("selected"));
      featureDetails.forEach((fd) => fd.classList.remove("active"));
      this.classList.add("selected");
      const detail = document.getElementById(targetId);
      if (detail) detail.classList.add("active");
      setTimeout(function () {
        const headerHeight = header ? header.offsetHeight : 80;
        const top =
          detail.getBoundingClientRect().top +
          window.pageYOffset -
          headerHeight -
          12;
        window.scrollTo({ top, behavior: "smooth" });
      }, 150);
    });
  });

  /* -------------------------
       Menu category filtering with smooth show/hide
       ------------------------- */
  const categoryButtons = qa(".category-btn");
  const menuItems = qa(".menu-item");
  categoryButtons.forEach((btn) => {
    btn.addEventListener("click", function () {
      const cat = this.getAttribute("data-category") || "all";
      categoryButtons.forEach((b) => b.classList.remove("active"));
      this.classList.add("active");
      menuItems.forEach((item) => {
        const itemCat = item.getAttribute("data-category") || "";
        if (cat === "all" || itemCat === cat) {
          item.style.display = "block";
          item.style.opacity = "0";
          item.style.transform = "translateY(6px)";
          setTimeout(() => {
            item.style.transition = "opacity 300ms ease, transform 300ms ease";
            item.style.opacity = "1";
            item.style.transform = "translateY(0)";
          }, 40);
        } else {
          item.style.transition = "opacity 300ms ease, transform 300ms ease";
          item.style.opacity = "0";
          item.style.transform = "translateY(12px)";
          setTimeout(() => {
            item.style.display = "none";
          }, 320);
        }
      });
    });
  });

  /* -------------------------
       Menu card hover: visual upgrade
       ------------------------- */
  qa(".menu-card").forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-6px)";
      this.style.boxShadow = "0 18px 40px rgba(12,30,18,0.12)";
    });
    card.addEventListener("mouseleave", function () {
      this.style.transform = "";
      this.style.boxShadow = "";
    });
  });

  /* -------------------------
   Testimonials slider
------------------------- */
  const testimonialViewport = q(".testimonial-viewport");
  const testimonialTrack = q(".testimonial-track");
  const testimonialItems = qa(".testimonial-item");
  const prevBtn = q(".prev-btn");
  const nextBtn = q(".next-btn");

  let currentTestimonial = 0;

  function updateTestimonialPosition() {
    if (
      !testimonialTrack ||
      testimonialItems.length === 0 ||
      !testimonialViewport
    )
      return;
    const viewportWidth = testimonialViewport.clientWidth;
    const itemWidth =
      testimonialItems[0].offsetWidth +
      parseFloat(getComputedStyle(testimonialItems[0]).marginRight || 16);
    const maxIndex = Math.max(
      0,
      testimonialItems.length - Math.floor(viewportWidth / itemWidth)
    );
    if (currentTestimonial > maxIndex) currentTestimonial = maxIndex;
    testimonialTrack.style.transform = `translateX(-${
      currentTestimonial * itemWidth
    }px)`;
    testimonialTrack.style.transition = "transform 0.3s ease";
  }

  if (prevBtn)
    prevBtn.addEventListener("click", () => {
      currentTestimonial = Math.max(0, currentTestimonial - 1);
      updateTestimonialPosition();
    });

  if (nextBtn)
    nextBtn.addEventListener("click", () => {
      const maxIndex = Math.max(0, testimonialItems.length - 1);
      currentTestimonial = Math.min(maxIndex, currentTestimonial + 1);
      updateTestimonialPosition();
    });

  window.addEventListener("resize", updateTestimonialPosition);
  updateTestimonialPosition();

  /* -------------------------
   Profile Dropdown Functionality
------------------------- */
  // Initialize all user dropdowns
  qa('.user-nav').forEach(userNav => {
    const userToggle = userNav.querySelector('.user-toggle');
    const userDropdown = userNav.querySelector('.user-dropdown');
    
    if (userToggle && userDropdown) {
      // Toggle dropdown
      userToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        userDropdown.classList.toggle('show');
        
        // Add animation class
        if (userDropdown.classList.contains('show')) {
          userDropdown.style.animation = 'slideDown 0.3s ease-out';
        }
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (!userToggle.contains(e.target) && !userDropdown.contains(e.target)) {
          userDropdown.classList.remove('show');
        }
      });
      
      // Close on escape key
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          userDropdown.classList.remove('show');
        }
      });
    }
  });
  
  // Generate user avatar with initials
  function generateAvatarInitials() {
    qa('.user-avatar:not(.dropdown-avatar)').forEach(avatar => {
      if (!avatar.innerHTML.trim()) {
        const userName = avatar.closest('.user-nav')?.querySelector('.user-name')?.textContent || 'User';
        const initials = userName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
        avatar.innerHTML = initials;
      }
    });
    
    qa('.dropdown-avatar').forEach(avatar => {
      if (!avatar.innerHTML.trim() && !avatar.querySelector('img')) {
        const userName = avatar.closest('.dropdown-header')?.querySelector('h4')?.textContent || 'User';
        const initials = userName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
        avatar.innerHTML = initials;
        
        // Add hover effect
        avatar.addEventListener('mouseenter', function() {
          this.style.transform = 'scale(1.1) rotate(5deg)';
        });
        
        avatar.addEventListener('mouseleave', function() {
          this.style.transform = 'scale(1) rotate(0deg)';
        });
      }
    });
  }
  
  // Call avatar generation
  generateAvatarInitials();
  
  // Add floating animation to cart
  const cartIcons = qa('.fa-shopping-cart');
  cartIcons.forEach(icon => {
    const parent = icon.parentElement;
    if (parent && !parent.classList.contains('floating-cart')) {
      parent.classList.add('floating-cart');
    }
  });
  
  // Add click animation to buttons
  qa('.btn-gold, .logout-btn, .btn-order-action').forEach(btn => {
    btn.addEventListener('click', function(e) {
      // Create ripple effect
      const ripple = document.createElement('span');
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const x = e.clientX - rect.left - size / 2;
      const y = e.clientY - rect.top - size / 2;
      
      ripple.style.cssText = `
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s linear;
        width: ${size}px;
        height: ${size}px;
        left: ${x}px;
        top: ${y}px;
        pointer-events: none;
      `;
      
      this.appendChild(ripple);
      setTimeout(() => ripple.remove(), 600);
    });
  });
  
  // Add hover effect to dropdown items
  qa('.dropdown-menu a').forEach(item => {
    item.addEventListener('mouseenter', function() {
      this.style.backgroundColor = '#f8f9fa';
      this.style.paddingLeft = '25px';
    });
    
    item.addEventListener('mouseleave', function() {
      this.style.backgroundColor = '';
      this.style.paddingLeft = '20px';
    });
  });
  
  // Notification bell animation
  const notificationBell = q('.fa-bell');
  if (notificationBell) {
    notificationBell.addEventListener('click', function() {
      this.style.animation = 'ring 0.5s ease-in-out';
      setTimeout(() => {
        this.style.animation = '';
      }, 500);
    });
  }

  /* -------------------------
   Menu category filter (if your original script has this, this will complement it)
------------------------- */
  qa(".category-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const cat = this.dataset.category || "all";
      qa(".category-btn").forEach((b) => b.classList.remove("active"));
      this.classList.add("active");
      qa(".menu-item").forEach((item) => {
        const icat = item.dataset.category || "";
        if (cat === "all" || icat === cat) {
          item.style.display = "block";
          item.style.opacity = "1";
        } else {
          item.style.display = "none";
          item.style.opacity = "0";
        }
      });
    });
  });

  // Accessibility: close sidebar on ESC
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      // closeCart(); // Uncomment if you have this function
      // if (addModal) addModal.hide(); // Uncomment if you have this
    }
  });

  // Add CSS animations
  const style = document.createElement('style');
  style.textContent = `
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes ripple-animation {
      to {
        transform: scale(4);
        opacity: 0;
      }
    }
    
    @keyframes ring {
      0% { transform: rotate(0deg); }
      25% { transform: rotate(15deg); }
      50% { transform: rotate(-15deg); }
      75% { transform: rotate(10deg); }
      100% { transform: rotate(0deg); }
    }
    
    .floating-cart {
      animation: float 3s ease-in-out infinite;
    }
    
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-5px); }
      100% { transform: translateY(0px); }
    }
    
    @keyframes slideInRight {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    
    @keyframes slideOutRight {
      from {
        transform: translateX(0);
        opacity: 1;
      }
      to {
        transform: translateX(100%);
        opacity: 0;
      }
    }
    
    @keyframes slideIn {
      from {
        transform: translateX(100%);
        opacity: 0;
      }
      to {
        transform: translateX(0);
        opacity: 1;
      }
    }
    
    @keyframes slideOut {
      from {
        transform: translateX(0);
        opacity: 1;
      }
      to {
        transform: translateX(100%);
        opacity: 0;
      }
    }
  `;
  document.head.appendChild(style);
});

// Cart functionality (updated)
let cart = JSON.parse(localStorage.getItem('cart')) || [];

function updateCartCount() {
  const count = cart.reduce((total, item) => total + item.quantity, 0);
  // Update all cart badges
  qa('.cart-badge').forEach(badge => {
    if (count > 0) {
      badge.textContent = count;
      badge.style.display = 'flex';
    } else {
      badge.style.display = 'none';
    }
  });
  
  // Also update any cart count elements with id cartCount
  const cartCountElement = document.getElementById('cartCount');
  if (cartCountElement) {
    cartCountElement.textContent = count;
    cartCountElement.style.display = count > 0 ? 'block' : 'none';
  }
}

function addToCart(product) {
  const existingItem = cart.find(item => item.id === product.id);
  if (existingItem) {
    existingItem.quantity += product.quantity;
  } else {
    cart.push(product);
  }
  localStorage.setItem('cart', JSON.stringify(cart));
  updateCartCount();
  showAlert('Product added to cart!', 'success');
}

function removeFromCart(productId) {
  cart = cart.filter(item => item.id !== productId);
  localStorage.setItem('cart', JSON.stringify(cart));
  updateCartCount();
  showAlert('Product removed from cart!', 'success');
}

// Show notification (enhanced version)
function showAlert(message, type) {
  // Remove existing alerts
  qa('.notification').forEach(alert => alert.remove());
  qa('.alert-message').forEach(alert => alert.remove());
  
  // Create alert element
  const alertDiv = document.createElement('div');
  alertDiv.className = `notification notification-${type}`;
  
  // Style alert
  alertDiv.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 25px;
    background: ${type === 'success' ? '#4CAF50' : '#f44336'};
    color: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    z-index: 9999;
    animation: slideInRight 0.3s ease-out;
    display: flex;
    align-items: center;
    gap: 10px;
    max-width: 400px;
  `;
  
  const icon = type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>';
  alertDiv.innerHTML = `
    <div style="width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
      ${icon}
    </div>
    <span>${message}</span>
  `;
  
  document.body.appendChild(alertDiv);
  
  // Remove after 3 seconds
  setTimeout(() => {
    alertDiv.style.animation = 'slideOutRight 0.3s ease-out';
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.remove();
      }
    }, 300);
  }, 3000);
}

// Update notification count
function updateNotificationCount(count) {
  qa('.notification-badge').forEach(badge => {
    if (count > 0) {
      badge.textContent = count;
      badge.style.display = 'flex';
    } else {
      badge.style.display = 'none';
    }
  });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
  updateCartCount();
  
  // Add to cart button event listeners
  qa('.btn-add-to-cart').forEach(button => {
    button.addEventListener('click', function() {
      const product = {
        id: this.dataset.id,
        name: this.dataset.name,
        price: parseFloat(this.dataset.price),
        image: this.dataset.image,
        quantity: parseInt(this.dataset.quantity) || 1
      };
      addToCart(product);
    });
  });
  
  // Quantity controls for menu items
  qa('.quantity-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const productId = this.dataset.id;
      const input = document.getElementById(`quantity-${productId}`);
      if (!input) return;
      
      let quantity = parseInt(input.value);
      
      if (this.classList.contains('plus')) {
        quantity = Math.min(10, quantity + 1);
      } else if (this.classList.contains('minus')) {
        quantity = Math.max(1, quantity - 1);
      }
      
      input.value = quantity;
    });
  });
});

// Smooth scroll for anchor links
document.addEventListener('DOMContentLoaded', function() {
  qa('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href');
      if (targetId === '#') return;
      
      const targetElement = document.querySelector(targetId);
      if (targetElement) {
        e.preventDefault();
        window.scrollTo({
          top: targetElement.offsetTop - 80,
          behavior: 'smooth'
        });
      }
    });
  });
});

// Auto-hide alerts after 5 seconds
setTimeout(function() {
  qa('.alert').forEach(alert => {
    const bsAlert = new bootstrap.Alert(alert);
    bsAlert.close();
  });
}, 5000);

console.log("Testimonial page loaded");