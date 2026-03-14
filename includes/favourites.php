<?php

// Check whether a recipe is already saved by the user
function isFavourite($conn, $userId, $recipeId) {
    $stmt = mysqli_prepare($conn, 'SELECT 1 FROM favourites WHERE user_id = ? AND recipe_id = ?');
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $recipeId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $exists = mysqli_stmt_num_rows($stmt) > 0;
    mysqli_stmt_close($stmt);
    return $exists;
}

// Save a recipe to the user's favourites
function addFavourite($conn, $userId, $recipeId) {
    $stmt = mysqli_prepare($conn, 'INSERT IGNORE INTO favourites (user_id, recipe_id) VALUES (?, ?)');
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $recipeId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Remove a recipe from the user's favourites
function removeFavourite($conn, $userId, $recipeId) {
    $stmt = mysqli_prepare($conn, 'DELETE FROM favourites WHERE user_id = ? AND recipe_id = ?');
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $recipeId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Get all favourite recipes for a user
function getUserFavourites($conn, $userId) {
    // image_url is included so the account page can render recipe thumbnails alongside saved items.
    $stmt = mysqli_prepare($conn, '
        SELECT recipes.id, recipes.title, recipes.cook_time_min, recipes.image_url, categories.name AS category_name
        FROM favourites
        JOIN recipes ON favourites.recipe_id = recipes.id
        LEFT JOIN categories ON recipes.category_id = categories.id
        WHERE favourites.user_id = ?
        ORDER BY favourites.created_at DESC
    ');
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);
    return $result;
}
