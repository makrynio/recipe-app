document.querySelectorAll('.star-rating').forEach(function (fieldset) {
    const labels = Array.from(fieldset.querySelectorAll('label'));
    const inputs = Array.from(fieldset.querySelectorAll('input[type="radio"]'));

    function highlight(upToIndex) {
        labels.forEach(function (label, i) {
            label.classList.toggle('active', i <= upToIndex);
        });
    }

    function resetToChecked() {
        const checkedIndex = inputs.findIndex(function (input) { return input.checked; });
        if (checkedIndex >= 0) {
            highlight(checkedIndex);
        } else {
            labels.forEach(function (label) { label.classList.remove('active'); });
        }
    }

    labels.forEach(function (label, index) {
        label.addEventListener('mouseenter', function () { highlight(index); });
        label.addEventListener('mouseleave', resetToChecked);
    });

    inputs.forEach(function (input, index) {
        input.addEventListener('change', function () { highlight(index); });
    });

    // Set initial state if a rating is already selected
    resetToChecked();
});
