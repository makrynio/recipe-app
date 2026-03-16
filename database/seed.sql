USE recipe_app;

-- For demo purposes all users use the same hash (password: password)
INSERT INTO users (id, full_name, email, password_hash, role) VALUES
  (1, 'Emily Carter', 'arta@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
  (2, 'Michael Brown', 'blerim@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
  (3, 'Sophia Johnson', 'diona@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

INSERT INTO categories (id, name, slug) VALUES
  (1, 'Breakfast', 'breakfast'),
  (2, 'Lunch', 'lunch'),
  (3, 'Dinner', 'dinner'),
  (4, 'Dessert', 'dessert'),
  (5, 'Vegan', 'vegan');

INSERT INTO recipes (
  id, user_id, category_id, title, slug, description,
  servings, prep_time_min, cook_time_min, difficulty, image_url, source_url, is_published
) VALUES
  (
    1, 1, 3, 'Spaghetti Bolognese', 'spaghetti-bolognese',
    'Classic spaghetti bolognese with beef mince, vegetables and tomato sauce.',
    4, 25, 75, 'medium',
    'https://ichef.bbci.co.uk/food/ic/food_16x9_448/recipes/spaghettibolognese_67868_16x9.jpg',
    'https://www.bbc.co.uk/food/recipes/spaghettibolognese_67868',
    1
  ),
  (
    2, 2, 5, 'Vegan American Pancakes', 'vegan-american-pancakes',
    'Fluffy vegan pancakes served with fruit and maple syrup.',
    4, 5, 10, 'easy',
    'https://images.unsplash.com/photo-1528207776546-365bb710ee93',
    'https://www.bbc.co.uk/food/recipes/vegan_american_pancakes_76094',
    1
  ),
  (
    3, 1, 2, 'Healthy Pizza', 'healthy-pizza',
    'Home-made healthy pizza with yoghurt base and vegetable topping.',
    4, 30, 45, 'medium',
    'https://ichef.bbci.co.uk/food/ic/food_16x9_448/recipes/healthy_pizza_55143_16x9.jpg',
    'https://www.bbc.co.uk/food/recipes/healthy_pizza_55143',
    1
  ),
  (
    4, 2, 3, 'Easy Lamb Biryani', 'easy-lamb-biryani',
    'Fragrant one-pot lamb biryani with basmati rice, herbs and spices.',
    4, 25, 75, 'medium',
    'https://ichef.bbci.co.uk/food/ic/food_16x9_448/recipes/easy_lamb_biryani_46729_16x9.jpg',
    'https://www.bbc.co.uk/food/recipes/easy_lamb_biryani_46729',
    1
  ),
  (
    5, 1, 4, 'Dried Fruits and Nuts Bars', 'dried-fruits-and-nuts-bars',
    'No-bake dried fruit and nut bars pressed into a tray and sliced.',
    12, 20, 0, 'easy',
    'https://ichef.bbci.co.uk/food/ic/food_16x9_448/recipes/pressed_dried_fruit__and_06954_16x9.jpg',
    'https://www.bbc.co.uk/food/recipes/dried_fruits_and_nuts_18053',
    1
  ),
  (
    6, 3, 1, 'Banana Oat Breakfast Bowl', 'banana-oat-breakfast-bowl',
    'Warm oat bowl with banana, honey and berries for a quick breakfast.',
    2, 5, 10, 'easy',
    'https://images.unsplash.com/photo-1517673400267-0251440c45dc',
    NULL,
    1
  );

INSERT INTO recipe_ingredients (
  recipe_id, ingredient_name, quantity, unit, note, position
) VALUES
  (1, 'Olive oil', 1, 'tbsp', NULL, 1),
  (1, 'Smoked streaky bacon', 4, 'rashers', 'finely chopped', 2),
  (1, 'Onions', 2, 'pcs', 'medium, finely chopped', 3),
  (1, 'Carrots', 2, 'pcs', 'trimmed and finely chopped', 4),
  (1, 'Celery sticks', 2, 'pcs', 'finely chopped', 5),
  (1, 'Garlic cloves', 2, 'pcs', 'finely chopped', 6),
  (1, 'Rosemary', NULL, NULL, '2-3 sprigs, leaves picked and chopped', 7),
  (1, 'Beef mince', 500, 'g', NULL, 8),
  (1, 'Red wine', 125, 'ml', 'optional', 9),
  (1, 'Chopped tomatoes', 400, 'g', '1 tin', 10),
  (1, 'Tomato puree', 1, 'tbsp', NULL, 11),
  (1, 'Chicken stock', 290, 'ml', 'made with stock cube', 12),
  (1, 'Dried oregano', 1, 'tsp', NULL, 13),
  (1, 'Salt', NULL, NULL, 'to taste', 14),
  (1, 'Black pepper', NULL, NULL, 'to taste', 15),
  (1, 'Spaghetti', 400, 'g', 'to serve', 16),
  (1, 'Parmesan', NULL, NULL, 'freshly grated, to serve', 17),

  (2, 'Plain flour', 250, 'g', NULL, 1),
  (2, 'Baking powder', 2, 'tsp', NULL, 2),
  (2, 'Bicarbonate of soda', 0.50, 'tsp', NULL, 3),
  (2, 'Caster sugar', 1, 'tbsp', NULL, 4),
  (2, 'Sea salt', NULL, NULL, 'pinch', 5),
  (2, 'Soya yogurt', 200, 'g', NULL, 6),
  (2, 'Almond milk', 300, 'ml', NULL, 7),
  (2, 'Apple cider vinegar', 1, 'tsp', NULL, 8),
  (2, 'Sunflower oil', 1, 'tbsp', 'plus extra for frying', 9),
  (2, 'Vanilla extract', 1, 'tsp', NULL, 10),
  (2, 'Maple syrup', NULL, NULL, 'for serving', 11),
  (2, 'Fresh berries', NULL, NULL, 'for serving', 12),

  (3, 'Strong white flour', 300, 'g', NULL, 1),
  (3, 'Instant yeast', 1, 'tsp', NULL, 2),
  (3, 'Olive oil', 1, 'tbsp', 'plus extra for frying', 3),
  (3, 'Egg', 1, 'pcs', 'beaten', 4),
  (3, 'Natural yoghurt', 150, 'g', NULL, 5),
  (3, 'Salt', NULL, NULL, 'to taste', 6),
  (3, 'Black pepper', NULL, NULL, 'to taste', 7),
  (3, 'Passata', 100, 'ml', NULL, 8),
  (3, 'Dried oregano', 1, 'tsp', NULL, 9),
  (3, 'Red pepper', 1, 'pcs', 'deseeded, thinly sliced', 10),
  (3, 'Courgette', 1, 'pcs', 'thinly sliced', 11),
  (3, 'Red onion', 1, 'pcs', 'small, thinly sliced', 12),
  (3, 'Olive oil', 1, 'tbsp', NULL, 13),
  (3, 'Chilli flakes', NULL, NULL, 'pinch', 14),
  (3, 'Mozzarella', 25, 'g', 'grated', 15),
  (3, 'Cheddar', 25, 'g', 'grated', 16),
  (3, 'Basil leaves', NULL, NULL, 'a few leaves', 17),

  (4, 'Sunflower oil', 2, 'tbsp', NULL, 1),
  (4, 'Onions', 2, 'pcs', 'thinly sliced', 2),
  (4, 'Garlic cloves', 2, 'pcs', 'roughly chopped', 3),
  (4, 'Fresh ginger', 1, 'thumb', 'roughly chopped', 4),
  (4, 'Medium curry powder', 1, 'tbsp', NULL, 5),
  (4, 'Boneless lamb leg', 500, 'g', 'cut into 4cm chunks', 6),
  (4, 'Beef or lamb stock', 300, 'ml', NULL, 7),
  (4, 'Basmati rice', 250, 'g', NULL, 8),
  (4, 'Cardamom pods', 4, 'pcs', NULL, 9),
  (4, 'Bay leaves', 2, 'pcs', NULL, 10),
  (4, 'Coriander', NULL, NULL, 'small bunch, chopped', 11),
  (4, 'Mint', NULL, NULL, 'small bunch, chopped', 12),
  (4, 'Salt', NULL, NULL, 'to taste', 13),
  (4, 'Black pepper', NULL, NULL, 'to taste', 14),
  (4, 'Pomegranate seeds', NULL, NULL, 'to serve', 15),

  (5, 'Pitted dates', 250, 'g', NULL, 1),
  (5, 'Ready-to-eat dried apricots', 250, 'g', NULL, 2),
  (5, 'Sultanas', 250, 'g', NULL, 3),
  (5, 'Almonds', 250, 'g', NULL, 4),
  (5, 'Desiccated coconut', 100, 'g', NULL, 5),
  (5, 'Almond extract', 1, 'tsp', NULL, 6),

  (6, 'Rolled oats', 120, 'g', NULL, 1),
  (6, 'Milk', 400, 'ml', 'or plant-based milk', 2),
  (6, 'Banana', 1, 'pcs', 'sliced', 3),
  (6, 'Honey', 1, 'tbsp', 'optional', 4),
  (6, 'Cinnamon', 0.50, 'tsp', NULL, 5),
  (6, 'Mixed berries', 80, 'g', 'fresh or frozen', 6),
  (6, 'Chopped nuts', 20, 'g', 'for topping', 7);

INSERT INTO recipe_steps (
  recipe_id, step_number, instruction, step_time_min
) VALUES
  (1, 1, 'Heat oil in a large pan and fry bacon for 2-3 minutes until golden.', 3),
  (1, 2, 'Add onion, carrot, celery, rosemary and garlic. Cook for 8-10 minutes until soft.', 10),
  (1, 3, 'Add beef mince and cook for 3-4 minutes until browned all over.', 4),
  (1, 4, 'Stir in chopped tomatoes, tomato puree and oregano.', 2),
  (1, 5, 'Pour in wine and boil for 2-3 minutes.', 3),
  (1, 6, 'Add stock, bring to a simmer and cook gently for around 45 minutes, stirring occasionally.', 45),
  (1, 7, 'Season with salt and pepper to taste.', 1),
  (1, 8, 'Cook spaghetti according to packet instructions and drain.', 10),
  (1, 9, 'Serve the sauce over spaghetti and top with grated parmesan.', 1),

  (2, 1, 'Whisk flour, baking powder, bicarbonate of soda, sugar and a pinch of salt in a bowl.', 2),
  (2, 2, 'Mix yogurt, almond milk, vinegar, oil and vanilla, then combine with dry ingredients into a smooth batter.', 3),
  (2, 3, 'Heat a lightly oiled pan over medium heat and cook pancakes in batches until golden on both sides.', 10),
  (2, 4, 'Serve warm with maple syrup and fresh berries.', 1),

  (3, 1, 'Mix flour, yeast, olive oil and egg, then add yoghurt and knead into a smooth dough.', 10),
  (3, 2, 'Leave dough in a warm place for 20 minutes to rise, then shape into pizza bases.', 20),
  (3, 3, 'Saute red pepper, courgette and onion in olive oil, then stir in chilli flakes.', 8),
  (3, 4, 'Spread passata over the base and sprinkle oregano, salt and pepper.', 2),
  (3, 5, 'Top with sauteed vegetables and scatter mozzarella and cheddar.', 3),
  (3, 6, 'Bake at 220C for around 15 minutes.', 15),
  (3, 7, 'Finish with black pepper and basil leaves before serving.', 1),

  (4, 1, 'Heat oil in a pan and fry the sliced onions for 5-6 minutes until golden.', 6),
  (4, 2, 'Blend garlic and ginger to a paste, stir into onions and cook for 2 minutes.', 2),
  (4, 3, 'Add curry powder and lamb chunks, season, and cook until the lamb is browned.', 8),
  (4, 4, 'Pour in stock, bring to a simmer and cook for about 45 minutes until lamb is tender.', 45),
  (4, 5, 'Stir in rice, cardamom and bay leaves, adding extra water if needed.', 2),
  (4, 6, 'Cover and cook on low heat for 15-20 minutes until rice is cooked and liquid absorbed.', 20),
  (4, 7, 'Turn off heat and let rest for 5 minutes.', 5),
  (4, 8, 'Fluff with fork, fold in coriander and mint, and serve with pomegranate seeds.', 2),

  (5, 1, 'Blend dates, apricots and sultanas in a food processor until finely chopped.', 4),
  (5, 2, 'Add almonds, coconut and almond extract and process until combined.', 3),
  (5, 3, 'Line a 20cm square tray with baking paper.', 2),
  (5, 4, 'Press mixture firmly into the tray, then cover and chill for at least 1 hour.', 60),
  (5, 5, 'Lift from tray and cut into bars.', 2),

  (6, 1, 'Add oats, milk and cinnamon to a saucepan and bring to a gentle simmer.', 2),
  (6, 2, 'Cook for 6-8 minutes, stirring often, until creamy.', 8),
  (6, 3, 'Stir in half of the banana slices and a little honey.', 1),
  (6, 4, 'Divide into bowls and top with berries, remaining banana and chopped nuts.', 1);


INSERT INTO favourites (user_id, recipe_id) VALUES
  (1, 1),
  (1, 4),
  (2, 2),
  (2, 5),
  (3, 3),
  (3, 4),
  (2, 6);
