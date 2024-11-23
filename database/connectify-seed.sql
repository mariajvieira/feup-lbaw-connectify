DROP SCHEMA IF EXISTS lbaw2453 CASCADE;
CREATE SCHEMA lbaw2453;
SET search_path = lbaw2453;


DROP TABLE IF EXISTS users CASCADE;
DROP TABLE IF EXISTS administrator CASCADE;
DROP TABLE IF EXISTS post CASCADE;
DROP TABLE IF EXISTS saved_post CASCADE;
DROP TABLE IF EXISTS comment_ CASCADE;
DROP TABLE IF EXISTS reaction CASCADE;
DROP TABLE IF EXISTS friend_request CASCADE;
DROP TABLE IF EXISTS friendship CASCADE;
DROP TABLE IF EXISTS group_ CASCADE;
DROP TABLE IF EXISTS join_group_request CASCADE;
DROP TABLE IF EXISTS group_member CASCADE;
DROP TABLE IF EXISTS group_owner CASCADE;
DROP TABLE IF EXISTS notification CASCADE;
DROP TABLE IF EXISTS comment_notification CASCADE;
DROP TABLE IF EXISTS reaction_notification CASCADE;
DROP TABLE IF EXISTS friend_request_notification CASCADE;
DROP TABLE IF EXISTS group_request_notification CASCADE;
DROP TABLE IF EXISTS group_post_notification CASCADE;

DROP TYPE IF EXISTS statusGroup_request CASCADE;
DROP TYPE IF EXISTS statusFriendship_request CASCADE;
DROP TYPE IF EXISTS reactionType CASCADE;


-- Types

CREATE TYPE statusGroup_request AS ENUM ('pending', 'accepted', 'denied');
CREATE TYPE statusFriendship_request AS ENUM ('pending', 'accepted', 'denied');
CREATE TYPE reactionType AS ENUM ('like', 'laugh', 'cry', 'applause', 'shocked');

-- Tables

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    profile_picture TEXT DEFAULT 'default.png',
    password TEXT NOT NULL,
    is_public BOOLEAN DEFAULT TRUE NOT NULL
);

CREATE TABLE administrator (
    user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id)
);

CREATE TABLE group_ (
    id SERIAL PRIMARY KEY,
    owner_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    group_name TEXT NOT NULL,
    description TEXT,
    visibility BOOLEAN NOT NULL,
    is_public BOOLEAN DEFAULT TRUE NOT NULL
);

CREATE TABLE post (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    group_id INT REFERENCES group_(id) ON UPDATE CASCADE,
    content TEXT,
    IMAGE1 TEXT,
    IMAGE2 TEXT,
    IMAGE3 TEXT,
    is_public BOOLEAN DEFAULT TRUE NOT NULL,
    post_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK ((content IS NOT NULL OR IMAGE1 IS NOT NULL) OR group_id IS NULL)
);

CREATE TABLE saved_post (
    user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    post_id INT NOT NULL REFERENCES post(id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id, post_id)
);

CREATE TABLE comment_ (
    id SERIAL PRIMARY KEY,
    post_id INT NOT NULL REFERENCES post(id) ON UPDATE CASCADE,
    user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    comment_content TEXT NOT NULL,
    commentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reaction (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    post_id INT NOT NULL REFERENCES post(id) ON UPDATE CASCADE,
    reaction_type reactionType NOT NULL,
    reaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE friend_request (
    id SERIAL PRIMARY KEY,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    request_status statusFriendship_request NOT NULL,
    sender_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    receiver_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE
);

CREATE TABLE friendship (
    user_id1 INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    user_id2 INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id1, user_id2)
);

CREATE TABLE join_group_request (
    id SERIAL PRIMARY KEY,
    group_id INT NOT NULL REFERENCES group_(id) ON UPDATE CASCADE,
    user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    request_status statusGroup_request NOT NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE group_member (
    user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    group_id INT NOT NULL REFERENCES group_(id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id, group_id)
);

CREATE TABLE group_owner (
    user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    group_id INT NOT NULL REFERENCES group_(id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id, group_id)
);

CREATE TABLE notification (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    related_id INT,
    is_read BOOLEAN DEFAULT false,
    notification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE comment_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(id) ON UPDATE CASCADE,
    comment_id INT NOT NULL REFERENCES comment_(id) ON UPDATE CASCADE
);

CREATE TABLE reaction_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(id) ON UPDATE CASCADE,
    reaction_id INT NOT NULL REFERENCES reaction(id) ON UPDATE CASCADE
);

CREATE TABLE friend_request_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(id) ON UPDATE CASCADE,
    friend_request_id INT NOT NULL REFERENCES friend_request(id) ON UPDATE CASCADE
);

