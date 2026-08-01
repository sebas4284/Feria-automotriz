export function scrollRestore() {
    const key = 'scrollPos:' + window.location.pathname;

    window.addEventListener('beforeunload', () => {
        sessionStorage.setItem(key, window.scrollY);
    });

    const saved = sessionStorage.getItem(key);
    if (saved !== null) {
        window.scrollTo(0, parseInt(saved, 10));
        sessionStorage.removeItem(key);
    }
}
