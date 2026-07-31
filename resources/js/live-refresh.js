export function liveRefresh(containerId, intervalMs = 4000) {
    setInterval(async () => {
        try {
            const res = await fetch(window.location.href, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return;

            const html = await res.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const nuevo = doc.getElementById(containerId);
            const actual = document.getElementById(containerId);

            if (nuevo && actual) {
                actual.replaceWith(nuevo);
            }
        } catch (e) {
            // silencioso: se reintenta en el siguiente ciclo
        }
    }, intervalMs);
}