CREATE TABLE group_request_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(id) ON UPDATE CASCADE,
    group_request_id INT NOT NULL REFERENCES join_group_request(id) ON UPDATE CASCADE
);

CREATE TABLE group_post_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(id) ON UPDATE CASCADE,
    post_id INT NOT NULL REFERENCES post(id) ON UPDATE CASCADE
);

-- Perfomance Indices

CREATE INDEX idx_user_posts ON post(user_id);
CLUSTER Post USING idx_user_posts;

CREATE INDEX idx_post_postdate ON post(post_date);
CREATE INDEX idx_notification_user_date ON notification (user_id, notification_date);

-- Full-text Search Indices

-- User FTS index
DROP FUNCTION IF EXISTS user_search_update() CASCADE;
ALTER TABLE users
ADD COLUMN tsvectors TSVECTOR;

CREATE FUNCTION user_search_update() RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN 
        NEW.tsvectors := to_tsvector('portuguese', NEW.username);
    END IF;
    IF TG_OP = 'UPDATE' THEN 
        IF (NEW.username <> OLD.username) THEN
            NEW.tsvectors := to_tsvector('portuguese', NEW.username); 
        END IF;
    END IF;
 RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER user_search_update
BEFORE INSERT OR UPDATE ON users
FOR EACH ROW
EXECUTE PROCEDURE user_search_update();

CREATE INDEX idx_user_search ON users USING GIN (tsvectors);

-- Post FTS index
DROP FUNCTION IF EXISTS post_search_update() CASCADE;

ALTER TABLE post
ADD COLUMN IF NOT EXISTS tsvectors TSVECTOR;

CREATE FUNCTION post_search_update() RETURNS TRIGGER AS $$
BEGIN
  IF TG_OP = 'INSERT' THEN 
    NEW.tsvectors := to_tsvector('portuguese', NEW.content);
  ELSIF TG_OP = 'UPDATE' THEN 
    IF NEW.content <> OLD.content THEN
      NEW.tsvectors := to_tsvector('portuguese', NEW.content); 
    END IF;
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_post_tsvectors
BEFORE INSERT OR UPDATE ON post
FOR EACH ROW
EXECUTE FUNCTION post_search_update();

CREATE INDEX idx_post_content ON post USING GIN (tsvectors);


-- Group FTS index
DROP FUNCTION IF EXISTS group_search_update() CASCADE;

ALTER TABLE group_
ADD COLUMN IF NOT EXISTS tsvectors TSVECTOR;

CREATE FUNCTION group_search_update() RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN 
        NEW.tsvectors := (
            setweight(to_tsvector('portuguese', NEW.group_name), 'A') ||
            setweight(to_tsvector('portuguese', NEW.description), 'B')
        );
    ELSIF TG_OP = 'UPDATE' THEN
        IF NEW.group_name <> OLD.group_name OR NEW.description <> OLD.description THEN
            NEW.tsvectors := (
                setweight(to_tsvector('portuguese', NEW.group_name), 'A') ||
                setweight(to_tsvector('portuguese', NEW.description), 'B')
            );
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER group_search_update
BEFORE INSERT OR UPDATE ON group_
FOR EACH ROW
EXECUTE FUNCTION group_search_update();

CREATE INDEX idx_group_description ON group_ USING GIN (tsvectors);

-- Triggers 

-- TRIGGER01: Enforces that only approved friends can view private profiles (BR01, BR07)
CREATE OR REPLACE FUNCTION enforce_profile_visibility_update()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.is_public = FALSE THEN
        IF NOT EXISTS (
            SELECT 1 FROM friendship
            WHERE (user_id1 = NEW.user_id AND user_id2 = current_user) OR
                  (user_id2 = NEW.user_id AND user_id1 = current_user)
        ) THEN
            RAISE EXCEPTION 'Perfil privado. Acesso negado.';
        END IF;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_enforce_profile_visibility
BEFORE UPDATE ON users
FOR EACH ROW
EXECUTE FUNCTION enforce_profile_visibility_update();

-- TRIGGER02: Ensures users cannot send duplicate friend requests (BR02)
CREATE OR REPLACE FUNCTION enforce_friend_request_limit()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM friend_request
        WHERE sender_id = NEW.sender_id
          AND receiver_id = NEW.receiver_id
          AND request_status NOT IN ('denied')
    ) THEN
        RAISE EXCEPTION 'Not successful: cannot send more than one friend request to the same user.';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_enforce_friend_request_limit
