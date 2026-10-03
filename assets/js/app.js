document.addEventListener('DOMContentLoaded', function () {
    const today = new Date();
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(function (input) {
        if (!input.value) {
            const iso = today.toISOString().split('T')[0];
            input.value = iso;
        }
    });
});
