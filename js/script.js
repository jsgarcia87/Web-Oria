// Initialize Lenis smooth scrolling
const lenis = new Lenis({
    duration: 1.2, // Casa Althay style smooth float
    easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)), // smooth easeOutQuint
    orientation: 'vertical',
    gestureOrientation: 'vertical',
    smoothWheel: true,
    wheelMultiplier: 0.8,
    smoothTouch: false,
    touchMultiplier: 2,
    infinite: false,
});

lenis.on('scroll', ScrollTrigger.update);

gsap.ticker.add((time)=>{
  lenis.raf(time * 1000);
});

gsap.ticker.lagSmoothing(0);

gsap.registerPlugin(ScrollTrigger);
gsap.config({ nullTargetWarn: false });

// Fade in chapters with premium parallax feel and ease in-out
document.querySelectorAll('.chapter').forEach(chapter => {
    const texts = chapter.querySelectorAll('.chapter-text, .cta-link');
    const visual = chapter.querySelector('.chapter-visual');

    if (texts.length) {
        gsap.fromTo(texts, 
            { opacity: 0, y: 100 },
            {
                opacity: 1, 
                y: 0, 
                duration: 1.5, 
                stagger: 0.1,
                ease: 'power2.inOut',
                scrollTrigger: {
                    trigger: chapter,
                    start: 'top 85%',
                    toggleActions: 'play none none none'
                }
            }
        );
    }

    if (visual) {
        gsap.fromTo(visual, 
            { opacity: 0, y: 120 },
            {
                opacity: 1, 
                y: 0, 
                duration: 1.5, 
                ease: 'power2.inOut',
                delay: 0.15,
                scrollTrigger: {
                    trigger: chapter,
                    start: 'top 85%',
                    toggleActions: 'play none none none'
                }
            }
        );
    }
});

// Parallax panning for media inside their containers
document.querySelectorAll('.chapter-media').forEach(media => {
    const strength = parseFloat(media.dataset.parallax) || 0.15;
    const trigger = media.closest('.chapter');
    if (!trigger) return;
    
    // Animate the image panning vertically
    gsap.fromTo(media,
        { yPercent: -strength * 50 },
        {
            yPercent: strength * 50,
            ease: 'none',
            scrollTrigger: {
                trigger: trigger,
                start: 'top bottom',
                end: 'bottom top',
                scrub: 0.5 // Restored original smooth scrub for images
            }
        }
    );
});

// Pin Sucesion wrapper so it doesn't conflict with fade-in
const sucesionWrapper = document.querySelector('.sucesion-pin-wrapper');
if (sucesionWrapper) {
    ScrollTrigger.create({
        trigger: sucesionWrapper,
        start: 'top 15%',
        endTrigger: '#area-sucesion',
        end: 'bottom bottom',
        pin: true,
        pinSpacing: false,
        pinType: "transform" // Absolute smoothness with Lenis
    });
}

// FORM SECTION — text rises and fades in together as it arrives.
const formSection = document.querySelector('.form-section');
if (formSection) {
    const formElements = formSection.querySelectorAll('h2, .mad-libs-form, .form-footer');
    if (formElements.length) {
        gsap.fromTo(formElements,
            { opacity: 0, y: 40 },
            {
                opacity: 1, y: 0,
                duration: 1.2,
                stagger: 0.15,
                ease: 'power3.out',
                scrollTrigger: {
                    trigger: formSection,
                    start: 'top 80%',
                    toggleActions: 'play none none none'
                }
            }
        );
    }
}

window.addEventListener('load', () => ScrollTrigger.refresh());

// Mobile menu toggle
const hamburgerBtn = document.querySelector('.hamburger-btn');
const socialLinks = document.querySelector('.social-links');

if (hamburgerBtn && socialLinks) {
    hamburgerBtn.addEventListener('click', () => {
        const isOpen = hamburgerBtn.classList.toggle('menu-open');
        socialLinks.classList.toggle('menu-open');
        document.body.classList.toggle('no-scroll');
        if (isOpen) { lenis.stop(); } else { lenis.start(); }
    });

    // Close menu when clicking a link
    socialLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            hamburgerBtn.classList.remove('menu-open');
            socialLinks.classList.remove('menu-open');
            document.body.classList.remove('no-scroll');
            lenis.start();
        });
    });
}

// Cookie Banner Logic
document.addEventListener('DOMContentLoaded', () => {
    const banner = document.getElementById('cookie-banner');
    const acceptBtn = document.getElementById('accept-cookies');
    
    if (banner && acceptBtn) {
        if (!localStorage.getItem('cookiesAccepted')) {
            banner.style.display = 'flex';
            // Pequeño delay para la animación de entrada
            setTimeout(() => {
                banner.classList.add('show');
            }, 500);
        }
        
        acceptBtn.addEventListener('click', () => {
            localStorage.setItem('cookiesAccepted', 'true');
            banner.classList.remove('show');
            setTimeout(() => {
                banner.style.display = 'none';
            }, 400); // Esperar a que termine la animación
        });
    }
});