BEFORE INSERT ON friend_request
FOR EACH ROW
EXECUTE FUNCTION enforce_friend_request_limit();


-- TRIGGER03: Prevents multiple reactions from the same user on a single post (BR04)
CREATE OR REPLACE FUNCTION enforce_reaction_limit()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM reaction
        WHERE post_id = NEW.post_id
          AND user_id = NEW.user_id
    ) THEN
        RAISE EXCEPTION 'User already reacted to this post.';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_enforce_reaction_limit
BEFORE INSERT ON reaction
FOR EACH ROW
EXECUTE FUNCTION enforce_reaction_limit();


-- TRIGGER04: Ensures each post has content (text or media) (BR08)
CREATE OR REPLACE FUNCTION enforce_post_content()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.content IS NULL AND NEW.image1 IS NULL AND NEW.image2 IS NULL AND NEW.image3 IS NULL THEN
        RAISE EXCEPTION 'A post must contain text or at least one image.';
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_enforce_post_content
BEFORE INSERT OR UPDATE ON post
FOR EACH ROW
EXECUTE FUNCTION enforce_post_content();


-- TRIGGER05: Anonymizes user data upon account deletion, retaining content (BR05)
CREATE OR REPLACE FUNCTION anonymize_user_data()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE comment_ SET user_id = NULL WHERE user_id = OLD.user_id;
    UPDATE reaction SET user_id = NULL WHERE user_id = OLD.user_id;
    UPDATE post SET user_id = NULL WHERE user_id = OLD.user_id; 
    
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_anonymize_user_data
AFTER DELETE ON users
FOR EACH ROW
EXECUTE FUNCTION anonymize_user_data();


-- TRIGGER06: Ensures users can only post in groups they belong to (BR11)
CREATE OR REPLACE FUNCTION enforce_group_posting()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.group_id IS NOT NULL THEN
        IF NOT EXISTS (
            SELECT 1 FROM group_member
            WHERE group_id = NEW.group_id AND user_id = NEW.user_id
        ) THEN
            RAISE EXCEPTION 'User must be a member of the group to post.';
        END IF;
    END IF; 
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_enforce_group_posting
BEFORE INSERT ON post
FOR EACH ROW
EXECUTE FUNCTION enforce_group_posting();



-- TRIGGER07: Requires group owner's approval for joining private groups (BR03)
CREATE OR REPLACE FUNCTION enforce_group_membership_control()
RETURNS TRIGGER AS $$
BEGIN
    IF (SELECT is_public FROM group_ WHERE id = NEW.group_id) = FALSE THEN
        IF NEW.request_status = 'Pending' THEN
            RAISE EXCEPTION 'Group membership requires owner approval.';
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_enforce_group_membership_control
BEFORE INSERT OR UPDATE ON join_group_request
FOR EACH ROW
EXECUTE FUNCTION enforce_group_membership_control();

-- Transactions
SET SESSION CHARACTERISTICS AS TRANSACTION ISOLATION LEVEL SERIALIZABLE;


-- Tran01
CREATE OR REPLACE FUNCTION inserir_post(content TEXT, visibility BOOLEAN) 
RETURNS VOID AS $$
BEGIN
    INSERT INTO post (content, is_public, user_id)  
    VALUES ($1, $2, COALESCE(current_user_id, 1));  
END;
$$ LANGUAGE plpgsql;

-- Tran02
CREATE OR REPLACE FUNCTION add_comment(
    postId INT,
    userId INT,
    commentContent TEXT
) RETURNS VOID AS $$
DECLARE
    postOwnerId INT;
    commentId INT;
BEGIN
    SELECT user_id INTO postOwnerId
    FROM post
    WHERE post_id = postId;

    IF postOwnerId IS NULL THEN
        RAISE EXCEPTION 'No valid post found.';
    END IF;

    INSERT INTO comment_ (post_id, user_id, comment_content, commentDate)
    VALUES (postId, userId, commentContent, NOW())
    RETURNING id INTO commentId;

    INSERT INTO notification (content, is_read, notification_date, user_id)
    VALUES ('User ' || userId || ' commented on your post.', FALSE, NOW(), postOwnerId);

    INSERT INTO comment_notification (notification_id, comment_id)
    VALUES (currval(pg_get_serial_sequence('notification', 'notification_id')), commentId);
