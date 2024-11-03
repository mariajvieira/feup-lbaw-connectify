
INSERT INTO user_ (username, email, profile_picture, user_password, is_public)
VALUES
    ('alice_wonder', 'alice@example.com', 'alice.jpg', 'securepassword1', TRUE),
    ('bob_builder', 'bob@example.com', 'bob.jpg', 'securepassword2', TRUE),
    ('charlie_chaplin', 'charlie@example.com', 'charlie.jpg', 'securepassword3', FALSE),
    ('daisy_duck', 'daisy@example.com', 'daisy.jpg', 'securepassword4', TRUE),
    ('edgar_allan', 'edgar@example.com', 'edgar.jpg', 'securepassword5', TRUE),
    ('fiona_fairy', 'fiona@example.com', 'fiona.jpg', 'securepassword6', FALSE),
    ('george_gremlin', 'george@example.com', 'george.jpg', 'securepassword7', TRUE),
    ('hannah_hacker', 'hannah@example.com', 'hannah.jpg', 'securepassword8', TRUE),
    ('ian_icecream', 'ian@example.com', 'ian.jpg', 'securepassword9', TRUE),
    ('jessica_jones', 'jessica@example.com', 'jessica.jpg', 'securepassword10', FALSE),
    ('karl_kong', 'karl@example.com', 'karl.jpg', 'securepassword11', TRUE),
    ('linda_lion', 'linda@example.com', 'linda.jpg', 'securepassword12', TRUE),
    ('mike_mouse', 'mike@example.com', 'mike.jpg', 'securepassword13', TRUE),
    ('nina_ninja', 'nina@example.com', 'nina.jpg', 'securepassword14', FALSE),
    ('oliver_orange', 'oliver@example.com', 'oliver.jpg', 'securepassword15', TRUE),
    ('peter_panda', 'peter@example.com', 'peter.jpg', 'securepassword16', TRUE),
    ('quincy_quokka', 'quincy@example.com', 'quincy.jpg', 'securepassword17', TRUE),
    ('rose_rabbit', 'rose@example.com', 'rose.jpg', 'securepassword18', TRUE),
    ('sara_sparrow', 'sara@example.com', 'sara.jpg', 'securepassword19', FALSE),
    ('tom_tiger', 'tom@example.com', 'tom.jpg', 'securepassword20', TRUE),
    ('uma_unicorn', 'uma@example.com', 'uma.jpg', 'securepassword21', TRUE),
    ('vicky_vulture', 'vicky@example.com', 'vicky.jpg', 'securepassword22', TRUE),
    ('will_walrus', 'will@example.com', 'will.jpg', 'securepassword23', TRUE),
    ('xena_xerus', 'xena@example.com', 'xena.jpg', 'securepassword24', TRUE),
    ('yara_yeti', 'yara@example.com', 'yara.jpg', 'securepassword25', TRUE),
    ('zach_zebra', 'zach@example.com', 'zach.jpg', 'securepassword26', FALSE),
    ('arnold_alligator', 'arnold@example.com', 'arnold.jpg', 'securepassword27', TRUE),
    ('bianca_butterfly', 'bianca@example.com', 'bianca.jpg', 'securepassword28', TRUE),
    ('clara_cat', 'clara@example.com', 'clara.jpg', 'securepassword29', TRUE),
    ('david_dog', 'david@example.com', 'david.jpg', 'securepassword30', TRUE),
    ('elaine_emu', 'elaine@example.com', 'elaine.jpg', 'securepassword31', TRUE),
    ('frank_frog', 'frank@example.com', 'frank.jpg', 'securepassword32', TRUE),
    ('gina_goose', 'gina@example.com', 'gina.jpg', 'securepassword33', TRUE),
    ('harry_hedgehog', 'harry@example.com', 'harry.jpg', 'securepassword34', TRUE),
    ('irene_ibis', 'irene@example.com', 'irene.jpg', 'securepassword35', TRUE),
    ('john_jellyfish', 'john@example.com', 'john.jpg', 'securepassword36', TRUE),
    ('kelly_kangaroo', 'kelly@example.com', 'kelly.jpg', 'securepassword37', TRUE),
    ('leo_leopard', 'leo@example.com', 'leo.jpg', 'securepassword38', TRUE),
    ('mona_monkey', 'mona@example.com', 'mona.jpg', 'securepassword39', TRUE),
    ('nora_narwhal', 'nora@example.com', 'nora.jpg', 'securepassword40', TRUE),
    ('olga_octopus', 'olga@example.com', 'olga.jpg', 'securepassword41', TRUE),
    ('paul_parrot', 'paul@example.com', 'paul.jpg', 'securepassword42', TRUE),
    ('quinn_quail', 'quinn@example.com', 'quinn.jpg', 'securepassword43', TRUE),
    ('rachel_raccoon', 'rachel@example.com', 'rachel.jpg', 'securepassword44', TRUE),
    ('sammy_seal', 'sammy@example.com', 'sammy.jpg', 'securepassword45', TRUE),
    ('tina_tortoise', 'tina@example.com', 'tina.jpg', 'securepassword46', TRUE),
    ('ursula_unicorn', 'ursula@example.com', 'ursula.jpg', 'securepassword47', TRUE),
    ('vince_viper', 'vince@example.com', 'vince.jpg', 'securepassword48', TRUE),
    ('willow_wolf', 'willow@example.com', 'willow.jpg', 'securepassword49', TRUE),
    ('xander_xerus', 'xander@example.com', 'xander.jpg', 'securepassword50', TRUE);

