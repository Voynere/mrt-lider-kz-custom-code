document.addEventListener('DOMContentLoaded', function() {
    // Находим все элементы аккордеона
    const accordionItems = document.querySelectorAll('.price__item');
    
    // Добавляем обработчики кликов
    accordionItems.forEach(item => {
        const btn = item.querySelector('.price__item-category');
        const content = item.querySelector('.price__item-wrapper');
        if (!btn || !content) {
            return;
        }

        btn.addEventListener('click', function() {
            // Закрываем все другие открытые элементы
            accordionItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                    otherItem.querySelector('.price__item-category').classList.remove('active');
                    otherItem.querySelector('.price__item-wrapper').classList.remove('active');
                }
            });
            
            // Переключаем состояние текущего элемента
            item.classList.toggle('active');
            btn.classList.toggle('active');
            content.classList.toggle('active');
        });
    });
});