END;
$$ LANGUAGE plpgsql;


--Tran03
CREATE OR REPLACE FUNCTION add_reaction(postId INT, userId INT, reactionType TEXT) 
RETURNS VOID AS $$
DECLARE
    postOwnerId INT;
    reactionId INT;
BEGIN
    SELECT user_id INTO postOwnerId
    FROM post
    WHERE post_id = postId;

    INSERT INTO reaction (reactionType, reaction_date, post_id, user_id) 
    VALUES (reactionType, NOW(), postId, userId)
    RETURNING reaction_id INTO reactionId;

    INSERT INTO notification (content, is_read, notification_date, user_id)
    VALUES ('User ' || userId || ' reacted to your post with ' || reactionType, FALSE, NOW(), postOwnerId);
    
    INSERT INTO reaction_notification (notification_id, reaction_id)
    VALUES (currval(pg_get_serial_sequence('notification', 'notification_id')), reactionId);
END; $$ LANGUAGE plpgsql;

--Tran04
CREATE OR REPLACE FUNCTION update_user_info(userId INT, newName TEXT, newEmail TEXT, newProfilePicture TEXT) 
RETURNS VOID AS $$
BEGIN
    UPDATE users
    SET username = newName, email = newEmail, profile_picture = newProfilePicture 
    WHERE id = userId;
END; $$ LANGUAGE plpgsql;


-- Tran05
CREATE OR REPLACE FUNCTION delete_post(postId INT)
RETURNS VOID AS $$
BEGIN
    DELETE FROM comment_ WHERE post_id = postId;
    DELETE FROM reaction WHERE post_id = postId;
    DELETE FROM post WHERE post_id = postId;
END;
$$ LANGUAGE plpgsql;


-- Tran06
CREATE OR REPLACE FUNCTION send_friend_request(sender_id INT, receiver_id INT) 
RETURNS VOID AS $$
DECLARE
    notification_id INT;
BEGIN
    INSERT INTO friend_request (request_date, request_status, sender_id, receiver_id)
    VALUES (NOW(), 'pending', sender_id, receiver_id);
    
    INSERT INTO notification (content, is_read, notification_date, user_id)
    VALUES ('User ' || sender_id || ' sent you a friend request.', FALSE, NOW(), receiver_id);

    notification_id := currval(pg_get_serial_sequence('notification', 'notification_id'));

    INSERT INTO friend_request_notification (notification_id, friend_request_id)
    VALUES (notification_id, (SELECT MAX(request_id) FROM friend_request WHERE sender_id = sender_id AND receiver_id = receiver_id));
END; 
$$ LANGUAGE plpgsql;


-- Tran07
CREATE OR REPLACE FUNCTION accept_friend_request(request_id INT, user_id1 INT, user_id2 INT) 
RETURNS VOID AS $$
BEGIN
    UPDATE friend_request
    SET request_status = 'accepted'
    WHERE id = request_id; 

    INSERT INTO friendship (userId1, userId2)
    VALUES (user_id1, user_id2); 
END; 
$$ LANGUAGE plpgsql;

-- Tran08
CREATE OR REPLACE FUNCTION create_group(group_name TEXT, description TEXT, is_public BOOLEAN, owner_id INT) 
RETURNS VOID AS $$
BEGIN
    INSERT INTO "group" (groupName, description, is_public, ownerId)
    VALUES (group_name, description, is_public, owner_id);
END; 
$$ LANGUAGE plpgsql;


-- Tran09
CREATE OR REPLACE FUNCTION delete_user(user_id INT)
RETURNS VOID AS $$
BEGIN
    UPDATE comment 
    SET userId = NULL, 
        content = CONCAT('Deleted User: ', content) 
    WHERE userId = user_id; 

    UPDATE post 
    SET userId = NULL, 
        content = CONCAT('Deleted User Post: ', content) 
    WHERE userId = user_id;

    UPDATE saved_post 
    SET userId = NULL 
    WHERE userId = user_id;

    UPDATE friend_request 
    SET senderId = NULL 
    WHERE senderId = user_id;

    UPDATE friend_request 
    SET receiverId = NULL 
    WHERE receiverId = user_id;

    UPDATE group_member 
    SET user_id = NULL 
    WHERE user_id = user_id;

    UPDATE group_owner 
    SET user_id = NULL 
    WHERE user_id = user_id;

    DELETE FROM users
    WHERE id = user_id;
END; 
$$ LANGUAGE plpgsql;


