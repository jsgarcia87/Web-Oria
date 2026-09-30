# 001 — Parallax narrativo tipo Casa Althay

- **Status**: TODO
- **Commit**: c9d0f70
- **Severity**: HIGH
- **Category**: Physicality & origin / Easing / Missed opportunities
- **Estimated scope**: 3 files (`script.js`, `style.css`, `index.html`), ~60 líneas netas

## Problem

Tres defectos se combinan y hacen que el scroll narrativo actual no lea como Casa Althay (https://casaalthay.com):

1. **El parallax apenas se percibe.** El rango es demasiado corto (±25% / ±30% del alto del propio media) para que la diferencia de velocidad entre imagen y texto sea perceptible dentro del viewport de un chapter (100vh).
   ```js
   // script.js:46 — current
   document.querySelectorAll('[data-parallax]').forEach(el => {
       const strength = parseFloat(el.dataset.parallax) || 0.25;
       const trigger = el.closest('.chapter');
       if (!trigger) return;
       gsap.fromTo(el,
           { yPercent: -strength * 100 },
           {
               yPercent: strength * 100,
               ease: 'none',
               scrollTrigger: {
                   trigger,
                   start: 'top bottom',
                   end: 'bottom top',
                   scrub: true
               }
           }
       );
   });
   ```
   ```html
   <!-- index.html — current data-parallax values -->
   <!-- chapter-1: data-parallax="0.25" (foto)   -->
   <!-- chapter-2: data-parallax="0.3"  (SVG 01) -->
   <!-- chapter-3: data-parallax="0.3"  (SVG 02) -->
   <!-- chapter-4: data-parallax="0.3"  (SVG 03) -->
   <!-- chapter-5: data-parallax="0.25" (video)  -->
   ```

2. **Fase de salida usa `power2.in` sobre UI.** GSAP `power2.in` es equivalente a `cubic-bezier(0.11, 0, 0.5, 0)` — arranca lento y termina rápido. Regla dura del playbook: en UI, `ease-in` siempre es un finding: el elemento aguanta casi opaco hasta el final del scroll y desaparece de golpe.
   ```js
   // script.js:37-42 — current
   // Phase 3 – exit (0.65 → 1.00): fade out & rise further
   tl.to(parts, {
       opacity: 0, y: -40,
       ease: 'power2.in',
       duration: 0.35
   }, 0.65);
   ```

3. **La imagen aparece con fade puro, sin reveal.** Casa Althay descubre las imágenes con una máscara (curtain-up) sincronizada con la entrada — el fade solo es blando y plano. En este proyecto la máscara existe como potencial (`.chapter-visual` ya tiene `overflow: hidden`) pero no se usa.
   ```css
   /* style.css:181-190 — current */
   .chapter-visual {
       position: relative;
       width: 100%;
       max-width: 520px;
       height: min(42vh, 380px);
       overflow: hidden;
       display: flex;
       align-items: center;
       justify-content: center;
   }
   ```

4. **Bloque de texto no tiene contra-parallax.** Texto e imagen scrollean a la misma velocidad → todo el chapter se percibe como un bloque plano. Con un contra-parallax leve (yPercent opuesto y de menor magnitud) sobre `.chapter-text`, el texto lee "sujeto" mientras la imagen "viaja", que es la firma de casaalthay.

## Target

Un único timeline scrubbed por chapter en el que:

- La **imagen/video** viaja `yPercent ±40` (foto/video) o `±35` (SVGs) durante todo el chapter — parallax perceptible.
- El **texto** hace contra-parallax `yPercent 8 → -8` (opuesto, más pequeño) durante todo el chapter.
- La **entrada del visual** usa un `clip-path` de curtain-up (`inset(0 0 100% 0)` → `inset(0 0 0 0)`) durante la fase enter, con `power2.out`.
- El **fade** de texto y visual conserva `power2.out` en enter y **cambia a `power2.out`** en exit (elimina `power2.in`).
- La **`y` del fade** deja de ser 40px absolutos y pasa a `yPercent: 6` (relativo al elemento), para que escale con títulos vs. párrafos.

Valores exactos, todos citables:

- Easing enter: `power2.out` — equivalente GSAP de `cubic-bezier(0.23, 1, 0.32, 1)` (strong ease-out para UI, del playbook §2).
- Easing exit: `power2.out` — misma curva; nada de `power2.in` sobre UI.
- Easing parallax + reveal: sigue `ease: 'none'` (scrub controla la curva perceptible) salvo el clip-path reveal que va en la fase enter con `power2.out`.
- Strengths de parallax (atributo `data-parallax` en HTML):
  - `chapter-1` foto Jorge → `0.4`
  - `chapter-2` ilustración 01 → `0.35`
  - `chapter-3` ilustración 02 → `0.35`
  - `chapter-4` ilustración 03 → `0.35`
  - `chapter-5` video ORIA → `0.4`
- Contra-parallax de texto: constante `TEXT_PARALLAX = -0.08` en JS (yPercent 8 → -8, opuesto a la imagen del mismo chapter).
- Timeline de reveal (dentro de la timeline del chapter): `clip-path: inset(0 0 100% 0)` a `inset(0 0 0 0)`, duración `0.35`, ease `power2.out`, posición `0` (mismo start que el fade enter).

CSS diff (target):
```css
/* style.css — target */
.chapter-visual {
    position: relative;
    width: 100%;
    max-width: 520px;
    height: min(42vh, 380px);
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    clip-path: inset(0 0 100% 0);      /* nuevo — estado inicial del reveal */
    will-change: clip-path, transform;  /* nuevo */
}
```

JS diff (target — bloque completo de chapters + parallax reescrito):
```js
// script.js — target
gsap.registerPlugin(ScrollTrigger);
gsap.config({ nullTargetWarn: false });

const TEXT_PARALLAX = -0.08; // texto contra-parallax (opuesto y más pequeño)

// CHAPTERS — timeline scrubbed con enter (fade+reveal) → hold → exit (fade out).
document.querySelectorAll('.chapter').forEach(chapter => {
    const text = chapter.querySelector('.chapter-text');
    const visual = chapter.querySelector('.chapter-visual');
    const parts = [text, visual].filter(Boolean);
    if (!parts.length) return;

    gsap.set(parts, { opacity: 0, yPercent: 6 });
    if (visual) gsap.set(visual, { clipPath: 'inset(0 0 100% 0)' });

    const tl = gsap.timeline({
        scrollTrigger: {
            trigger: chapter,
            start: 'top bottom',
            end: 'bottom top',
            scrub: 0.8
        }
    });

    // Phase 1 – enter (0.00 → 0.35): fade + rise + reveal
    tl.to(parts, {
        opacity: 1, yPercent: 0,
        ease: 'power2.out',
        duration: 0.35
    }, 0);
    if (visual) {
        tl.to(visual, {
            clipPath: 'inset(0 0 0 0)',
            ease: 'power2.out',
            duration: 0.35
        }, 0);
    }

    // Phase 2 – hold (0.35 → 0.65)
    tl.to(parts, {
        opacity: 1, yPercent: 0,
        duration: 0.30
    }, 0.35);

    // Phase 3 – exit (0.65 → 1.00): fade out & rise further — power2.out (nunca power2.in)
    tl.to(parts, {
        opacity: 0, yPercent: -6,
        ease: 'power2.out',
        duration: 0.35
    }, 0.65);
});

// PARALLAX — media viaja con strength positivo, texto viaja opuesto y más suave.
document.querySelectorAll('[data-parallax]').forEach(el => {
    const strength = parseFloat(el.dataset.parallax) || 0.25;
    const trigger = el.closest('.chapter');
    if (!trigger) return;
    gsap.fromTo(el,
        { yPercent: -strength * 100 },
        {
            yPercent: strength * 100,
            ease: 'none',
            scrollTrigger: {
                trigger,
                start: 'top bottom',
                end: 'bottom top',
                scrub: true
            }
        }
    );
});

// CONTRA-PARALLAX del texto — mismo scrub que el media pero opuesto.
document.querySelectorAll('.chapter-text').forEach(el => {
    const trigger = el.closest('.chapter');
    if (!trigger) return;
    gsap.fromTo(el,
        { yPercent: -TEXT_PARALLAX * 100 },   // = +8
        {
            yPercent: TEXT_PARALLAX * 100,    // = -8
            ease: 'none',
            scrollTrigger: {
                trigger,
                start: 'top bottom',
                end: 'bottom top',
                scrub: true
            }
        }
    );
});

// FORM SECTION — sin cambios respecto a la versión previa.
const formSection = document.querySelector('.form-section');
if (formSection) {
    const formElements = formSection.querySelectorAll('h2, .mad-libs-form, .form-footer');
    if (formElements.length) {
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
    }
}

window.addEventListener('load', () => ScrollTrigger.refresh());
```

HTML diff (target — solo los atributos `data-parallax`):
```html
<!-- index.html — target -->
<!-- chapter-1: data-parallax="0.4"   (foto)   -->
<!-- chapter-2: data-parallax="0.35"  (SVG 01) -->
<!-- chapter-3: data-parallax="0.35"  (SVG 02) -->
<!-- chapter-4: data-parallax="0.35"  (SVG 03) -->
<!-- chapter-5: data-parallax="0.4"   (video)  -->
```

## Repo conventions to follow

- **GSAP + ScrollTrigger** ya cargados desde CDN en `index.html:214-215` (`gsap.min.js` y `ScrollTrigger.min.js` v3.12.2). No añadir nuevas dependencias.
- **Un solo `script.js`** (no hay bundler ni módulos). El nuevo código sustituye por completo el existente, salvo el bloque `FORM SECTION` y el `window.addEventListener('load', …)` finales, que se conservan tal cual.
- **Cache-busting manual con `?v=N`** en el `<link rel="stylesheet">` y en `<script src="script.js?v=N">`. Al editar CSS o JS **hay que subir el número** (`?v=15` → `?v=16` en style; `?v=4` → `?v=5` en script). Ejemplo actual: `index.html:47` (`style.css?v=15`) y `index.html:216` (`script.js?v=4`).
- **Todos los `data-parallax` viven en `index.html`** dentro de cada `.chapter-visual > (img|video).chapter-media`. Actualmente están en las líneas 118, 129, 139, 149, 160 aproximadamente (buscar por `data-parallax=` para localizar exactamente).
- **`.chapter-visual`** ya usa `overflow: hidden` y `will-change: transform` en el ancestro `.chapter-media` (`style.css:171-177`). Añadir `will-change: clip-path, transform` al `.chapter-visual` es el sitio correcto.
- **No hay tokens de motion** todavía; no crear ninguno en este plan (es scope de otro plan futuro). Los valores van inline como en el resto del código.
- **Ejemplar de referencia** dentro del repo: [script.js:65-85](../script.js#L65) (bloque `FORM SECTION`) muestra el patrón `gsap.fromTo` + `scrollTrigger` scrubbed que este plan repite. Copiar ese estilo (indentación de 4 espacios, comentarios en mayúsculas de sección).

## Steps

1. Abrir `index.html` y actualizar los valores del atributo `data-parallax` en los 5 elementos `chapter-media`:
   - Línea aproximada 118 (foto Jorge): `data-parallax="0.25"` → `data-parallax="0.4"`.
   - Línea aproximada 129 (SVG 01): `data-parallax="0.3"` → `data-parallax="0.35"`.
   - Línea aproximada 139 (SVG 02): `data-parallax="0.3"` → `data-parallax="0.35"`.
   - Línea aproximada 149 (SVG 03): `data-parallax="0.3"` → `data-parallax="0.35"`.
   - Línea aproximada 160 (video): `data-parallax="0.25"` → `data-parallax="0.4"`.

2. En el mismo `index.html`, subir el cache-buster de `script.js`:
   - `index.html:216` — `<script src="script.js?v=4"></script>` → `<script src="script.js?v=5"></script>`.

3. Subir el cache-buster de `style.css`:
   - `index.html:47` — `<link rel="stylesheet" href="style.css?v=15">` → `<link rel="stylesheet" href="style.css?v=16">`.

4. Abrir `style.css` y localizar el bloque `.chapter-visual` (empieza en `style.css:181` aprox., termina cuando aparece `}` seguido de línea en blanco). Reemplazarlo por:
   ```css
   .chapter-visual {
       position: relative;
       width: 100%;
       max-width: 520px;
       height: min(42vh, 380px);
       overflow: hidden;
       display: flex;
       align-items: center;
       justify-content: center;
       clip-path: inset(0 0 100% 0);
       will-change: clip-path, transform;
   }
   ```
   Es decir: añadir las dos últimas propiedades (`clip-path` y `will-change`). No tocar nada más del bloque.

5. Abrir `script.js`. Sustituir **todo el contenido desde la línea 1 hasta la línea 63 inclusive** (bloques `registerPlugin`, `config`, `// CHAPTERS —` completo y `// PARALLAX —` completo, hasta la última `});` del parallax) por el bloque JS listado arriba en la sección **Target** (todo lo que va antes del comentario `// FORM SECTION —`). Conservar sin tocar:
   - El bloque `// FORM SECTION —` completo (script.js:65-85).
   - La última línea `window.addEventListener('load', () => ScrollTrigger.refresh());` (script.js:87).

6. Verificar que el archivo `script.js` final contiene, en este orden:
   1. `gsap.registerPlugin(ScrollTrigger);`
   2. `gsap.config({ nullTargetWarn: false });`
   3. `const TEXT_PARALLAX = -0.08;`
   4. Bloque `// CHAPTERS —` reescrito.
   5. Bloque `// PARALLAX —` reescrito.
   6. Bloque `// CONTRA-PARALLAX del texto —` (nuevo).
   7. Bloque `// FORM SECTION —` intacto.
   8. `window.addEventListener('load', …)` intacto.

## Boundaries

- **NO tocar** `.chapter`, `.chapter-text`, `.chapter-media`, `.chapter-illustration`, `.chapter-video` en CSS (solo `.chapter-visual`).
- **NO tocar** el contenido HTML de los chapters (ni copy, ni etiquetas, ni orden). Solo los valores de `data-parallax` y los `?v=` de asset cache-busting.
- **NO tocar** `form-section`, `mad-libs-form`, sidebar, badge, ni el inline script del formulario al final de `index.html`.
- **NO añadir** nuevas dependencias (npm, CDN, bundler). GSAP + ScrollTrigger ya están cargados.
- **NO crear tokens** de motion (`--ease-*`, `--duration-*`) — eso es scope de otro plan.
- **NO añadir** `prefers-reduced-motion` — eso es otro plan (accessibility).
- **NO cambiar** `scrub: 0.8` a otro valor. Ese smoothing ya está calibrado.
- Si algún fichero o línea no coincide con lo descrito (drift desde el commit `c9d0f70`), **PARAR y reportar** — no improvisar.

## Verification

**Mechanical:**
```bash
# desde la raíz del repo
python3 -m http.server 8087
# abrir http://localhost:8087
```
No hay typecheck ni build. La única verificación mecánica es que la consola del navegador NO tenga errores tras cargar:
- Abrir DevTools → Console.
- Recargar con Ctrl+Shift+R (hard reload para saltar caché).
- Esperado: 0 errores en rojo; puede haber warnings de Typekit sobre red lenta (ignorar).

**Feel check** (obligatorio, se hace en navegador con la web servida):

1. **Parallax perceptible**: scrollear lentamente con la rueda del ratón desde el inicio. Observar en el chapter 1: la foto de Jorge y el texto deben separarse visualmente al scrollear — la foto se mueve MÁS que el texto, en la MISMA dirección; el texto parece "quedarse atrás". Repetir en los chapters 2, 3, 4 con las ilustraciones y en el 5 con el video.

2. **Reveal curtain-up**: en cada transición de un chapter al siguiente, la imagen entrante debe descubrirse de abajo hacia arriba (bottom-up), no aparecer entera de golpe con fade. Poner DevTools → Rendering → Animation Inspector y bajar la velocidad al 25% para verlo claro.

3. **Exit sin sluggish**: al salir un chapter, el texto NO debe quedarse a opacity 0.9 hasta el último momento. Debe desvanecerse pronto y suavemente a lo largo del último tercio del scroll del chapter. Comparar antes/después del cambio.

4. **Sin saltos ni jank**: scrollear rápido de arriba abajo. No debe haber ni flashes de contenido ni tirones. En DevTools → Performance → grabar 5 segundos de scroll: `Frames` debe mantenerse ≥ 55 fps sostenidos.

5. **Sin cambios en el layout del formulario**: al llegar al `form-section`, la entrada de campos debe seguir igual (stagger 0.08, fade + rise). Si notas diferencia, es una regresión — revisar step 5 (probablemente se tocó el bloque form).

6. **Comparar con Casa Althay**: abrir https://casaalthay.com/casa-althay/ en otra pestaña. Scrollear la sección de las casitas y luego scrollear la web ORIA. La sensación de "algo se mueve a otra velocidad" debe estar presente en ambas — no idéntica pero de la misma familia.

**Done when:**
- Los 5 chapters muestran parallax visible entre imagen y texto (feel check 1).
- Cada imagen entra con curtain-up (feel check 2).
- Ningún elemento hace la salida sluggish del `power2.in` original (feel check 3).
- Sin errores en consola tras hard-reload (mechanical).
- 55+ fps sostenidos durante scroll rápido (feel check 4).
