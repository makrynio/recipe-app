<?php

/**
 * Retrieve the current user's rating for a given recipe.
 * Returns an associative array with 'rating' and 'comment', or null if no rating exists.
 */
function getUserRating($conn, $userId, $recipeId) {
    $stmt = $conn->prepare("SELECT rating, comment FROM ratings WHERE user_id = ? AND recipe_id = ?");
    $stmt->bind_param("ii", $userId, $recipeId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : null;
}

/**
 * Insert or update a user's rating for a recipe.
 * Uses INSERT ... ON DUPLICATE KEY UPDATE to handle the unique (user_id, recipe_id) constraint.
 */
function saveRating($conn, $userId, $recipeId, $rating, $comment) {
    $sql = "
        INSERT INTO ratings (user_id, recipe_id, rating, comment)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment), updated_at = NOW()
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $userId, $recipeId, $rating, $comment);
    $stmt->execute();
}
