// ----------------------
// Star rating widget
// ----------------------

document.querySelectorAll('.star-rating').forEach(function (fieldset) {
    const labels = Array.from(fieldset.querySelectorAll('label'));
    const inputs = Array.from(fieldset.querySelectorAll('input[type="radio"]'));
    // Reference the parent form to check for the data-auto-submit attribute
    const parentForm = fieldset.closest('form');

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
        input.addEventListener('change', function () {
            highlight(index);

            // If data-auto-submit="true" is set on the form, submit immediately on star selection.
            // requestSubmit() triggers built-in HTML5 validation; submit() is the fallback for older browsers.
            if (parentForm && parentForm.dataset.autoSubmit === 'true') {
                if (typeof parentForm.requestSubmit === 'function') {
                    parentForm.requestSubmit();
                } else {
                    parentForm.submit();
                }
            }
        });

        // Accessibility update: keyboard focus gets the same star highlight feedback as mouse hover.
        input.addEventListener('focus', function () {
            highlight(index);
        });

        // Accessibility update: when focus leaves, restore highlight to the saved/checked rating.
        input.addEventListener('blur', resetToChecked);
    });

    // Set initial state if a rating is already selected
    resetToChecked();
});

// ----------------------
// Account profile editing
// ----------------------

// Account-info edit profile behavior
const editProfileBtn = document.getElementById('edit-profile-btn');
const editProfileForm = document.getElementById('edit-profile-form');
const cancelEditBtn = document.getElementById('cancel-edit-btn');

if (editProfileBtn && editProfileForm && cancelEditBtn) {
    editProfileBtn.addEventListener('click', function () {
        // Show inline edit form and hide trigger button without leaving the page.
        editProfileForm.classList.remove('hidden');
        editProfileBtn.classList.add('hidden');
    });

    cancelEditBtn.addEventListener('click', function () {
        // Revert to read-only profile view when user cancels editing.
        editProfileForm.classList.add('hidden');
        editProfileBtn.classList.remove('hidden');
    });
}

// ----------------------
// Authentication form validation
// ----------------------

