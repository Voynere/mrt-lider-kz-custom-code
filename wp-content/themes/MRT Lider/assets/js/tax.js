jQuery(document).ready(function($) {
    // Функция для применения маски даты
    function applyDateMask(input) {
        input.on('input', function() {
            let value = $(this).val().replace(/\D/g, '');
            if (value.length > 8) value = value.substring(0, 8);
            
            let formatted = '';
            for (let i = 0; i < value.length; i++) {
                if (i === 2 || i === 4) formatted += '.';
                formatted += value[i];
            }
            
            $(this).val(formatted);
        });
    }

    // Применяем маски к полям дат
    $('.tax__section_patient .tax__input[placeholder*="Дата рождения"]').each(function() {
        applyDateMask($(this));
    });
    $('.tax__section_patient .tax__input[placeholder*="Дата выдачи документа"]').each(function() {
        applyDateMask($(this));
    });
    $('.tax__section_taxpayer .tax__input[placeholder*="Дата рождения"]').each(function() {
        applyDateMask($(this));
    });
    $('.tax__section_taxpayer .tax__input[placeholder*="Дата выдачи документа"]').each(function() {
        applyDateMask($(this));
    });

    // Функция для синхронизации данных
    function syncPatientToTaxpayer() {
        const patientFields = [
            { patient: 'ФИО', taxpayer: 'ФИО' },
            { patient: 'Дата рождения', taxpayer: 'Дата рождения' },
            { patient: 'ИНН', taxpayer: 'ИНН' },
            { patient: 'Документ, удостоверяющий личность', taxpayer: 'Документ, удостоверяющий личность' },
            { patient: 'Серия и номер', taxpayer: 'Серия и номер' },
            { patient: 'Дата выдачи документа, удостоверяющего личность', taxpayer: 'Дата выдачи документа, удостоверяющего личность' }
        ];

        patientFields.forEach(field => {
            const patientInput = $('.tax__section_patient .tax__input[placeholder*="' + field.patient + '"]').first();
            const taxpayerInput = $('.tax__section_taxpayer .tax__input[placeholder*="' + field.taxpayer + '"]').first();
            
            if (patientInput.length && taxpayerInput.length) {
                taxpayerInput.val(patientInput.val());
            }
        });
    }

    // Обработчик изменения радио-кнопок
    $('input[name="relation"]').change(function() {
        if ($(this).val() === 'patient') {
            // Разблокируем поля налогоплательщика для возможности редактирования
            $('.tax__section_taxpayer .tax__input').prop('readonly', false);
            syncPatientToTaxpayer();
        } else {
            // Очищаем поля налогоплательщика при выборе "законный представитель"
            $('.tax__section_taxpayer .tax__input:not([placeholder*="Кем налогоплательщик приходится"], [placeholder*="Контактный номер"], [placeholder*="Дополнительная информация"])').val('');
        }
    });

    // Обработчик изменений в полях пациента
    $('.tax__section_patient .tax__input').on('input', function() {
        if ($('input[name="relation"][value="patient"]').is(':checked')) {
            syncPatientToTaxpayer();
        }
    });

    // Инициализация при загрузке страницы
    if ($('input[name="relation"][value="patient"]').is(':checked')) {
        setTimeout(syncPatientToTaxpayer, 100);
    }

    // Обработчик календарных иконок (если нужно)
    $('.tax__calendar-icon').click(function() {
        const input = $(this).siblings('.tax__input');
        input.focus();
    });
});