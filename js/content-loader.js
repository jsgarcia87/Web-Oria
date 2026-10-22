// js/content-loader.js
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Hacemos la petición a nuestra nueva API
        const response = await fetch('/api/get_content.php');
        const json = await response.json();
        
        if (json.success && json.data) {
            // Buscamos todos los elementos en el HTML que tengan el atributo data-content-id
            // Ejemplo: <h1 data-content-id="titulo_portada">Texto por defecto</h1>
            const elements = document.querySelectorAll('[data-content-id]');
            
            elements.forEach(el => {
                const id = el.getAttribute('data-content-id');
                // Si el ID existe en nuestra base de datos, reemplazamos el texto
                if (json.data[id]) {
                    el.innerHTML = json.data[id];
                }
            });
        }

        // --- INYECCIÓN DE EXTENSIONES (Google Analytics, Clarity, scripts personalizados) ---
        if (json.success && json.extensions) {
            applyExtensions(json.extensions);
        }
    } catch (error) {
        console.error("Error al cargar los contenidos dinámicos:", error);
    }
});

function applyExtensions(ext) {
    if (!ext) return;

    // 1. Google Analytics (GA4)
    if (ext.google_analytics && ext.google_analytics.trim()) {
        const gaId = ext.google_analytics.trim();
        if (!document.querySelector(`script[src*="googletagmanager.com/gtag/js?id=${gaId}"]`)) {
            const gaScript = document.createElement('script');
            gaScript.async = true;
            gaScript.src = `https://www.googletagmanager.com/gtag/js?id=${gaId}`;
            document.head.appendChild(gaScript);

            const gaInit = document.createElement('script');
            gaInit.innerHTML = `
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '${gaId}');
            `;
            document.head.appendChild(gaInit);
        }
    }

    // 2. Microsoft Clarity
    if (ext.clarity && ext.clarity.trim()) {
        const clarityId = ext.clarity.trim();
        if (!window.clarity) {
            const clarityScript = document.createElement('script');
            clarityScript.type = 'text/javascript';
            clarityScript.innerHTML = `
                (function(c,l,a,r,i,t,y){
                    c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                    t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
                    y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
                })(window, document, "clarity", "script", "${clarityId}");
            `;
            document.head.appendChild(clarityScript);
        }
        // Si las cookies ya están aceptadas, enviar consentimiento explícito a Clarity
        if (localStorage.getItem('cookiesAccepted') === 'true' && typeof window.clarity === 'function') {
            window.clarity('consent');
        }
    }

    // 3. Scripts personalizados en <head> (Meta Pixel, Tag Manager, etc.)
    if (ext.custom_head && ext.custom_head.trim()) {
        executeHtmlScripts(ext.custom_head, document.head);
    }

    // 4. Scripts personalizados justo tras la apertura de <body> (Google Tag Manager noscript, etc.)
    if (ext.custom_body_open && ext.custom_body_open.trim()) {
        executeHtmlScripts(ext.custom_body_open, document.body, true);
    }

    // 5. Scripts personalizados antes del fin de <body>
    if (ext.custom_body && ext.custom_body.trim()) {
        executeHtmlScripts(ext.custom_body, document.body, false);
    }
}

function executeHtmlScripts(htmlCode, targetElement, prepend = false) {
    if (!htmlCode) return;
    const temp = document.createElement('div');
    temp.innerHTML = htmlCode;

    const nodes = Array.from(temp.childNodes);
    if (prepend) {
        const refNode = targetElement.firstChild;
        nodes.forEach(node => {
            let toInsert = null;
            if (node.tagName === 'SCRIPT') {
                toInsert = document.createElement('script');
                Array.from(node.attributes).forEach(attr => {
                    toInsert.setAttribute(attr.name, attr.value);
                });
                toInsert.innerHTML = node.innerHTML;
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                toInsert = node.cloneNode(true);
            }
            if (toInsert) {
                targetElement.insertBefore(toInsert, refNode);
            }
        });
    } else {
        nodes.forEach(node => {
            if (node.tagName === 'SCRIPT') {
                const script = document.createElement('script');
                Array.from(node.attributes).forEach(attr => {
                    script.setAttribute(attr.name, attr.value);
                });
                script.innerHTML = node.innerHTML;
                targetElement.appendChild(script);
            } else if (node.nodeType === Node.ELEMENT_NODE) {
                targetElement.appendChild(node.cloneNode(true));
            }
        });
    }
}

// --- TRACKING DE ANALÍTICAS BÁSICO ---
(function() {
    let startTime = Date.now();
    let isTracked = false;

    function sendTrackingData() {
        if (isTracked) return;
        
        let timeSpent = Math.floor((Date.now() - startTime) / 1000);
        let path = window.location.pathname.split('/').pop() || 'index.html';
        
        const data = JSON.stringify({ page: path, time_spent: timeSpent });
        
        // Usamos sendBeacon porque es ideal para enviar datos cuando la pestaña se cierra
        if (navigator.sendBeacon) {
            navigator.sendBeacon('/api/track.php', data);
        } else {
            // Fallback para navegadores antiguos
            fetch('/api/track.php', {
                method: 'POST',
                body: data,
                keepalive: true
            });
        }
        isTracked = true;
    }

    // Enviar datos al cerrar o esconder la pestaña
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            sendTrackingData();
            // Reseteamos el contador por si el usuario vuelve a la pestaña
            startTime = Date.now();
            isTracked = false;
        }
    });

    // Fallback para cuando la ventana se descarga (cerrar o navegar a otra página)
    window.addEventListener('pagehide', sendTrackingData);
})();
