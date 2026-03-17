<?php
// Accessibility statement page is intentionally public so all users can review support expectations.
$pageTitle = 'Accessibility Statement';
require_once __DIR__ . '/../includes/header.php';
?>

<main id="main-content" class="container" tabindex="-1">
    <h1>Accessibility Statement</h1>
    <p>
        The Recipe Library web application aims to meet WCAG 2.1 Level AA standards to provide an inclusive user experience.
    </p>

    <section>
        <h2>What You Can Expect</h2>
        <ul>
            <li>Full keyboard navigation, including a “Skip to main content” link and logical tab order (WCAG 2.4.1 Bypass Blocks, 2.1.1 Keyboard, 2.4.3 Focus Order).</li>
            <li>Visible focus indicators on all interactive elements (WCAG 2.4.7 Focus Visible).</li>
            <li>Use of semantic HTML to support assistive technologies (WCAG 1.3.1 Info and Relationships).</li>
            <li>All interactive components (e.g. navigation, forms, rating and sorting controls) are keyboard accessible (WCAG 2.1.1 Keyboard).</li>
            <li>Form inputs are clearly labelled, with validation errors communicated using ARIA roles (e.g. role="alert") (WCAG 3.3.1 Error Identification, 4.1.3 Status Messages).</li>
            <li>Error messages are provided as text alongside colour, ensuring information is not conveyed by colour alone (WCAG 1.4.1 Use of Colour).</li>
            <li>Colour contrast has been considered during design, with key elements reviewed against WCAG 2.1 AA guidelines (WCAG 1.4.3 Contrast (Minimum)).</li>
            <li>Images include alternative text where appropriate, with decorative images ignored by screen readers (WCAG 1.1.1 Non-text Content)</li>
            <li>Responsive design supports use across mobile, tablet, and desktop, including zoom up to 400% without loss of functionality (WCAG 1.4.10 Reflow, 1.4.4 Resize Text).</li>

        </ul>
    </section>
    <section>
        <h2>Known Limitations</h2>
        <ul>
            <li>The application has not been fully tested with screen readers (e.g. NVDA or VoiceOver).</li>
        </ul>

    </section>
    <p><small>Last updated: March 2026.</small></p>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
