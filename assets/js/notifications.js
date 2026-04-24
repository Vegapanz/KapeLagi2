(function () {
    function ensureToastContainer() {
        let container = document.getElementById('kapeToastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'kapeToastContainer';
            container.className = 'kape-toast-container';
            container.setAttribute('aria-live', 'polite');
            container.setAttribute('aria-atomic', 'true');
            document.body.appendChild(container);
        }
        return container;
    }

    function createToast(title, message, tone) {
        const container = ensureToastContainer();
        const toneClass = tone === 'success' ? 'kape-toast-success' : tone === 'error' ? 'kape-toast-error' : 'kape-toast-info';

        const toastEl = document.createElement('div');
        toastEl.className = 'toast show kape-toast ' + toneClass;
        toastEl.setAttribute('role', 'status');
        toastEl.setAttribute('aria-live', 'polite');
        toastEl.innerHTML =
            '<div class="toast-header">' +
                '<strong class="me-auto">' + title + '</strong>' +
                '<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>' +
            '</div>' +
            '<div class="toast-body">' + message + '</div>';

        container.appendChild(toastEl);

        if (window.bootstrap && window.bootstrap.Toast) {
            const bsToast = new window.bootstrap.Toast(toastEl, { delay: 2600 });
            toastEl.addEventListener('hidden.bs.toast', function () {
                toastEl.remove();
            });
            bsToast.show();
        } else {
            setTimeout(function () {
                toastEl.remove();
            }, 2600);
        }
    }

    function openPopup(title, text, icon) {
        if (window.Swal) {
            return window.Swal.fire({
                title: title,
                text: text,
                icon: icon || 'info',
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'kape-swal-popup',
                    title: 'kape-swal-title',
                    htmlContainer: 'kape-swal-html',
                    confirmButton: 'kape-swal-confirm'
                },
                buttonsStyling: false
            });
        }

        window.alert(text);
        return Promise.resolve();
    }

    function confirmModal(options) {
        const opts = options || {};
        const title = opts.title || 'Are you sure?';
        const text = opts.text || 'Please confirm your action.';

        if (window.Swal) {
            return window.Swal.fire({
                title: title,
                text: text,
                icon: opts.icon || 'question',
                showCancelButton: true,
                confirmButtonText: opts.confirmText || 'Confirm',
                cancelButtonText: opts.cancelText || 'Cancel',
                reverseButtons: true,
                customClass: {
                    popup: 'kape-swal-popup',
                    title: 'kape-swal-title',
                    htmlContainer: 'kape-swal-html',
                    confirmButton: 'kape-swal-confirm',
                    cancelButton: 'kape-swal-cancel'
                },
                buttonsStyling: false
            }).then(function (result) {
                return result.isConfirmed;
            });
        }

        return Promise.resolve(window.confirm(text));
    }

    window.KapeNotify = {
        toastSuccess: function (message, title) {
            createToast(title || 'Success', message, 'success');
        },
        toastError: function (message, title) {
            createToast(title || 'Error', message, 'error');
        },
        toastInfo: function (message, title) {
            createToast(title || 'Notice', message, 'info');
        },
        popup: function (title, text, icon) {
            return openPopup(title, text, icon);
        },
        confirm: function (options) {
            return confirmModal(options);
        }
    };
})();
