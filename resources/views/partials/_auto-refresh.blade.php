<script>
    (function () {
        const intervalMs = {{ ($seconds ?? 15) * 1000 }};

        setInterval(function () {
            const active = document.activeElement;
            const isTyping = active && ['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName);

            if (!isTyping) {
                window.location.reload();
            }
        }, intervalMs);
    })();
</script>