// Shared validation rules for login/register. These mirror the server-side checks in register.php.
const authValidationPatterns = {
    // Basic email structure check: local@domain.tld
    email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,

    // Full name policy: 2-100 letters, allowing spaces/apostrophes/hyphens between name parts.
    fullName: /^(?=.{2,100}$)[\p{L}\p{M}]+(?:[ '-][\p{L}\p{M}]+)*$/u,

    // Password strength policy: at least 8 chars, 1 lowercase, 1 uppercase, and 1 number.
    password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/
};

// Ensure each validated field has a dedicated inline error element and ARIA linkage for screen readers.
function ensureFieldError(input) {
    // Build a predictable error element id tied to the field id.
    const errorId = input.id + '-error';

    // Reuse an existing error node if one already exists.
    let error = document.getElementById(errorId);

    // Create and insert the error node only when it does not already exist.
    if (!error) {
        error = document.createElement('p');
        error.id = errorId;
        error.className = 'field-error';
        error.setAttribute('aria-live', 'polite');
        error.hidden = true;

        // If a hint exists after the input, place the error after the hint; otherwise place it after the input.
        const next = input.nextElementSibling;
        if (next && next.classList.contains('field-hint')) {
            next.insertAdjacentElement('afterend', error);
        } else {
            input.insertAdjacentElement('afterend', error);
        }
    }

    // Read existing aria-describedby references so we can append the error id once.
    const describedBy = (input.getAttribute('aria-describedby') || '')
        .split(/\s+/)
        .filter(Boolean);

    // Link the field to its error element for assistive technologies.
    if (!describedBy.includes(errorId)) {
        describedBy.push(errorId);
        input.setAttribute('aria-describedby', describedBy.join(' '));
    }

    // Return the error element to allow caller updates.
    return error;
}

// Apply the current validation message and visual state to a single field.
function setFieldError(input, message) {
    // Ensure a visible/accessible error target exists for this field.
    const error = ensureFieldError(input);

    // Compute state flags used for text visibility and style classes.
    const hasError = message !== '';
    const hasValue = input.value.trim() !== '';

    // Update inline message text and hide state.
    error.textContent = message;
    error.hidden = !hasError;

    // Toggle visual states on the input (invalid/valid border styles).
    input.classList.toggle('input-invalid', hasError);
    input.classList.toggle('input-valid', !hasError && hasValue);

    // Keep aria-invalid synchronized with visual validation state.
    input.setAttribute('aria-invalid', hasError ? 'true' : 'false');

    // Return true when field is valid, false when invalid.
    return !hasError;
}

// Attach blur, input and submit validation handlers to an auth form using the supplied per-field validators.
function setupAuthValidation(formId, validators) {
    // Locate the target form by id.
    const form = document.getElementById(formId);

    // Exit quietly when this form does not exist on the current page.
    if (!form) {
        return;
    }

    // Holds input references keyed by field name.
    const fields = {};

    // Resolve all validator field names to actual inputs and pre-create error slots.
    Object.keys(validators).forEach(function (name) {
        const field = form.querySelector('[name="' + name + '"]');

        // Skip keys with no matching element in this form.
        if (!field) {
            return;
        }

        // Cache field and ensure an error node exists for consistent rendering.
        fields[name] = field;
        ensureFieldError(field);
    });

    // Validate one named field using its validator callback.
    function validateField(name) {
        const field = fields[name];

        // Missing field is treated as valid so optional keys do not block submission.
        if (!field) {
            return true;
        }

        // Run validator and apply resulting message/style state.
        return setFieldError(field, validators[name](field, fields));
    }

    // Wire per-field blur/input listeners for immediate feedback.
    Object.keys(fields).forEach(function (name) {
        const field = fields[name];

        // Validate on blur to show errors after the user leaves a field.
        field.addEventListener('blur', function () {
            // Trim non-password fields so validation and submitted values stay consistent.
            if (field.type !== 'password') {
                field.value = field.value.trim();
            }

            // Validate the field that just lost focus.
            validateField(name);

            // Re-check confirm password whenever the main password changes after the confirm field has content.
            if (name === 'password' && fields.confirm_password && fields.confirm_password.value !== '') {
                validateField('confirm_password');
            }
        });

        // Validate while typing to clear errors quickly and guide correction.
        field.addEventListener('input', function () {
            // Start live validation once the user has interacted with the field or already has an error showing.
            if (field.classList.contains('input-invalid') || field.value !== '') {
                validateField(name);
            }

            // Keep confirm_password in sync if password changes after confirm is filled.
            if (name === 'password' && fields.confirm_password && fields.confirm_password.value !== '') {
                validateField('confirm_password');
            }
        });
    });

    // Validate all mapped fields on submit and focus the first invalid field.
    form.addEventListener('submit', function (event) {
        // Track the first invalid field so keyboard focus can move there.
        let firstInvalidField = null;

        // Run validation for each field configured in this form.
        Object.keys(fields).forEach(function (name) {
            const isValid = validateField(name);

            // Store the first invalid field only once.
            if (!isValid && !firstInvalidField) {
                firstInvalidField = fields[name];
            }
        });

        // Block submission and move focus to the first invalid input.
        if (firstInvalidField) {
            event.preventDefault();
            firstInvalidField.focus();
        }
    });
}

// Login only validates presence and email format; password strength is enforced during registration.
setupAuthValidation('login-form', {
    // Email validator for login form.
    email: function (input) {
        // Trim to avoid passing accidental leading/trailing spaces.
        const value = input.value.trim();

        // Rule: email is required.
        if (value === '') {
            return 'Email address is required.';
        }

        // Rule: email must match the expected format.
        if (!authValidationPatterns.email.test(value)) {
            return 'Enter a valid email address.';
        }

        // Empty message means valid.
        return '';
    },

    // Password validator for login form.
    password: function (input) {
        // Rule: password is required.
        if (input.value === '') {
            return 'Password is required.';
        }

        // Empty message means valid.
        return '';
    }
});

// Register enforces all client-side rules before submit:
// - Full name is required and must match the allowed character pattern.
// - Email is required and must match a valid email format.
// - Password is required and must meet the strength policy.
// - Confirm password is required and must match the password field.
setupAuthValidation('register-form', {
    // Full name validator for registration.
    name: function (input) {
        // Trim spaces so pattern validation uses the normalized value.
        const value = input.value.trim();

        // Rule: full name is required.
        if (value === '') {
            return 'Full name is required.';
        }

        // Rule: full name must satisfy the allowed character/length pattern.
        if (!authValidationPatterns.fullName.test(value)) {
            return 'Enter a full name using letters, spaces, apostrophes, or hyphens.';
        }

        // Empty message means valid.
        return '';
    },

    // Email validator for registration.
    email: function (input) {
        // Trim to avoid false negatives due to surrounding whitespace.
        const value = input.value.trim();

        // Rule: email is required.
        if (value === '') {
            return 'Email address is required.';
        }

        // Rule: email must match the expected format.
        if (!authValidationPatterns.email.test(value)) {
            return 'Enter a valid email address.';
        }

        // Empty message means valid.
        return '';
    },

    // Password validator for registration.
    password: function (input) {
        // Rule: password is required.
        if (input.value === '') {
            return 'Password is required.';
        }

        // Rule: password must satisfy strength policy.
        if (!authValidationPatterns.password.test(input.value)) {
            return 'Password must be at least 8 characters and include an uppercase letter, a lowercase letter, and a number.';
        }

        // Empty message means valid.
        return '';
    },

    // Confirm-password validator for registration.
    confirm_password: function (input, fields) {
        // Rule: confirmation field is required.
        if (input.value === '') {
            return 'Please confirm your password.';
        }

        // Rule: confirmation must match password field.
        if (fields.password && input.value !== fields.password.value) {
            return 'Passwords do not match.';
        }

        // Empty message means valid.
        return '';
    }
});

// ----------------------
// Live search suggestions
// ----------------------

// Binds a text input to a datalist and fills suggestions from the given endpoint.
function wireSearchSuggestions(inputId, datalistId, endpointPath) {
    // Resolve the input and datalist once; abort setup if either element is missing on this page.
    const input = document.getElementById(inputId);
    const datalist = document.getElementById(datalistId);

    if (!input || !datalist) {
        return;
    }

    // debounceTimer delays requests while the user is still typing.
    // activeController cancels stale in-flight requests to avoid race conditions.
    let debounceTimer;
    let activeController;

    input.addEventListener('input', function () {
        const query = input.value.trim();

        // Cancel the previous scheduled request as the query has changed.
        window.clearTimeout(debounceTimer);

        // Cancel the previous fetch call if it is still running.
        if (activeController) {
            activeController.abort();
        }

        // Empty query means no suggestions should be shown.
        if (query.length < 1) {
            datalist.replaceChildren();
            return;
        }

        // Small delay keeps the UI responsive and reduces unnecessary network traffic.
        debounceTimer = window.setTimeout(function () {
            activeController = new AbortController();

            // Support endpoints that may already contain query parameters.
            const separator = endpointPath.includes('?') ? '&' : '?';
            fetch(endpointPath + separator + 'q=' + encodeURIComponent(query), {
                signal: activeController.signal
            })
                .then(function (response) {
                    // Force non-2xx responses into catch() so the datalist can be reset safely.
                    if (!response.ok) {
                        throw new Error('Suggestion request failed');
                    }
                    return response.json();
                })
                .then(function (suggestions) {
                    // Guard against malformed payloads.
                    if (!Array.isArray(suggestions)) {
                        return;
                    }

                    // Build options in a fragment to minimize DOM reflows.
                    const fragment = document.createDocumentFragment();
                    datalist.replaceChildren();

                    suggestions.forEach(function (suggestion) {
                        const option = document.createElement('option');
                        option.value = suggestion;
                        fragment.appendChild(option);
                    });

                    datalist.appendChild(fragment);
                })
                .catch(function (error) {
                    // AbortError is expected when typing fast; clear list only for real failures.
                    if (error.name !== 'AbortError') {
                        datalist.replaceChildren();
                    }
                });
        }, 180);
    });
}

// Initialize autocomplete for recipe titles and ingredients.
wireSearchSuggestions('search-name', 'recipe-name-suggestions', '/recipe-app/pages/search_suggestions.php');
wireSearchSuggestions('search-ingredient', 'ingredient-suggestions', '/recipe-app/pages/search_suggestions.php?type=ingredient');

document.addEventListener("DOMContentLoaded", function () {

    // Get references to button and hidden input
    const button = document.getElementById("sort-direction");
    const orderInput = document.getElementById("sort-order");

    // Exit quietly on pages that do not include sort controls.
    if (!button || !orderInput) {
        return;
    }

    function syncSortButtonState() {
        const isAscending = orderInput.value === "asc";

        // Accessibility update: keep visual arrow and ARIA state in sync so assistive tech announces current sort order.
        button.textContent = isAscending ? "↑" : "↓";
        button.setAttribute("aria-pressed", isAscending ? "true" : "false");
        button.setAttribute("aria-label", "Sort order: " + (isAscending ? "ascending" : "descending"));
        button.setAttribute("title", "Toggle sort order");
    }

    // Set the correct arrow when the page loads
    syncSortButtonState();

    // Toggle sorting direction when the button is clicked
    button.addEventListener("click", function () {

        // Persist selected direction in hidden input so the server receives asc/desc on submit.
        if (orderInput.value === "asc") {
            orderInput.value = "desc";
        } else {
            orderInput.value = "asc";
        }

        syncSortButtonState();

    });

});