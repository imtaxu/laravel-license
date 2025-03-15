/**
 * Lisans Yönetim Paneli JavaScript
 */

// DOM yüklendikten sonra çalışacak kodlar
document.addEventListener('DOMContentLoaded', function() {
    // Bootstrap tooltip'leri etkinleştir
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Alert'leri otomatik kapat
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Lisans oluşturma formu için domain kontrolü
    var domainInput = document.getElementById('domain');
    if (domainInput) {
        domainInput.addEventListener('blur', function() {
            validateDomain(this.value);
        });
    }
    
    // Lisans oluşturma formu için e-posta kontrolü
    var emailInput = document.getElementById('owner_email');
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            validateEmail(this.value);
        });
    }
    
    // Lisans detay modalı için lisans anahtarı kopyalama
    var copyButtons = document.querySelectorAll('.copy-license-key');
    copyButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var licenseKey = this.getAttribute('data-license-key');
            copyToClipboard(licenseKey);
            
            // Kopyalandı bildirimi
            var originalText = this.innerHTML;
            this.innerHTML = '<i class="bi bi-check"></i> Kopyalandı';
            
            setTimeout(function() {
                button.innerHTML = originalText;
            }, 2000);
        });
    });
});

/**
 * Domain geçerliliğini kontrol eder
 * 
 * @param {string} domain Domain
 * @return {boolean}
 */
function validateDomain(domain) {
    var domainInput = document.getElementById('domain');
    var feedback = domainInput.nextElementSibling;
    
    // Domain boş mu?
    if (!domain) {
        setInvalidFeedback(domainInput, feedback, 'Domain alanı zorunludur.');
        return false;
    }
    
    // Domain formatı geçerli mi?
    var domainRegex = /^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,}$/;
    if (!domainRegex.test(domain)) {
        setInvalidFeedback(domainInput, feedback, 'Geçerli bir domain giriniz (örn: example.com).');
        return false;
    }
    
    // Geçerli domain
    setValidFeedback(domainInput, feedback, 'Domain formatı geçerli.');
    return true;
}

/**
 * E-posta geçerliliğini kontrol eder
 * 
 * @param {string} email E-posta
 * @return {boolean}
 */
function validateEmail(email) {
    var emailInput = document.getElementById('owner_email');
    var feedback = emailInput.nextElementSibling;
    
    // E-posta boş mu?
    if (!email) {
        setInvalidFeedback(emailInput, feedback, 'E-posta alanı zorunludur.');
        return false;
    }
    
    // E-posta formatı geçerli mi?
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        setInvalidFeedback(emailInput, feedback, 'Geçerli bir e-posta adresi giriniz.');
        return false;
    }
    
    // Geçerli e-posta
    setValidFeedback(emailInput, feedback, 'E-posta formatı geçerli.');
    return true;
}

/**
 * Geçersiz form geri bildirimi ayarlar
 * 
 * @param {HTMLElement} input Form elemanı
 * @param {HTMLElement} feedback Geri bildirim elemanı
 * @param {string} message Mesaj
 */
function setInvalidFeedback(input, feedback, message) {
    input.classList.add('is-invalid');
    input.classList.remove('is-valid');
    
    if (feedback) {
        feedback.innerHTML = message;
        feedback.classList.add('invalid-feedback');
        feedback.classList.remove('valid-feedback');
    }
}

/**
 * Geçerli form geri bildirimi ayarlar
 * 
 * @param {HTMLElement} input Form elemanı
 * @param {HTMLElement} feedback Geri bildirim elemanı
 * @param {string} message Mesaj
 */
function setValidFeedback(input, feedback, message) {
    input.classList.add('is-valid');
    input.classList.remove('is-invalid');
    
    if (feedback) {
        feedback.innerHTML = message;
        feedback.classList.add('valid-feedback');
        feedback.classList.remove('invalid-feedback');
    }
}

/**
 * Metni panoya kopyalar
 * 
 * @param {string} text Kopyalanacak metin
 */
function copyToClipboard(text) {
    var tempInput = document.createElement('input');
    tempInput.value = text;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
}
