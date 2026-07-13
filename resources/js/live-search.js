export function liveSearch(initial = '') {
    return {
        q: initial,
        matches(el) {
            const term = this.q.trim().toLowerCase();
            return !term || el.dataset.search.includes(term);
        },
        get visibleCount() {
            return [...this.$el.querySelectorAll('[data-search]')]
                .filter((el) => this.matches(el)).length;
        },
    };
}
