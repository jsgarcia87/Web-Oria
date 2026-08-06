gsap.registerPlugin(ScrollTrigger);
gsap.config({ nullTargetWarn: false });

// ============== TEXT SPLITTING ==============
function splitIntoWords(element) {
    const walker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT, null);
    const textNodes = [];
    while (walker.nextNode()) textNodes.push(walker.currentNode);

    textNodes.forEach(node => {
        const words = node.textContent.split(/(\s+)/);
        const fragment = document.createDocumentFragment();
        words.forEach(word => {
            if (/^\s+$/.test(word)) {
                fragment.appendChild(document.createTextNode(word));
            } else if (word) {
                const wrapper = document.createElement('span');
                wrapper.className = 'word-wrap';
                const inner = document.createElement('span');
                inner.className = 'word-inner';
                inner.textContent = word;
                wrapper.appendChild(inner);
                fragment.appendChild(wrapper);
            }
        });
        node.parentNode.replaceChild(fragment, node);
    });
}

// ============== SCRAMBLE / DECODE EFFECT (pure rAF, no GSAP) ==============
function scrambleText(wordElements, totalDuration) {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const getRandomChar = () => chars[Math.floor(Math.random() * chars.length)];
    totalDuration = (totalDuration || 0.6) * 1000;

    wordElements.forEach((word, i) => {
        const original = word.textContent;
        const letters = original.split('');
        const delay = i * 60;

        word.style.opacity = '0';

        setTimeout(() => {
            word.style.opacity = '1';
            const startTime = performance.now();

            const animate = () => {
                const elapsed = performance.now() - startTime;
                const progress = Math.min(elapsed / totalDuration, 1);

                if (progress < 1) {
                    word.textContent = letters.map((char, j) => {
                        const threshold = (j / letters.length) * 0.5 + 0.4;
                        return progress > threshold ? char : getRandomChar();
                    }).join('');
                    requestAnimationFrame(animate);
                } else {
                    word.textContent = original;
                }
            };
            requestAnimationFrame(animate);
        }, delay);
    });
}

// ============== SETUP ==============
const slides = Array.from(document.querySelectorAll('.text-slide'));
const scrollTrack = document.querySelector('.scroll-track');

slides.forEach(slide => {
    slide.style.visibility = 'visible';
    slide.style.opacity = '1';
    const textEls = slide.querySelectorAll('h2, p');
    textEls.forEach(el => splitIntoWords(el));
});

const slideH2Words = slides.map(s => Array.from(s.querySelectorAll('h2 .word-inner')));
const slidePWords = slides.map(s => Array.from(s.querySelectorAll('p .word-inner')));
const slideAllWords = slides.map(s => Array.from(s.querySelectorAll('.word-inner')));

// Initial state
slides.forEach((slide, i) => {
    if (i === 0) {
        gsap.set(slideAllWords[i], { yPercent: 0 });
    } else {
        gsap.set(slide, { opacity: 0 });
        if (slidePWords[i].length > 0) gsap.set(slidePWords[i], { yPercent: 110 });
        if (slideH2Words[i].length > 0) gsap.set(slideH2Words[i], { yPercent: 110 });
    }
});

// ============== MASTER TIMELINE (scroll-scrubbed) ==============
const tl = gsap.timeline({
    scrollTrigger: {
        trigger: scrollTrack,
        start: 'top top',
        end: 'bottom bottom',
        scrub: 1
    }
});

const totalSlides = slides.length;
const unit = 1;

slides.forEach((slide, index) => {
    const start = index * unit;
    const allWords = slideAllWords[index];
    const wordCount = allWords.length;
    if (wordCount === 0) return;
    const staggerSpread = Math.min(wordCount * 0.004, 0.18);

    if (index === 0) {
        tl.fromTo(slide, { y: 0 }, { y: -25, duration: unit, ease: 'none' }, start);

        tl.to(allWords, {
            yPercent: -110,
            stagger: { amount: staggerSpread },
            duration: unit * 0.2,
            ease: 'power2.in'
        }, start + unit * 0.78);

        tl.to(slide, { opacity: 0, duration: unit * 0.05 }, start + unit * 0.95);

    } else {
        // --- ENTRY ---
        tl.to(slide, { opacity: 1, duration: unit * 0.01 }, start);

        tl.to(allWords, {
            yPercent: 0,
            stagger: { amount: staggerSpread },
            duration: unit * 0.12,
            ease: 'power2.out'
        }, start + unit * 0.02);

        tl.fromTo(slide, { y: 25 }, {
            y: -25, duration: unit, ease: 'none'
        }, start);

        // --- EXIT ---
        if (index < totalSlides - 1) {
            const exitStart = start + unit * 0.78;

            tl.to(allWords, {
                yPercent: -110,
                stagger: { amount: staggerSpread },
                duration: unit * 0.2,
                ease: 'power2.in'
            }, exitStart);

            tl.to(slide, { opacity: 0, duration: unit * 0.05 }, exitStart + unit * 0.2);
        }
    }
});

// ============== SCRAMBLE TRIGGERS (time-based, fired once) ==============
const scrambled = new Set();

if (slideH2Words[0].length > 0) {
    scrambled.add(0);
    setTimeout(() => scrambleText(slideH2Words[0], 0.8), 400);
}

slides.forEach((slide, index) => {
    if (index === 0 || slideH2Words[index].length === 0) return;

    const triggerStart = (index / totalSlides) * 100;

    ScrollTrigger.create({
        trigger: scrollTrack,
        start: `${triggerStart}% top`,
        end: `${triggerStart + 5}% top`,
        onEnter: () => {
            if (scrambled.has(index)) return;
            scrambled.add(index);
            scrambleText(slideH2Words[index], 0.8);
        },
        onLeaveBack: () => {
            scrambled.delete(index);
        }
    });
});

// ============== FORM SECTION TRANSITIONS ==============
const textStage = document.querySelector('.text-stage');
const videoStage = document.querySelector('.video-stage');
const formSection = document.querySelector('.form-section');

gsap.to([textStage, videoStage], {
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