-- Tran10
CREATE OR REPLACE FUNCTION save_post(user_id INT, post_id INT)
RETURNS VOID AS $$
BEGIN
    INSERT INTO saved_post (user_id, postId) 
    VALUES (user_id, post_id);
END; 
$$ LANGUAGE plpgsql;


-- Tran11
CREATE OR REPLACE FUNCTION remove_saved_post(user_id INT, post_id INT)
RETURNS VOID AS $$
BEGIN
    DELETE FROM saved_post 
    WHERE user_id = user_id AND postId = post_id;  
END; 
$$ LANGUAGE plpgsql;


-- Tran12
CREATE OR REPLACE FUNCTION request_to_join_group(group_id INT, user_id INT)
RETURNS VOID AS $$
DECLARE 
    groupOwnerId INT;
    notification_id INT;
BEGIN
    INSERT INTO join_group_request (group_id, user_id, request_status, requested_at)
    VALUES (group_id, user_id, 'pending', NOW());

    SELECT owner_id INTO groupOwnerId
    FROM group_
    WHERE group_id = group_id;

    INSERT INTO notification (content, is_read, notification_date, user_id)
    VALUES ('User ' || user_id || ' has requested to join your group.', FALSE, NOW(), groupOwnerId);

    notification_id := LASTVAL();

    INSERT INTO group_request_notification (notification_id, group_request_id)
    VALUES (notification_id, (SELECT MAX(request_id) FROM join_group_request WHERE group_id = group_id AND user_id = user_id));
END; 
$$ LANGUAGE plpgsql;



-- Tran13
CREATE OR REPLACE FUNCTION add_post(
    p_user_id INT,
    p_group_id INT,
    p_content TEXT
)
RETURNS VOID AS $$
DECLARE
    groupOwnerId INT;
    groupMemberIds INT[];
    new_post_id INT;
    member_id INT;  
BEGIN
    INSERT INTO post (user_id, group_id, content, post_date)
    VALUES (p_user_id, p_group_id, p_content, NOW())
    RETURNING post_id INTO new_post_id;

    SELECT owner_id INTO groupOwnerId
    FROM group_
    WHERE group_id = p_group_id;

    SELECT ARRAY_AGG(user_id) INTO groupMemberIds
    FROM group_member
    WHERE group_id = p_group_id;

    INSERT INTO notification (content, is_read, notification_date, user_id)
    VALUES ('User ' || p_user_id || ' has posted in your group.', FALSE, NOW(), groupOwnerId);
    
    FOREACH member_id IN ARRAY groupMemberIds LOOP
        INSERT INTO notification (content, is_read, notification_date, user_id)
        VALUES ('User ' || p_user_id || ' has posted in the group.', FALSE, NOW(), member_id);
        
        INSERT INTO group_post_notification (notification_id, post_id)
        VALUES (currval(pg_get_serial_sequence('notification', 'notification_id')), new_post_id);
    END LOOP;
END $$ LANGUAGE plpgsql;


-- Tran14
CREATE OR REPLACE FUNCTION accept_join_group_request(
    p_request_id INT  -- Declare request_id as a parameter
)
RETURNS VOID AS $$
DECLARE
    requesterId INT;
    groupId INT; 
    groupOwnerId INT;  
    notification_id INT; 
BEGIN
    -- Select user_id and group_id from the join_group_request table
    SELECT user_id, group_id INTO requesterId, groupId
    FROM join_group_request
    WHERE request_id = p_request_id;  -- Use the parameter instead

    -- Update the request status to accepted
    UPDATE join_group_request
    SET request_status = 'accepted'
    WHERE request_id = p_request_id;  -- Use the parameter instead

    -- Insert the new group member
    INSERT INTO group_member (user_id, group_id) 
    VALUES (requesterId, groupId);

    -- Get the group owner's ID
    SELECT owner_id INTO groupOwnerId
    FROM group_
    WHERE group_id = groupId;

    INSERT INTO notification (content, is_read, notification_date, user_id)
    VALUES ('Your request to join the group has been accepted.', FALSE, NOW(), requesterId);

    INSERT INTO notification (content, is_read, notification_date, user_id)
    VALUES ('User ' || requesterId || ' has joined your group.', FALSE, NOW(), groupOwnerId);

    notification_id := currval(pg_get_serial_sequence('notification', 'notification_id'));

 
    INSERT INTO group_request_notification (notification_id, request_id)
    VALUES (notification_id, p_request_id);
END $$ LANGUAGE plpgsql;



