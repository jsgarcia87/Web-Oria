gsap.registerPlugin(ScrollTrigger);

const slides = Array.from(document.querySelectorAll('.text-slide'));
const scrollTrack = document.querySelector('.scroll-track');

// All slides are visible as containers, but children inside are opaque 0
slides.forEach(slide => {
    slide.style.visibility = 'visible';
    slide.style.opacity = '1'; 
});

// Build Anime.js Master Timeline
const tlDuration = 10000; 
const animeTl = anime.timeline({
    autoplay: false,
    duration: tlDuration,
    easing: 'linear'
});

const slideTime = tlDuration / slides.length; 

slides.forEach((slide, index) => {
    const slideStartTime = index * slideTime;
    
    // Parallax movement for the entire slide
    const startY = index === 0 ? 0 : 100;
    const endY = -100;
    
    animeTl.add({
        targets: slide,
        translateY: [startY, endY],
        duration: slideTime,
        easing: 'linear'
    }, slideStartTime);
    
    // Slide container fades (if not the first one)
    if (index > 0) {
        animeTl.add({
            targets: slides[index - 1],
            opacity: [1, 0],
            duration: 600,
            easing: 'linear'
        }, slideStartTime - 600);

        animeTl.add({
            targets: slide,
            opacity: [0, 1],
            duration: 600,
            easing: 'linear'
        }, slideStartTime);
    } else {
        slide.style.opacity = '1';
    }
    
    const slideElements = Array.from(slide.querySelectorAll('h2, p'));
    if (slideElements.length === 0) return;
    
    const activeSlideTime = slideTime * 0.6; 
    const elementTime = activeSlideTime / slideElements.length;
    
    slideElements.forEach((el, i) => {
        if (index === 0) {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        } else {
            const elementStartTime = slideStartTime + 400 + (i * elementTime);
            
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            
            animeTl.add({
                targets: el,
                opacity: [0, 1],
                translateY: [20, 0],
                duration: 1000, 
                easing: 'easeOutQuad'
            }, elementStartTime);
        }
    });
});

// Fade out the very last slide before the form appears
animeTl.add({
    targets: slides[slides.length - 1],
    opacity: [1, 0],
    duration: 600,
    easing: 'linear'
}, tlDuration - 600);

animeTl.seek(0);

// Link Anime.js Timeline to GSAP ScrollTrigger
ScrollTrigger.create({
    trigger: scrollTrack,
    start: "top top",
    end: "bottom bottom",
    scrub: 1,
    onUpdate: (self) => {
        animeTl.seek(animeTl.duration * self.progress);
    }
});

// Video fade out logic
const videoStage = document.querySelector('.video-stage');
const formSection = document.querySelector('.form-section');

gsap.to(videoStage, {
    opacity: 0,
    scrollTrigger: {
        trigger: formSection,
        start: "top bottom",
        end: "top center",
        scrub: true
    }
});

