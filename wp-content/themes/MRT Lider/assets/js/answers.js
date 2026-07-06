document.addEventListener('DOMContentLoaded', function() {
    // Находим все элементы аккордеона
    const accordionItems = document.querySelectorAll('.answers__tabs-item');
    
    // Добавляем обработчики кликов
    accordionItems.forEach(item => {
        const btn = item.querySelector('.answers__tabs-btn');
        const content = item.querySelector('.answers__tabs-text');
        
        btn.addEventListener('click', function() {
            // Закрываем все другие открытые элементы
            accordionItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                    otherItem.querySelector('.answers__tabs-btn').classList.remove('active');
                    otherItem.querySelector('.answers__tabs-text').classList.remove('active');
                }
            });
            
            // Переключаем состояние текущего элемента
            item.classList.toggle('active');
            btn.classList.toggle('active');
            content.classList.toggle('active');
        });
    });
});



document.addEventListener('DOMContentLoaded', function() {
    const customSelects = document.querySelectorAll('.answers__select');

    customSelects.forEach(select => {
        const trigger = select.querySelector('.answers__select-trigger');
        const options = select.querySelectorAll('.answers__select-item'); // Исправлено с -option на -item
        const originalSelect = select.querySelector('.original-select');
        const triggerSpan = trigger.querySelector('span');

        // Открытие/закрытие селекта
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.answers__select').forEach(s => {
                if (s !== select) s.classList.remove('open');
            });
            select.classList.toggle('open');
        });

        // Выбор опции
        options.forEach(option => {
            option.addEventListener('click', function() {
                options.forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                triggerSpan.textContent = this.textContent;
                originalSelect.value = this.dataset.value;
                select.classList.remove('open');
                
                const event = new Event('change');
                originalSelect.dispatchEvent(event);
            });
        });

        // Закрытие при клике вне селекта
        document.addEventListener('click', function(e) {
            if (!select.contains(e.target)) {
                select.classList.remove('open');
            }
        });

        // Синхронизация с оригинальным select
        originalSelect.addEventListener('change', function() {
            const selectedOption = select.querySelector(`.answers__select-item[data-value="${this.value}"]`);
            if (selectedOption) {
                options.forEach(opt => opt.classList.remove('selected'));
                selectedOption.classList.add('selected');
                triggerSpan.textContent = selectedOption.textContent;
            }
        });
        
        // Инициализация текущего значения
        const currentValue = originalSelect.value;
        if (currentValue) {
            const selectedOption = select.querySelector(`.answers__select-item[data-value="${currentValue}"]`);
            if (selectedOption) {
                options.forEach(opt => opt.classList.remove('selected'));
                selectedOption.classList.add('selected');
                triggerSpan.textContent = selectedOption.textContent;
            }
        }
    });
});