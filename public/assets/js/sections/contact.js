(function () {
    function showSuccess(form) {
        var success = form.querySelector('.section-contact__form-success');
        if (!success) {
            return;
        }
        success.hidden = false;
        success.classList.add('is-visible');
        window.setTimeout(function () {
            success.classList.remove('is-visible');
        }, 4500);
        window.setTimeout(function () {
            success.hidden = true;
        }, 5000);
    }

    function setSubmitting(form, submitting) {
        var button = form.querySelector('[data-contact-submit]');
        if (!button) {
            return;
        }
        button.disabled = submitting;
        var idle = button.querySelector('[data-contact-submit-label]');
        var busy = button.querySelector('[data-contact-submitting-label]');
        if (idle) {
            idle.hidden = submitting;
        }
        if (busy) {
            busy.hidden = !submitting;
        }
    }

    function initForm(form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var error = form.querySelector('.section-contact__form-error');
            if (error) {
                error.hidden = true;
                error.textContent = '';
            }

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            setSubmitting(form, true);
            window.setTimeout(function () {
                setSubmitting(form, false);
                form.reset();
                showSuccess(form);
            }, 800);
        });
    }

    document.querySelectorAll('[data-contact-form]').forEach(initForm);
})();
