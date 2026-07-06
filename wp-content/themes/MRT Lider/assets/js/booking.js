document.addEventListener('DOMContentLoaded', function () {
    const bookingBtns = document.querySelectorAll('.booking-btn');
    const bookingPopup = document.getElementById('booking');
    const closeBtn = bookingPopup.querySelector('.booking__close-btn');
    const overlay = bookingPopup.querySelector('.booking__overlay');
    const form = bookingPopup.querySelector('.booking__form');

    let scrollPosition = 0; // Сохраняем позицию скролла

    // Открытие попапа
    bookingBtns.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            // Сохраняем текущую позицию скролла
            scrollPosition = window.scrollY;

            // Фиксируем body, убираем скролл, сохраняя позицию
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';

            // Показываем попап
            bookingPopup.classList.add('show');
            setTimeout(() => {
                form.classList.add('show');
            }, 10);
        });
    });

    // Закрытие попапа
    function closePopup() {
        form.classList.remove('show');

        setTimeout(() => {
            bookingPopup.classList.remove('show');

            // Возвращаем скролл
            document.body.style.position = '';
            document.body.style.width = '';

            // Возвращаемся на сохранённую позицию
            window.scrollTo(0, scrollPosition);
        }, 500); // Должно совпадать с длительностью анимации
    }

    closeBtn.addEventListener('click', closePopup);

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) {
            closePopup();
        }
    });

    // Закрытие по Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && bookingPopup.classList.contains('show')) {
            closePopup();
        }
    });
});