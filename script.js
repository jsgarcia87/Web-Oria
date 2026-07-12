gsap.registerPlugin(ScrollTrigger);

const slides = Array.from(document.querySelectorAll('.text-slide'));
const scrollTrack = document.querySelector('.scroll-track');

// 1. Prepare DOM: Split sentences into individual words for anime.js stagger
const sentences = document.querySelectorAll('.sentence');
sentences.forEach(sentence => {
    const text = sentence.textContent.trim();
    if (!text) return;
    
    const words = text.split(/\s+/);
    sentence.innerHTML = ''; // clear original text
    
    words.forEach((word, idx) => {
        const wordSpan = document.createElement('span');
        wordSpan.className = 'word';
        wordSpan.textContent = word;
        wordSpan.style.display = 'inline-block';
        wordSpan.style.opacity = '0'; // start completely hidden
        wordSpan.style.transform = 'translateY(10px)';
        wordSpan.style.willChange = 'opacity, transform';
        
        sentence.appendChild(wordSpan);
        
        if (idx < words.length - 1) {
            sentence.appendChild(document.createTextNode(' '));
        }
    });
});

slides.forEach((slide, i) => {
    slide.style.visibility = 'visible';
    slide.style.opacity = i === 0 ? '1' : '0';
});

// 2. Build Anime.js Master Timeline
const tlDuration = 10000; 
const animeTl = anime.timeline({
    autoplay: false,
    duration: tlDuration,
    easing: 'linear'
});

const slideTime = tlDuration / slides.length; 

slides.forEach((slide, index) => {
    const slideStartTime = index * slideTime;
    
    // Fade in the current slide container
    if (index > 0) {
        animeTl.add({
            targets: slide,
            opacity: [0, 1],
            duration: 200,
            easing: 'linear'
        }, slideStartTime);
    }

    // Fade out previous slide container
    if (index > 0) {
        animeTl.add({
            targets: slides[index - 1],
            opacity: [1, 0],
            duration: 200,
            easing: 'linear'
        }, slideStartTime - 100);
    }
    
    const slideSentences = Array.from(slide.querySelectorAll('.sentence'));
    if (slideSentences.length === 0) return;
    
    const activeSlideTime = slideTime - 400; 
    const sentenceTime = activeSlideTime / slideSentences.length;
    
    slideSentences.forEach((sentence, i) => {
        const words = sentence.querySelectorAll('.word');
        const sentenceStartTime = slideStartTime + 200 + (i * sentenceTime);
        const isFirstSentence = (index === 0 && i === 0);
        
        if (isFirstSentence) {
            // First sentence is fully lit immediately
            words.forEach(w => {
                w.style.opacity = '1';
                w.style.transform = 'translateY(0px)';
            });
        } else {
            // Smoothly reveal words, and leave them illuminated
            animeTl.add({
                targets: words,
                opacity: [0, 1],
                translateY: [10, 0],
                duration: 600, // slightly longer for elegance
                delay: anime.stagger(30), 
                easing: 'easeOutQuad'
            }, sentenceStartTime);
        }
        
        // Removed the dimming block: words now stay fully visible
    });
    
    // Removed the block that dims the last sentence at the end of the slide
});

// Fade out the very last slide before the form appears
animeTl.add({
    targets: slides[slides.length - 1],
    opacity: [1, 0],
    duration: 300,
    easing: 'linear'
}, tlDuration - 300);

// Initialize state
animeTl.seek(0);

// 3. Link Anime.js Timeline to GSAP ScrollTrigger
ScrollTrigger.create({
    trigger: scrollTrack,
    start: "top top",
    end: "bottom bottom",
    scrub: 1.5, // Added a slight smooth scrub delay for extra elegance
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
