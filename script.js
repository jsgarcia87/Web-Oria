gsap.registerPlugin(ScrollTrigger);

// There are 5 text slides and 5 scroll steps.
const slides = document.querySelectorAll('.text-slide');
const steps = document.querySelectorAll('.track-step');

// We fade in the first slide initially
gsap.set(slides[0], { opacity: 1, visibility: 'visible', y: 0 });

// For slides 2 to 5, we link their appearance to the corresponding scroll step
steps.forEach((step, index) => {
    if (index === 0) {
        // Step 1 controls fading out slide 1 and fading in slide 2
        ScrollTrigger.create({
            trigger: step,
            start: "top top",
            end: "bottom top",
            scrub: true,
            animation: gsap.timeline()
                .to(slides[0], { opacity: 0, y: -20, autoAlpha: 0 })
                .fromTo(slides[1], { opacity: 0, y: 20, autoAlpha: 0 }, { opacity: 1, y: 0, autoAlpha: 1 }, "<0.5")
        });
    } else if (index < steps.length - 1) {
        // Steps 2, 3, 4 fade out the current slide and fade in the next
        ScrollTrigger.create({
            trigger: step,
            start: "top top",
            end: "bottom top",
            scrub: true,
            animation: gsap.timeline()
                .to(slides[index], { opacity: 0, y: -20, autoAlpha: 0 })
                .fromTo(slides[index + 1], { opacity: 0, y: 20, autoAlpha: 0 }, { opacity: 1, y: 0, autoAlpha: 1 }, "<0.5")
        });
    } else {
        // The last step (step 5) fades out the last slide before the form appears
        ScrollTrigger.create({
            trigger: step,
            start: "top top",
            end: "bottom top",
            scrub: true,
            animation: gsap.to(slides[index], { opacity: 0, y: -20, autoAlpha: 0 })
        });
    }
});

// Fade out the video as the form section comes into view
const videoStage = document.querySelector('.video-stage');
const formSection = document.querySelector('.form-section');

gsap.to(videoStage, {
    opacity: 0,
    scrollTrigger: {
        trigger: formSection,
        start: "top bottom", // when the top of the form hits the bottom of the viewport
        end: "top center",   // when the top of the form hits the center
        scrub: true
    }
});
