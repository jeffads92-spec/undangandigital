/**
 * ROYAL ELEGANCE - MAIN JAVASCRIPT
 * Premium Wedding Template - Interactive Features
 * Version: 2.0.0
 */

(function() {
    'use strict';
    
    // ============================================
    // INITIALIZATION
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        initNavigation();
        initCountdown();
        initMusicPlayer();
        initScrollAnimations();
        initBackToTop();
        initLazyLoading();
        initFormValidation();
        initShareButtons();
        initGallery();
        
        console.log('✨ Royal Elegance Template Loaded Successfully');
    });
    
    // ============================================
    // NAVIGATION
    // ============================================
    function initNavigation() {
        const nav = document.querySelector('.main-nav');
        const navToggle = document.getElementById('navToggle');
        const navMenu = document.getElementById('navMenu');
        const navLinks = document.querySelectorAll('.nav-link');
        
        // Sticky navigation on scroll
        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
        
        // Mobile menu toggle
        if (navToggle && navMenu) {
            navToggle.addEventListener('click', function() {
                navMenu.classList.toggle('active');
                
                const icon = this.querySelector('i');
                if (navMenu.classList.contains('active')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-times');
                } else {
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });
        }
        
        // Close mobile menu on link click
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    navMenu.classList.remove('active');
                    navToggle.querySelector('i').classList.remove('fa-times');
                    navToggle.querySelector('i').classList.add('fa-bars');
                }
                
                // Update active state
                navLinks.forEach(l => l.classList.remove('active'));
                this.classList.add('active');
            });
        });
        
        // Smooth scroll to section
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetSection = document.getElementById(targetId);
                
                if (targetSection) {
                    const offsetTop = targetSection.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });
        
        // Highlight active section on scroll
        const sections = document.querySelectorAll('.section');
        window.addEventListener('scroll', function() {
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop - 100;
                if (window.scrollY >= sectionTop) {
                    current = section.getAttribute('id');
                }
            });
            
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href').substring(1) === current) {
                    link.classList.add('active');
                }
            });
        });
    }
    
    // ============================================
    // COUNTDOWN TIMER
    // ============================================
    function initCountdown() {
        const countdownElement = document.querySelector('.countdown-timer');
        if (!countdownElement) return;
        
        const weddingDate = countdownElement.getAttribute('data-date');
        if (!weddingDate) return;
        
        const countdownDate = new Date(weddingDate).getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = countdownDate - now;
            
            if (distance < 0) {
                document.getElementById('days').textContent = '00';
                document.getElementById('hours').textContent = '00';
                document.getElementById('minutes').textContent = '00';
                document.getElementById('seconds').textContent = '00';
                return;
            }
            
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            document.getElementById('days').textContent = String(days).padStart(2, '0');
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        }
        
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }
    
    // ============================================
    // MUSIC PLAYER
    // ============================================
    function initMusicPlayer() {
        const audio = document.getElementById('weddingAudio');
        const playBtn = document.getElementById('playBtn');
        const songSelect = document.getElementById('songSelect');
        
        if (!audio) return;
        
        // Auto-play on user interaction
        let hasInteracted = false;
        const autoPlay = function() {
            if (!hasInteracted) {
                audio.play().catch(e => {
                    console.log('Autoplay prevented:', e);
                });
                hasInteracted = true;
                document.removeEventListener('click', autoPlay);
                document.removeEventListener('scroll', autoPlay);
            }
        };
        
        document.addEventListener('click', autoPlay);
        document.addEventListener('scroll', autoPlay);
        
        // Play/Pause button
        if (playBtn) {
            playBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleMusic();
            });
        }
        
        // Song selection
        if (songSelect) {
            songSelect.addEventListener('change', function() {
                const songId = this.value;
                if (songId) {
                    changeSong(songId);
                }
            });
        }
        
        // Update button state
        audio.addEventListener('play', function() {
            if (playBtn) {
                playBtn.innerHTML = '<i class="fas fa-pause"></i>';
            }
        });
        
        audio.addEventListener('pause', function() {
            if (playBtn) {
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
            }
        });
        
        // Volume control
        audio.volume = 0.5;
    }
    
    window.toggleMusic = function() {
        const audio = document.getElementById('weddingAudio');
        const playBtn = document.getElementById('playBtn');
        
        if (audio.paused) {
            audio.play();
            if (playBtn) {
                playBtn.innerHTML = '<i class="fas fa-pause"></i>';
            }
        } else {
            audio.pause();
            if (playBtn) {
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
            }
        }
    };
    
    window.changeSong = function(songId) {
        console.log('Changing to song ID:', songId);
        // Implementation would fetch song URL from backend
    };
    
    // ============================================
    // SCROLL ANIMATIONS
    // ============================================
    function initScrollAnimations() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.animate-on-scroll').forEach(element => {
            observer.observe(element);
        });
    }
    
    // ============================================
    // BACK TO TOP BUTTON
    // ============================================
    function initBackToTop() {
        const backToTop = document.getElementById('backToTop');
        if (!backToTop) return;
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });
    }
    
    window.scrollToTop = function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    };
    
    // ============================================
    // LAZY LOADING IMAGES
    // ============================================
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        const src = img.getAttribute('data-src');
                        
                        if (src) {
                            img.src = src;
                            img.removeAttribute('data-src');
                            img.classList.add('loaded');
                        }
                        
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        } else {
            // Fallback for older browsers
            document.querySelectorAll('img[data-src]').forEach(img => {
                img.src = img.getAttribute('data-src');
            });
        }
    }
    
    // ============================================
    // FORM VALIDATION
    // ============================================
    function initFormValidation() {
        const forms = document.querySelectorAll('form[data-validate]');
        
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const inputs = form.querySelectorAll('[required]');
                let isValid = true;
                
                inputs.forEach(input => {
                    if (!validateField(input)) {
                        isValid = false;
                    }
                });
                
                if (isValid) {
                    // Submit form via AJAX or normal submit
                    if (form.hasAttribute('data-ajax')) {
                        submitFormAjax(form);
                    } else {
                        form.submit();
                    }
                }
            });
            
            // Real-time validation
            const inputs = form.querySelectorAll('[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    validateField(this);
                });
                
                input.addEventListener('input', function() {
                    if (this.classList.contains('is-invalid')) {
                        validateField(this);
                    }
                });
            });
        });
    }
    
    function validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        let isValid = true;
        let errorMessage = '';
        
        // Required validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'Field ini wajib diisi';
        }
        
        // Email validation
        else if (type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Format email tidak valid';
            }
        }
        
        // Phone validation
        else if (field.name === 'phone' && value) {
            const phoneRegex = /^[0-9+\-\s()]{10,}$/;
            if (!phoneRegex.test(value)) {
                isValid = false;
                errorMessage = 'Format nomor telepon tidak valid';
            }
        }
        
        // Min length validation
        else if (field.hasAttribute('minlength')) {
            const minLength = parseInt(field.getAttribute('minlength'));
            if (value.length < minLength) {
                isValid = false;
                errorMessage = `Minimal ${minLength} karakter`;
            }
        }
        
        // Update UI
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            removeErrorMessage(field);
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            showErrorMessage(field, errorMessage);
        }
        
        return isValid;
    }
    
    function showErrorMessage(field, message) {
        removeErrorMessage(field);
        
        const error = document.createElement('div');
        error.className = 'error-message';
        error.style.color = '#e74c3c';
        error.style.fontSize = '0.85em';
        error.style.marginTop = '5px';
        error.textContent = message;
        
        field.parentNode.appendChild(error);
    }
    
    function removeErrorMessage(field) {
        const error = field.parentNode.querySelector('.error-message');
        if (error) {
            error.remove();
        }
    }
    
    async function submitFormAjax(form) {
        const formData = new FormData(form);
        const action = form.getAttribute('action');
        
        try {
            const response = await fetch(action, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showNotification('success', result.message || 'Data berhasil dikirim!');
                form.reset();
                form.querySelectorAll('.is-valid').forEach(el => {
                    el.classList.remove('is-valid');
                });
            } else {
                showNotification('error', result.message || 'Terjadi kesalahan');
            }
        } catch (error) {
            showNotification('error', 'Gagal mengirim data. Silakan coba lagi.');
            console.error('Form submission error:', error);
        }
    }
    
    // ============================================
    // SHARE BUTTONS
    // ============================================
    function initShareButtons() {
        // Share buttons implementation
    }
    
    window.shareWhatsApp = function() {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent(document.title);
        window.open(`https://wa.me/?text=${text}%20${url}`, '_blank');
    };
    
    window.shareFacebook = function() {
        const url = encodeURIComponent(window.location.href);
        window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank');
    };
    
    window.shareTelegram = function() {
        const url = encodeURIComponent(window.location.href);
        const text = encodeURIComponent(document.title);
        window.open(`https://t.me/share/url?url=${url}&text=${text}`, '_blank');
    };
    
    window.copyLink = async function() {
        try {
            await navigator.clipboard.writeText(window.location.href);
            showNotification('success', 'Link berhasil disalin!');
        } catch (err) {
            showNotification('error', 'Gagal menyalin link');
        }
    };
    
    // ============================================
    // GALLERY
    // ============================================
    function initGallery() {
        const galleryItems = document.querySelectorAll('.gallery-item');
        
        galleryItems.forEach((item, index) => {
            item.addEventListener('click', function() {
                openLightbox(index);
            });
        });
    }
    
    window.openLightbox = function(index) {
        // Lightbox implementation
        console.log('Opening lightbox for image:', index);
    };
    
    // ============================================
    // NOTIFICATIONS
    // ============================================
    function showNotification(type, message) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            animation: slideInRight 0.3s ease;
            border-left: 4px solid ${type === 'success' ? '#27ae60' : '#e74c3c'};
        `;
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };
        
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <i class="fas ${icons[type]}" style="font-size: 1.5em; color: ${type === 'success' ? '#27ae60' : '#e74c3c'};"></i>
                <span style="color: #333;">${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // ============================================
    // UTILITY FUNCTIONS
    // ============================================
    
    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Throttle function
    function throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
    
    // Format number
    function formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }
    
    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }
    
    // Export utilities to window
    window.RoyalElegance = {
        showNotification,
        debounce,
        throttle,
        formatNumber,
        formatCurrency
    };
    
    // ============================================
    // ANIMATIONS
    // ============================================
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(100%);
            }
        }
        
        .form-control.is-invalid {
            border-color: #e74c3c;
        }
        
        .form-control.is-valid {
            border-color: #27ae60;
        }
        
        img.loaded {
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    `;
    document.head.appendChild(style);
    
})();

// End of Royal Elegance JavaScript
