function filterProducts() {
    const searchInput = document.getElementById('search-input').value.toLowerCase();
    const categoryFilter = document.getElementById('category-filter').value;
    const productCards = document.querySelectorAll('.product-card');

    productCards.forEach(card => {
        const name = card.getAttribute('data-name');
        const category = card.getAttribute('data-category');

        const matchText = name.includes(searchInput);
        const matchCategory = categoryFilter === "" || category === categoryFilter;

        card.style.display = (matchText && matchCategory) ? "block" : "none";
    });
}
