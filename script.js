gsap.registerPlugin(ScrollTrigger);

const slides = Array.from(document.querySelectorAll('.text-slide'));
const scrollTrack = document.querySelector('.scroll-track');

// All slides are visible as containers, but children inside start ready for animation
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
    const slideEndTime = (index + 1) * slideTime;
    
    // Smooth, subtle parallax movement for the entire slide (35px instead of 100px)
    const startY = index === 0 ? 0 : 35;
    const endY = -35;
    
    animeTl.add({
        targets: slide,
        translateY: [startY, endY],
        duration: slideTime,
        easing: 'easeInOutQuad'
    }, slideStartTime);
    
    // Slide container fades
    if (index === 0) {
        // First slide fades out near the end of its window
        animeTl.add({
            targets: slide,
            opacity: [1, 0],
            duration: 500,
            easing: 'easeInOutQuad'
        }, slideEndTime - 500);
    } else {
        // Subsequent slides fade in smoothly
        animeTl.add({
            targets: slide,
            opacity: [0, 1],
            duration: 500,
            easing: 'easeInOutQuad'
        }, slideStartTime - 250);

        // Fade out before next slide, unless it is the very last slide
        if (index < slides.length - 1) {
            animeTl.add({
                targets: slide,
                opacity: [1, 0],
                duration: 500,
                easing: 'easeInOutQuad'
            }, slideEndTime - 500);
        }
    }
    
    const slideElements = Array.from(slide.querySelectorAll('h2, p'));
    if (slideElements.length === 0) return;
    
    slideElements.forEach((el, i) => {
        if (index === 0) {
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
        } else {
            // Text elements appear quickly at the beginning of the slide window
            // so there is a long reading hold where 100% of the text is visible
            const elementStartTime = slideStartTime + (i * 90);
            
            el.style.opacity = '0';
            el.style.transform = 'translateY(18px)';
            
            animeTl.add({
                targets: el,
                opacity: [0, 1],
                translateY: [18, 0],
                duration: 450, 
                easing: 'easeOutCubic'
            }, elementStartTime);
        }
    });
});

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

// Smooth fade out for both text-stage and video-stage as form-section enters
const textStage = document.querySelector('.text-stage');
const videoStage = document.querySelector('.video-stage');
const formSection = document.querySelector('.form-section');

gsap.to([textStage, videoStage], {
    opacity: 0,
    ease: "power2.inOut",
    scrollTrigger: {
        trigger: formSection,
        start: "top 100%",
        end: "top 55%",
        scrub: true
    }
});

// Smooth staggered fade in and gentle upward slide for the contents of form-section
const formElements = formSection.querySelectorAll('h2, .mad-libs-form, .form-footer');
gsap.fromTo(formElements, {
    opacity: 0,
    y: 40
}, {
    opacity: 1,
    y: 0,
    stagger: 0.08,
    ease: "power2.out",
    scrollTrigger: {
        trigger: formSection,
        start: "top 80%",
        end: "top 25%",
        scrub: true
    }
});


