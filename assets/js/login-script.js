// Form submission handling
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const role = document.getElementById('role').value;
            
            if(!username || !password || !role) {
                showAlert('Harap isi semua field sebelum masuk!', 'error');
                return;
            }
            
            // Submit form
            this.submit();
        });
    }
});

// Fungsi untuk menampilkan alert
function showAlert(message, type) {
    // Hapus alert yang sudah ada
    const existingAlert = document.querySelector('.alert-message');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    // Buat elemen alert
    const alertElement = document.createElement('div');
    alertElement.className = `alert-message alert-${type}`;
    
    // Tentukan ikon berdasarkan tipe
    let icon = '';
    if (type === 'error') {
        icon = '<i class="fas fa-exclamation-circle"></i>';
    } else {
        icon = '<i class="fas fa-check-circle"></i>';
    }
    
    alertElement.innerHTML = `${icon} <span>${message}</span>`;
    
    // Tambahkan ke document
    document.body.appendChild(alertElement);
    
    // Hapus setelah 5 detik
    setTimeout(() => {
        if (alertElement.parentNode) {
            alertElement.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(() => {
                if (alertElement.parentNode) {
                    alertElement.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Fungsi untuk menyesuaikan layout agar tidak ada scroll vertikal
function adjustLayout() {
    const container = document.querySelector('.container');
    const loginContainer = document.querySelector('.login-container');
    
    if (container && loginContainer) {
        // Periksa jika konten lebih tinggi dari viewport
        if (container.offsetHeight > window.innerHeight) {
            // Kurangi padding/margin
            loginContainer.style.padding = '15px 18px';
            document.querySelector('.header').style.marginBottom = '10px';
            
            // Kurangi ukuran font untuk header
            const headerH1 = document.querySelector('.header h1');
            if (headerH1) {
                headerH1.style.fontSize = '1.8rem';
            }
            
            // Kurangi tinggi maksimum tabel
            const tableContainer = document.querySelector('.role-table-container');
            if (tableContainer) {
                tableContainer.style.maxHeight = '150px';
                tableContainer.style.overflowY = 'auto';
            }
        } else {
            // Reset ke default
            loginContainer.style.padding = '';
            document.querySelector('.header').style.marginBottom = '';
            
            const headerH1 = document.querySelector('.header h1');
            if (headerH1) {
                headerH1.style.fontSize = '';
            }
            
            const tableContainer = document.querySelector('.role-table-container');
            if (tableContainer) {
                tableContainer.style.maxHeight = '';
                tableContainer.style.overflowY = '';
            }
        }
    }
}

// Atur layout saat load dan resize
window.addEventListener('load', adjustLayout);
window.addEventListener('resize', adjustLayout);

// Shortcut keyboard (Ctrl+Enter untuk submit form)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'Enter') {
        const signinButton = document.querySelector('.signin-button');
        if (signinButton) signinButton.click();
    }
});

// Pilih role secara otomatis berdasarkan username (simulasi)
document.addEventListener('DOMContentLoaded', function() {
    const usernameInput = document.getElementById('username');
    const roleSelect = document.getElementById('role');
    
    if (usernameInput && roleSelect) {
        usernameInput.addEventListener('blur', function() {
            const username = this.value.toLowerCase();
            
            // Simulasi deteksi role berdasarkan username
            if (username.includes('admin') || username.includes('manager') || 
                username.includes('kevin') || username.includes('rani') || username.includes('mugy')) {
                roleSelect.value = 'admin';
            } else if (username.includes('chef') || username.includes('koki')) {
                roleSelect.value = 'chef';
            } else if (username.includes('cashier') || username.includes('kasir')) {
                roleSelect.value = 'cashier';
            } else if (username.includes('waiter') || username.includes('pelayan')) {
                roleSelect.value = 'waiter';
            } else if (username.includes('customer') || username.includes('user')) {
                roleSelect.value = 'customer';
            }
        });
    }
});