INSERT INTO users (username, email, profile_picture, password, is_public)
VALUES
    ('alice_wonder', 'alice@example.com', 'alice.jpg', '$2y$10$rX7CLGWOUaeAKP6ACma35.e9bVB5QqD5hLlUrU.nhxgdI2qWd9v7W', TRUE),
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
    (1, NULL, 'Just finished reading a fantastic book!', NULL, 'book1.jpg', NULL, TRUE, '2023-01-01 12:00:00'),
    (2, NULL, 'Building a new project, check it out!', 'construction.jpg', NULL, NULL, TRUE, '2023-01-02 12:01:00'),
    (3, NULL, 'Check out my latest painting!', NULL, 'artwork1.jpg', 'artwork2.jpg', TRUE, '2023-01-03 12:02:00'),
    (4, NULL, 'Excited about the new tech innovations!', NULL, 'tech1.jpg', NULL, TRUE, '2023-01-04 12:03:00'),
    (5, NULL, 'Lets solve some mysteries together!', NULL, NULL, NULL, TRUE, '2023-01-05 12:04:00'),
    (6, NULL, 'Adopting a new puppy today!', NULL, 'puppy.jpg', NULL, TRUE, '2023-01-06 12:05:00'),
    (7, NULL, 'Just got back from my trip to Italy!', NULL, NULL, 'italy.jpg', TRUE, '2023-01-07 12:06:00'),
    (8, NULL, 'Tried a new recipe today, it was delicious!', 'recipe.jpg', NULL, NULL, TRUE, '2023-01-08 12:07:00'),
    (9, NULL, 'Just finished a 5k run, feeling great!', NULL, 'run.jpg', NULL, TRUE, '2023-01-09 12:08:00'),
    (10, NULL, 'Discovering new music every day!', NULL, NULL, 'music.jpg', TRUE, '2023-01-10 12:09:00'),
    (1, NULL, 'Any recommendations for good books?', NULL, NULL, NULL, TRUE, '2023-01-11 12:10:00'),
    (2, NULL, 'New construction materials available!', 'materials.jpg', NULL, NULL, TRUE, '2023-01-12 12:11:00'),
    (3, NULL, 'Art competition coming up!', NULL, 'competition.jpg', NULL, TRUE, '2023-01-13 12:12:00'),
    (4, NULL, 'Latest gadget review is out!', NULL, NULL, 'gadget.jpg', TRUE, '2023-01-14 12:13:00'),
    (5, NULL, 'Share your best mystery story!', NULL, NULL, NULL, TRUE, '2023-01-15 12:14:00'),
    (6, NULL, 'What is your favorite pet?', NULL, NULL, NULL, TRUE, '2023-01-16 12:15:00'),
    (7, NULL, 'Looking for travel buddies!', NULL, NULL, NULL, TRUE, '2023-01-17 12:16:00'),
    (8, NULL, 'Food festival this weekend!', NULL, NULL, 'festival.jpg', TRUE, '2023-01-18 12:17:00'),
    (9, NULL, 'Join my fitness challenge!', NULL, NULL, NULL, TRUE, '2023-01-19 12:18:00'),
    (10, NULL, 'Music festival coming soon!', NULL, 'festival.jpg', NULL, TRUE, '2023-01-20 12:19:00'),
    (1, NULL, 'What construction project are you working on?', NULL, NULL, NULL, TRUE, '2023-01-21 12:20:00'),
    (2, NULL, 'Have you seen my latest drawing?', NULL, 'drawing.jpg', NULL, TRUE, '2023-01-22 12:21:00'),
    (3, NULL, 'What are your thoughts on AI?', NULL, NULL, NULL, TRUE, '2023-01-23 12:22:00'),
    (4, NULL, 'Anyone solved a mystery recently?', NULL, NULL, NULL, TRUE, '2023-01-24 12:23:00'),
    (5, NULL, 'Adopt, dont shop!', NULL, NULL, NULL, TRUE, '2023-01-25 12:24:00'),
    (6, NULL, 'Whats your favorite travel destination?', NULL, NULL, NULL, TRUE, '2023-01-26 12:25:00'),
    (7, NULL, 'What dish should I try next?', NULL, NULL, NULL, TRUE, '2023-01-27 12:26:00'),
    (8, NULL, 'Who wants to join me for a workout?', NULL, NULL, NULL, TRUE, '2023-01-28 12:27:00'),
    (9, NULL, 'Whats your go-to song for motivation?', NULL, NULL, NULL, TRUE, '2023-01-29 12:28:00');


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
