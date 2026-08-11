gsap.registerPlugin(ScrollTrigger);

const slides = Array.from(document.querySelectorAll('.text-slide'));
const scrollTrack = document.querySelector('.scroll-track');

slides.forEach((slide, i) => {
    slide.style.visibility = 'visible';
    if (i === 0) {
        slide.style.opacity = '1';
    } else {
        gsap.set(slide, { opacity: 0, y: 40 });
    }
});

const tl = gsap.timeline({
    scrollTrigger: {
        trigger: scrollTrack,
        start: 'top top',
        end: 'bottom bottom',
        scrub: 0.8
    }
});

const totalSlides = slides.length;
const unit = 1;

slides.forEach((slide, index) => {
    const start = index * unit;

    if (index === 0) {
        // First slide exits with crossfade into slide 2
        tl.to(slide, {
            opacity: 0,
            y: -30,
            duration: unit * 0.3,
            ease: 'power2.inOut'
        }, start + unit * 0.7);

    } else {
        tl.fromTo(slide,
            { opacity: 0, y: 30 },
            { opacity: 1, y: 0, duration: unit * 0.3, ease: 'power2.out' },
            start - unit * 0.2
        );

        // Exit: crossfade with next slide
        if (index < totalSlides - 1) {
            tl.to(slide, {
                opacity: 0,
                y: -30,
                duration: unit * 0.3,
                ease: 'power2.inOut'
            }, start + unit * 0.7);
        }
    }
});

const textStage = document.querySelector('.text-stage');
const visualStage = document.querySelector('.visual-stage');
const formSection = document.querySelector('.form-section');

gsap.to([textStage, visualStage], {
    opacity: 0,
    ease: 'power2.inOut',
    scrollTrigger: {
        trigger: formSection,
        start: 'top 100%',
        end: 'top 55%',
        scrub: true
    }
});

const formElements = formSection.querySelectorAll('h2, .mad-libs-form, .form-footer');
gsap.fromTo(formElements,
    { opacity: 0, y: 40 },
    {
        opacity: 1, y: 0,
        stagger: 0.08,
        ease: 'power2.out',
        scrollTrigger: {
            trigger: formSection,
            start: 'top 80%',
            end: 'top 25%',
            scrub: true
        }
    }
);
