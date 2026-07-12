gsap.registerPlugin(ScrollTrigger);

const slides = Array.from(document.querySelectorAll('.text-slide'));
const scrollTrack = document.querySelector('.scroll-track');

// 1. Prepare DOM: Split sentences into individual words for anime.js stagger
const sentences = document.querySelectorAll('.sentence');
sentences.forEach(sentence => {
    const text = sentence.textContent.trim();
    if (!text) return;
    
    const hasUnderline = sentence.classList.contains('underline');
    if (hasUnderline) {
        sentence.style.textDecoration = 'none';
        sentence.classList.remove('underline');
    }
    
    const words = text.split(/\s+/);
    sentence.innerHTML = ''; 
    
    words.forEach((word, idx) => {
        const wordSpan = document.createElement('span');
        wordSpan.className = 'word';
        // Append space directly to the word to keep underline continuous
        wordSpan.textContent = word + (idx < words.length - 1 ? ' ' : '');
        wordSpan.style.display = 'inline-block';
        wordSpan.style.whiteSpace = 'pre-wrap'; // Preserves the trailing space
        wordSpan.style.opacity = '0'; 
        wordSpan.style.transform = 'translateY(15px)';
        wordSpan.style.willChange = 'opacity, transform';
        
        if (hasUnderline) {
            wordSpan.style.textDecoration = 'underline';
            wordSpan.style.textUnderlineOffset = '4px';
        }
        
        sentence.appendChild(wordSpan);
    });
});

// All slides are visible as containers, but words inside are opaque 0
slides.forEach(slide => {
    slide.style.visibility = 'visible';
    slide.style.opacity = '1'; 
});

// 2. Build Anime.js Master Timeline
const tlDuration = 20000; // Increased virtual duration for better math
const animeTl = anime.timeline({
    autoplay: false,
    duration: tlDuration,
    easing: 'linear'
});

const slideTime = tlDuration / slides.length; // 4000ms per slide

slides.forEach((slide, index) => {
    const slideStartTime = index * slideTime;
    
    // Slide container fades (if not the first one)
    if (index > 0) {
        // Fade out previous slide
        animeTl.add({
            targets: slides[index - 1],
            opacity: [1, 0],
            duration: 400,
            easing: 'linear'
        }, slideStartTime - 200);

        // Fade in current slide
        animeTl.add({
            targets: slide,
            opacity: [0, 1],
            duration: 400,
            easing: 'linear'
        }, slideStartTime);
    } else {
        // First slide is already visible at opacity 1
        slide.style.opacity = '1';
    }
    
    const slideSentences = Array.from(slide.querySelectorAll('.sentence'));
    if (slideSentences.length === 0) return;
    
    // CRITICAL FIX: Allocate a large portion of the slide's scroll time 
    // strictly for PAUSiNG so the completed paragraph stays on screen longer.
    const pauseTime = 1800; // 45% of the slide time is just resting
    const activeSlideTime = slideTime - pauseTime - 400; // Time left for text revealing
    const sentenceTime = activeSlideTime / slideSentences.length;
    
    slideSentences.forEach((sentence, i) => {
        const words = sentence.querySelectorAll('.word');
        const sentenceStartTime = slideStartTime + 200 + (i * sentenceTime);
        
        // Smoothly reveal words, no dimming!
        animeTl.add({
            targets: words,
            opacity: [0, 1],
            translateY: [15, 0],
            duration: 800, // smoother fade
            delay: anime.stagger(40), // slightly more gap between words
            easing: 'easeOutQuad'
        }, sentenceStartTime);
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

// 3. Link Anime.js Timeline to GSAP ScrollTrigger
ScrollTrigger.create({
    trigger: scrollTrack,
    start: "top top",
    end: "bottom bottom",
    scrub: 1.5,
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