INSERT INTO administrator (user_id)
VALUES
    (1), (3), (5), (7), (9), (10), (12), (15), (18), (21);

INSERT INTO group_ (owner_id, group_name, description, visibility)
VALUES
    (1, 'Book Lovers', 'A group for those who love reading books.', TRUE),
    (2, 'Construction Crew', 'Group for builders and construction enthusiasts.', TRUE),
    (3, 'Creative Arts', 'Sharing creative arts and crafts.', FALSE),
    (4, 'Tech Innovators', 'A group for tech enthusiasts and innovators.', TRUE),
    (5, 'Mystery Solvers', 'For those who love solving mysteries.', TRUE),
    (6, 'Animal Lovers', 'For animal lovers to share stories and pictures.', TRUE),
    (7, 'Travel Enthusiasts', 'Discuss travel experiences and tips.', TRUE),
    (8, 'Foodies', 'For sharing recipes and food experiences.', TRUE),
    (9, 'Fitness Freaks', 'For fitness and health enthusiasts.', TRUE),
    (10, 'Music Lovers', 'Share your favorite music and playlists.', TRUE);

INSERT INTO post (user_id, group_id, content, IMAGE1, IMAGE2, IMAGE3, is_public, post_date)
VALUES
    (1, NULL, 'Just finished reading a fantastic book!', NULL, 'book1.jpg', NULL, TRUE, DEFAULT),
    (2, NULL, 'Building a new project, check it out!', 'construction.jpg', NULL, NULL, TRUE, DEFAULT),
    (3, NULL, 'Check out my latest painting!', NULL, 'artwork1.jpg', 'artwork2.jpg', TRUE, DEFAULT),
    (4, NULL, 'Excited about the new tech innovations!', NULL, 'tech1.jpg', NULL, TRUE, DEFAULT),
    (5, NULL, 'Lets solve some mysteries together!', NULL, NULL, NULL, TRUE, DEFAULT),
    (6, NULL, 'Adopting a new puppy today!', NULL, 'puppy.jpg', NULL, TRUE, DEFAULT),
    (7, NULL, 'Just got back from my trip to Italy!', NULL, NULL, 'italy.jpg', TRUE, DEFAULT),
    (8, NULL, 'Tried a new recipe today, it was delicious!', 'recipe.jpg', NULL, NULL, TRUE, DEFAULT),
    (9, NULL, 'Just finished a 5k run, feeling great!', NULL, 'run.jpg', NULL, TRUE, DEFAULT),
    (10, NULL, 'Discovering new music every day!', NULL, NULL, 'music.jpg', TRUE, DEFAULT),
    (1, NULL, 'Any recommendations for good books?', NULL, NULL, NULL, TRUE, DEFAULT),
    (2, NULL, 'New construction materials available!', 'materials.jpg', NULL, NULL, TRUE, DEFAULT),
    (3, NULL, 'Art competition coming up!', NULL, 'competition.jpg', NULL, TRUE, DEFAULT),
    (4, NULL, 'Latest gadget review is out!', NULL, NULL, 'gadget.jpg', TRUE, DEFAULT),
    (5, NULL, 'Share your best mystery story!', NULL, NULL, NULL, TRUE, DEFAULT),
    (6, NULL, 'What is your favorite pet?', NULL, NULL, NULL, TRUE, DEFAULT),
    (7, NULL, 'Looking for travel buddies!', NULL, NULL, NULL, TRUE, DEFAULT),
    (8, NULL, 'Food festival this weekend!', NULL, NULL, 'festival.jpg', TRUE, DEFAULT),
    (9, NULL, 'Join my fitness challenge!', NULL, NULL, NULL, TRUE, DEFAULT),
    (10, NULL, 'Music festival coming soon!', NULL, 'festival.jpg', NULL, TRUE, DEFAULT),
    (1, NULL, 'What construction project are you working on?', NULL, NULL, NULL, TRUE, DEFAULT),
    (2, NULL, 'Have you seen my latest drawing?', NULL, 'drawing.jpg', NULL, TRUE, DEFAULT),
    (3, NULL, 'What are your thoughts on AI?', NULL, NULL, NULL, TRUE, DEFAULT),
    (4, NULL, 'Anyone solved a mystery recently?', NULL, NULL, NULL, TRUE, DEFAULT),
    (5, NULL, 'Adopt, dont shop!', NULL, NULL, NULL, TRUE, DEFAULT),
    (6, NULL, 'Whats your favorite travel destination?', NULL, NULL, NULL, TRUE, DEFAULT),
    (7, NULL, 'What dish should I try next?', NULL, NULL, NULL, TRUE, DEFAULT),
    (8, NULL, 'Who wants to join me for a workout?', NULL, NULL, NULL, TRUE, DEFAULT),
    (9, NULL, 'Whats your go-to song for motivation?', NULL, NULL, NULL, TRUE, DEFAULT);

