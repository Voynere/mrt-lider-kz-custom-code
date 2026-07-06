document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('.examination__form form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const name = form.querySelector('input[placeholder="Введите имя"]').value;
            const phone = form.querySelector('input[placeholder="Введите телефон"]').value;

            const data = new FormData();
            data.append('action', 'send_main_form');
            data.append('name', name);
            data.append('phone', phone);
            data.append('nonce', '<?php echo wp_create_nonce("main_form_nonce"); ?>');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: data
            })
            .then(response => response.text())
            .then(result => {
                alert(result);
                form.reset();
            })
            .catch(error => {
                alert('Ошибка отправки формы.');
                console.error('Ошибка:', error);
            });
        });
    }
});