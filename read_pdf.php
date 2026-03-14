<?php

// ----------------------
// Temporary PDF text extraction helper
// ----------------------

// This script attempts a basic text scrape by reading PDF text objects between BT/ET markers.
// It is only intended for quick local inspection of the coding standards PDF.
$pdf = file_get_contents('c:/University/Networks and Web Technology/Coding_Standards.pdf');

// Collect each text object block from the PDF stream.
preg_match_all('/BT(.*?)ET/s', $pdf, $m);
$out = '';

// Extract bracketed text fragments, strip non-ASCII bytes, and concatenate readable tokens.
foreach ($m[1] as $block) {
    preg_match_all('/\(([^)]{1,200})\)/', $block, $t);
    foreach ($t[1] as $tt) {
        $clean = preg_replace('/[^\x20-\x7E]/', '', $tt);
        if (strlen(trim($clean)) > 2) {
            $out .= $clean . ' ';
        }
    }
}
echo $out;
