<div class="booking" id="booking">
    <div class="booking__overlay">
        <form class="booking__form" action="#">
            <button type="button" class="booking__close-btn" aria-label="Закрыть">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6L18 18" stroke="#404040" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>
            <div class="booking__form-wrapper">
                <input type="text" class="booking__form-input" placeholder="Введите имя" required>
                <input type="text" class="booking__form-input" placeholder="Введите телефон" required>
                <input type="text" class="booking__form-input" placeholder="Кличка и вид питомца">
                <button class="booking__form-btn btn-blue" type="submit">
                    <p><?php echo esc_html($booking_cta ?? 'Записаться на приём'); ?></p>
                </button>
            </div>
            <p class="booking__form-privacy">
                Нажимая на кнопку, вы автоматически соглашаетесь с
                <a href="<?php echo esc_url(home_url('/privacy/')); ?>">Политикой обработки персональных данных.</a>
            </p>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.booking__form');
    if (!form || form.dataset.submitListenerAdded) {
        return;
    }
    form.dataset.submitListenerAdded = 'true';

    const phoneInput = form.querySelector('input[placeholder="Введите телефон"]');
    let phoneMaskInstance = null;
    if (phoneInput && typeof IMask !== 'undefined') {
        phoneMaskInstance = IMask(phoneInput, {
            mask: '+{7} (000) 000-00-00',
            lazy: false,
            placeholderChar: '_'
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const nameInput = form.querySelector('input[placeholder="Введите имя"]');
        const petInput = form.querySelector('input[placeholder="Кличка и вид питомца"]');
        const name = nameInput ? nameInput.value.trim() : '';
        let phone = '';

        if (phoneInput) {
            if (phoneMaskInstance && phoneMaskInstance.unmaskedValue) {
                phone = phoneMaskInstance.unmaskedValue;
                if (phone.startsWith('7')) {
                    phone = '+7' + phone.substring(1);
                } else if (phone.startsWith('8')) {
                    phone = '+7' + phone.substring(1);
                } else {
                    phone = '+7' + phone;
                }
            } else {
                phone = phoneInput.value.replace(/[^\d\+]/g, '');
            }
        }

        if (!name) {
            alert('Пожалуйста, введите Ваше имя.');
            if (nameInput) nameInput.focus();
            return;
        }

        const phoneDigits = phone.replace(/[^\d]/g, '');
        if (phoneDigits.length < 10 || phoneDigits.length > 11) {
            alert('Пожалуйста, введите корректный номер телефона.');
            if (phoneInput) phoneInput.focus();
            return;
        }

        const data = new FormData();
        data.append('action', 'send_booking_form');
        data.append('name', name);
        data.append('phone', phone);
        data.append('pet', petInput ? petInput.value.trim() : '');
        data.append('nonce', '<?php echo wp_create_nonce('booking_form_nonce'); ?>');
        if (typeof window.mrtAppendUtmToFormData === 'function') {
            window.mrtAppendUtmToFormData(data);
        }

        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            body: data
        })
        .then(response => response.text())
        .then(result => {
            if (result.indexOf('успешно') !== -1 && typeof window.mrtReachGoal === 'function') {
                window.mrtReachGoal('animals_booking_submit');
            }
            alert(result);
            form.reset();
        })
        .catch(() => alert('Ошибка отправки формы. Пожалуйста, попробуйте еще раз.'));
    });
});
</script>