INSERT INTO saved_post (user_id, post_id)
VALUES
    (1, 1),
    (2, 2),
    (3, 3),
    (4, 4),
    (5, 5),
    (6, 6),
    (7, 7),
    (8, 8),
    (9, 9),
    (10, 10),
    (1, 2),
    (2, 3),
    (3, 4),
    (4, 5),
    (5, 6),
    (6, 7),
    (7, 8),
    (8, 9),
    (9, 10),
    (10, 1);

INSERT INTO comment_ (post_id, user_id, comment_content)
VALUES
    (1, 2, 'I loved that book too!'),
    (1, 3, 'Great choice!'),
    (2, 1, 'That looks amazing!'),
    (3, 4, 'Your art is inspiring!'),
    (4, 5, 'That’s a great gadget!'),
    (5, 6, 'Mysteries are the best!'),
    (6, 7, 'What a cute puppy!'),
    (7, 8, 'Italy is wonderful!'),
    (8, 9, 'That recipe sounds delicious!'),
    (9, 10, 'Running is so rewarding!'),
    (10, 1, 'What a nice playlist!'),
    (1, 4, 'I can recommend a great series!'),
    (2, 5, 'I love working on projects like this!'),
    (3, 6, 'Art is truly a reflection of the soul!'),
    (4, 7, 'What do you think about this tech?'),
    (5, 8, 'Tell me more about the mystery!'),
    (6, 9, 'Dogs are the best companions!'),
    (7, 10, 'Traveling is such a rewarding experience!'),
    (8, 1, 'Food is an art form in itself!'),
    (9, 2, 'Fitness is a journey, not a destination!'),
    (10, 3, 'I love discovering new music!');

