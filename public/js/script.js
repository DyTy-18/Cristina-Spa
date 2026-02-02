// Cristina Spa - Premium JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // ===== Campaign Popup =====
    const campaignPopup = document.getElementById('campaign-popup');
    const campaignClose = document.querySelector('.campaign-close');
    const campaignOverlay = document.querySelector('.campaign-overlay');
    const campaignLink = document.querySelector('.campaign-link');
    
    // Show popup after 2 seconds (only once per session)
    if (campaignPopup && !sessionStorage.getItem('campaignShown')) {
        setTimeout(() => {
            campaignPopup.classList.add('active');
            document.body.style.overflow = 'hidden';
        }, 2000);
    }
    
    // Close popup function
    const closePopup = () => {
        if (campaignPopup) {
            campaignPopup.classList.remove('active');
            document.body.style.overflow = '';
            sessionStorage.setItem('campaignShown', 'true');
        }
    };
    
    // Close popup events
    if (campaignClose) {
        campaignClose.addEventListener('click', closePopup);
    }
    if (campaignOverlay) {
        campaignOverlay.addEventListener('click', closePopup);
    }
    if (campaignLink) {
        campaignLink.addEventListener('click', closePopup);
    }
    
    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && campaignPopup && campaignPopup.classList.contains('active')) {
            closePopup();
        }
    });

    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const navLinks = document.querySelectorAll('.nav-menu a');
    const header = document.querySelector('.header');

    // Toggle mobile menu
    if (navToggle) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });
    }

    // Close menu when clicking on a link
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (navMenu.classList.contains('active')) {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            }
        });
    });

    // Smooth scroll with offset for fixed header
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            
            // Only handle anchor links
            if (targetId && targetId.startsWith('#')) {
                e.preventDefault();
                const targetSection = document.querySelector(targetId);
                
                if (targetSection) {
                    const headerHeight = document.querySelector('.header').offsetHeight;
                    const targetPosition = targetSection.offsetTop - headerHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Header scroll effect
    let lastScroll = 0;
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        lastScroll = currentScroll;
    });

    // Form submission handler
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const formData = new FormData(contactForm);
            const nombre = contactForm.querySelector('input[type="text"]').value;
            
            // Show success message with animation
            const submitBtn = contactForm.querySelector('.submit-button');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = '¡Enviado!';
            submitBtn.style.background = 'linear-gradient(135deg, #4CAF50, #45a049)';
            
            setTimeout(() => {
                alert(`¡Gracias ${nombre}! Te contactaremos pronto para confirmar tu cita en Cristina Spa.`);
                contactForm.reset();
                submitBtn.textContent = originalText;
                submitBtn.style.background = '';
            }, 1000);
        });
    }

    // Intersection Observer for fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Animate elements on scroll
    const animateElements = (selector, staggerDelay = 0.1) => {
        const elements = document.querySelectorAll(selector);
        elements.forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = `all 0.6s ease ${index * staggerDelay}s`;
            observer.observe(el);
        });
    };

    // Apply animations to various sections
    animateElements('.service-card', 0.1);
    animateElements('.package-card', 0.15);
    animateElements('.branch-card', 0.1);
    animateElements('.gallery-item', 0.08);
    animateElements('.about-stats .stat-item', 0.15);

    // Animate section titles and labels
    const sectionTitles = document.querySelectorAll('.section-title, .section-label');
    sectionTitles.forEach(title => {
        title.style.opacity = '0';
        title.style.transform = 'translateY(20px)';
        title.style.transition = 'all 0.6s ease';
        observer.observe(title);
    });

    // Parallax effect for hero section
    const hero = document.querySelector('.hero');
    if (hero) {
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * 0.3;
            
            if (scrolled < window.innerHeight) {
                hero.style.backgroundPositionY = `calc(center + ${rate}px)`;
            }
        });
    }

    // WhatsApp button tooltip animation
    const whatsappBtn = document.querySelector('.whatsapp-float');
    if (whatsappBtn) {
        // Show tooltip briefly on page load
        setTimeout(() => {
            const tooltip = whatsappBtn.querySelector('.whatsapp-tooltip');
            if (tooltip) {
                tooltip.style.opacity = '1';
                tooltip.style.visibility = 'visible';
                
                setTimeout(() => {
                    tooltip.style.opacity = '';
                    tooltip.style.visibility = '';
                }, 3000);
            }
        }, 2000);
    }

    // Package cards hover effect enhancement
    const packageCards = document.querySelectorAll('.package-card');
    packageCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.zIndex = '10';
        });
        card.addEventListener('mouseleave', function() {
            setTimeout(() => {
                this.style.zIndex = '';
            }, 300);
        });
    });

    // Counter animation for stats
    const animateCounter = (element, target, duration = 2000) => {
        let start = 0;
        const increment = target / (duration / 16);
        
        const updateCounter = () => {
            start += increment;
            if (start < target) {
                element.textContent = Math.floor(start) + '+';
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target + '+';
            }
        };
        
        updateCounter();
    };

    // Observe stats for counter animation
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const statNumbers = entry.target.querySelectorAll('.stat-number');
                statNumbers.forEach(stat => {
                    const text = stat.textContent;
                    const number = parseInt(text);
                    if (!isNaN(number) && !stat.dataset.animated) {
                        stat.dataset.animated = 'true';
                        stat.textContent = '0+';
                        animateCounter(stat, number);
                    }
                });
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    const aboutStats = document.querySelector('.about-stats');
    if (aboutStats) {
        statsObserver.observe(aboutStats);
    }
});