INSERT INTO reaction (user_id, post_id, reaction_type)
VALUES
    (1, 1, 'like'),
    (2, 2, 'laugh'),
    (3, 3, 'applause'),
    (4, 4, 'like'),
    (5, 5, 'applause'),
    (6, 6, 'like'),
    (7, 7, 'applause'),
    (8, 8, 'like'),
    (9, 9, 'like'),
    (10, 10, 'applause'),
    (1, 2, 'shocked'),
    (2, 3, 'like'),
    (3, 4, 'like'),
    (4, 5, 'shocked'),
    (5, 6, 'like'),
    (6, 7, 'shocked'),
    (7, 8, 'like'),
    (8, 9, 'shocked'),
    (9, 10, 'like'),
    (10, 1, 'shocked');

INSERT INTO friend_request (sender_id, receiver_id, request_status)
VALUES
    (3, 4, 'accepted'),
    (2, 5, 'denied'),
    (4, 6, 'pending'),
    (6, 7, 'accepted'),
    (8, 9, 'denied'),
    (10, 1, 'pending'),
    (5, 2, 'accepted'),
    (7, 1, 'pending'),
    (2, 8, 'accepted');

INSERT INTO friendship (user_id1, user_id2)
VALUES
    (3, 4),
    (5, 6),
    (7, 8),
    (9, 10),
    (11, 12),
    (13, 14),
    (15, 16),
    (17, 18),
    (19, 20),
    (21, 22),
    (23, 24),
    (25, 26),
    (27, 28),
    (29, 30),
    (31, 32),
    (33, 34),
    (35, 36),
    (37, 38),
    (39, 40),
    (41, 42),
    (43, 44),
    (45, 46),
    (47, 48),
    (49, 50);

INSERT INTO join_group_request (group_id, user_id, request_status)
VALUES
    (1, 3, 'pending'),
    (2, 1, 'accepted'),
    (3, 4, 'denied'),
    (4, 5, 'pending'),
    (5, 6, 'accepted'),
    (6, 7, 'denied'),
    (7, 8, 'pending'),
    (8, 9, 'accepted'),
    (9, 10, 'denied'),
    (10, 1, 'pending'),
    (1, 2, 'accepted'),
    (2, 3, 'pending'),
    (3, 4, 'denied');

INSERT INTO group_member (user_id, group_id)
VALUES
    (1, 1),
    (2, 2),
    (3, 3),
    (4, 4),
    (5, 5),
    (6, 6),
    (7, 7),
    (8, 8),
    (9, 9),
    (10, 10),
    (3, 1),
    (5, 2),
    (6, 3),
    (7, 4),
    (8, 5),
    (9, 6),
    (10, 7),
    (2, 8),
    (4, 9),
    (1, 10);

INSERT INTO group_owner (user_id, group_id)
VALUES
    (1, 1),
    (2, 2),
    (3, 3),
    (4, 4),
    (5, 5),
    (6, 6),
    (7, 7),
    (8, 8),
    (9, 9),
    (10, 10);

INSERT INTO notification (user_id, related_id, is_read)
VALUES
    (1, 1, FALSE),
    (2, 2, TRUE),
    (3, 3, FALSE),
    (4, 4, TRUE),
    (5, 5, FALSE),
    (6, 6, TRUE),
    (7, 7, FALSE),
    (8, 8, TRUE),
    (9, 9, FALSE),
    (10, 10, TRUE),
    (1, 2, FALSE),
    (2, 3, TRUE),
    (3, 4, FALSE),
    (4, 5, TRUE),
    (5, 6, FALSE),
    (6, 7, TRUE),
    (7, 8, FALSE),
    (8, 9, TRUE),
    (9, 10, FALSE),
    (10, 1, TRUE);

INSERT INTO comment_notification (notification_id, comment_id)
VALUES
    (1, 1),
    (2, 2),
    (3, 3),
    (4, 4);

INSERT INTO reaction_notification (notification_id, reaction_id)
VALUES
    (1, 1),
    (2, 2),
    (3, 3),
    (4, 4);

INSERT INTO friend_request_notification (notification_id, friend_request_id)
VALUES
    (1, 1),
    (2, 2),
    (3, 3),
    (4, 4);

INSERT INTO group_request_notification (notification_id, group_request_id)
VALUES
    (1, 1),
    (2, 2),
    (3, 3),
    (4, 4);

INSERT INTO group_post_notification (notification_id, post_id)
VALUES
    (1, 1),
    (2, 2),
    (3, 3),
    (4, 4);
