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
CREATE TYPE targetType AS ENUM('post', 'comment');

-- Tables

CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    username TEXT UNIQUE NOT NULL,
    email TEXT UNIQUE NOT NULL,
    profile_picture TEXT DEFAULT 'images/profile_pictures/default.png',
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

-- CREATE TABLE tagged_post (
--     user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE, -- id do user marcado
--     post_id INT NOT NULL REFERENCES posts(id) ON UPDATE CASCADE, 
--     -- tagged_by INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,  --id do user que marcou
--     PRIMARY KEY (user_id, post_id)
-- );

CREATE TABLE posts (
    user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE,
    post_id INT NOT NULL REFERENCES post(id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id, post_id)
);

CREATE TABLE tagged_post (
    user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE, --user marcado
    post_id INT NOT NULL REFERENCES post(id) ON UPDATE CASCADE, --post marcado
    tagged_by INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE, 
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, post_id,tagged_by)
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
    target_id INT NOT NULL, -- ID de um post ou comentário
    target_type targetType NOT NULL, --  'post' ou 'comment'
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
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT false,
    notification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT NOT NULL REFERENCES users(id) ON UPDATE CASCADE
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
DECLARE
    current_user_id INTEGER;
BEGIN
    -- Obter o user_id da tabela 'users' com base no 'username' (ou qualquer outro critério que você use para identificar o usuário)
    SELECT id INTO current_user_id
    FROM users
    WHERE username = NEW.username  -- Se você estiver usando 'username' para identificar o usuário
    LIMIT 1;

    IF NEW.password IS DISTINCT FROM OLD.password THEN
        IF NEW.is_public IS DISTINCT FROM OLD.is_public
            OR NEW.username IS DISTINCT FROM OLD.username
            OR NEW.email IS DISTINCT FROM OLD.email THEN
        ELSE
            RETURN NEW; 
        END IF;
    END IF;

    -- Permite que o usuário edite seu próprio perfil
    IF NEW.id = current_user_id THEN
        RAISE NOTICE 'User editing own profile. NEW.id: %, current_user_id: %', NEW.id, current_user_id;
        RETURN NEW; -- Permite a alteração
    ELSIF EXISTS (
        SELECT 1 
        FROM administrator
        WHERE user_id = current_user_id
    ) THEN
        RAISE NOTICE 'Administrator editing profile.';
        RETURN NEW; -- Permite a alteração
    END IF;

    -- Caso contrário, não permite a atualização
    RAISE EXCEPTION 'Only administrators and profile owner can edit this profile.';
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

    -- ELSE
    --     IF EXISTS (
    --         SELECT 1 
    --         FROM administrator
    --         WHERE user_id = current_user_id
    --     ) THEN
    --         RETURN NEW;
    --     END IF;
    -- END IF;


    -- IF NEW.is_public = FALSE THEN
    --     IF NOT EXISTS (
    --         SELECT 1 FROM friendship
    --         WHERE (user_id1 = NEW.id AND user_id2 = current_user_id) 
    --            OR (user_id2 = NEW.id AND user_id1 = current_user_id)
    --     ) AND NOT EXISTS (
    --         SELECT 1 FROM administrator
    --         WHERE user_id = current_user_id
    --     )
    --     THEN
    --         RAISE EXCEPTION 'Entrou na condição: o usuário está alterando o próprio perfil. NEW.id: %, current_user_id: %', NEW.id, current_user_id;
    --         -- RAISE EXCEPTION 'Perfil privado. Acesso negado.';
    --     END IF;
    -- END IF;
    -- IF EXISTS (
    --     SELECT 1 
    --     FROM administrator
    --     WHERE user_id = current_user_id
    -- ) THEN
    --     RETURN NEW;
    -- END IF;
    -- RAISE EXCEPTION 'Apenas o próprio usuário ou administradores podem alterar este perfil.';

    -- RETURN NULL;


CREATE TRIGGER trg_enforce_profile_visibility
BEFORE UPDATE ON users
FOR EACH ROW
EXECUTE FUNCTION enforce_profile_visibility_update();



-- TRIGGER02: Ensures users cannot send duplicate friend requests (BR02)
CREATE OR REPLACE FUNCTION enforce_friend_request_limit()
RETURNS TRIGGER AS $$
BEGIN
   IF EXISTS ( SELECT 1 FROM friend_request WHERE sender_id = NEW.sender_id AND receiver_id = NEW.receiver_id AND request_status NOT IN ('denied') ) THEN
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
    -- Verificar se já existe uma reação do mesmo usuário para o mesmo target (post ou comentário)
    IF EXISTS (
        SELECT 1 FROM reaction
        WHERE target_id = NEW.target_id
          AND target_type = NEW.target_type
          AND user_id = NEW.user_id
    ) THEN
        RAISE EXCEPTION 'User already reacted to this target.';
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
        IF NEW.request_status = 'pending' THEN
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


-- TRIGGER08: Validation of the reaction target (post or comment)
CREATE OR REPLACE FUNCTION validate_target_id()
RETURNS TRIGGER AS $$
BEGIN
    IF (NEW.target_type = 'post') THEN
        IF NOT EXISTS (SELECT 1 FROM post WHERE id = NEW.target_id) THEN
            RAISE EXCEPTION 'Target ID % not found in post table.', NEW.target_id;
        END IF;
    ELSIF (NEW.target_type = 'comment') THEN
        IF NOT EXISTS (SELECT 1 FROM comment_ WHERE id = NEW.target_id) THEN
            RAISE EXCEPTION 'Target ID % not found in comment table.', NEW.target_id;
        END IF;
    ELSE
        RAISE EXCEPTION 'Invalid target type: %', NEW.target_type;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_validate_target_id
BEFORE INSERT OR UPDATE ON reaction
FOR EACH ROW
EXECUTE FUNCTION validate_target_id();



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
CREATE OR REPLACE FUNCTION add_reaction(targetId INT, userId INT, targetType targetType, reactionType reactionType) 
RETURNS VOID AS $$
DECLARE
    postOwnerId INT;
    reactionId INT;
BEGIN
    -- Verificar se o usuário já reagiu ao post ou comentário
    IF EXISTS (
        SELECT 1 FROM reaction
        WHERE target_id = targetId
          AND target_type = targetType
          AND user_id = userId
    ) THEN
        RAISE EXCEPTION 'User already reacted to this target.';
    END IF;

    -- Obter o proprietário do post ou comentário
    IF targetType = 'post' THEN
        SELECT user_id INTO postOwnerId
        FROM post
        WHERE id = targetId;
    ELSIF targetType = 'comment' THEN
        SELECT user_id INTO postOwnerId
        FROM comment_
        WHERE id = targetId;
    END IF;

    -- Inserir a reação
    INSERT INTO reaction (reaction_type, reaction_date, target_id, target_type, user_id) 
    VALUES (reactionType, NOW(), targetId, targetType, userId)
    RETURNING id INTO reactionId;

    -- Criar a notificação
    INSERT INTO notification (content, is_read, notification_date, user_id)
    VALUES ('User ' || userId || ' reacted to your ' || targetType || ' with ' || reactionType, FALSE, NOW(), postOwnerId);
    
    -- Relacionar a notificação com a reação
    INSERT INTO reaction_notification (notification_id, reaction_id)
    VALUES (currval(pg_get_serial_sequence('notification', 'id')), reactionId);
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
    p_request_id INT  
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
    ('alice_wonder', 'alice@example.com', 'images/profile_pictures/alice.jpg', '$2y$10$rX7CLGWOUaeAKP6ACma35.e9bVB5QqD5hLlUrU.nhxgdI2qWd9v7W', TRUE),
    ('bob_builder', 'bob@example.com', 'images/profile_pictures/bob.jpg', '$2y$10$0xP8NZro/7udYYA0IA8Zhey919ccCDwUjSsj7ulYJlXpUXsSJ306G', TRUE),
    ('charlie_chaplin', 'charlie@example.com', DEFAULT, 'securepassword3', FALSE),
    ('daisy_duck', 'daisy@example.com', 'images/profile_pictures/daisy.jpg', 'securepassword4', TRUE),
    ('edgar_allan', 'edgar@example.com', 'images/profile_pictures/edgar.jpg', 'securepassword5', TRUE),
    ('fiona_fairy', 'fiona@example.com', DEFAULT, 'securepassword6', FALSE),
    ('george_gremlin', 'george@example.com', 'images/profile_pictures/george.jpg', 'securepassword7', TRUE),
    ('hannah_hacker', 'hannah@example.com', DEFAULT, 'securepassword8', TRUE),
    ('ian_icecream', 'ian@example.com', 'images/profile_pictures/ian.jpg', 'securepassword9', TRUE),
    ('jessica_jones', 'jessica@example.com', DEFAULT, 'securepassword10', FALSE),
    ('karl_kong', 'karl@example.com', 'images/profile_pictures/karl.jpg', 'securepassword11', TRUE),
    ('linda_lion', 'linda@example.com', 'images/profile_pictures/linda.jpg', 'securepassword12', TRUE),
    ('mike_mouse', 'mike@example.com', DEFAULT, 'securepassword13', TRUE),
    ('nina_ninja', 'nina@example.com', DEFAULT, 'securepassword14', FALSE),
    ('oliver_orange', 'oliver@example.com', DEFAULT, 'securepassword15', TRUE),
    ('peter_panda', 'peter@example.com', 'images/profile_pictures/peter.jpg', 'securepassword16', TRUE),
    ('quincy_quokka', 'quincy@example.com', DEFAULT, 'securepassword17', TRUE),
    ('rose_rabbit', 'rose@example.com', 'images/profile_pictures/rose.jpg', 'securepassword18', TRUE),
    ('sara_sparrow', 'sara@example.com', 'images/profile_pictures/sara.jpg', 'securepassword19', FALSE),
    ('tom_tiger', 'tom@example.com', DEFAULT, 'securepassword20', TRUE),
    ('uma_unicorn', 'uma@example.com', DEFAULT, 'securepassword21', TRUE),
    ('vicky_vulture', 'vicky@example.com', DEFAULT, 'securepassword22', TRUE),
    ('will_walrus', 'will@example.com', 'images/profile_pictures/will.jpg', 'securepassword23', TRUE),
    ('xena_xerus', 'xena@example.com', 'images/profile_pictures/xena.jpg', 'securepassword24', TRUE),
    ('yara_yeti', 'yara@example.com', DEFAULT, 'securepassword25', TRUE),
    ('zach_zebra', 'zach@example.com', 'images/profile_pictures/zach.jpg', 'securepassword26', FALSE),
    ('arnold_alligator', 'arnold@example.com', DEFAULT, 'securepassword27', TRUE),
    ('bianca_butterfly', 'bianca@example.com', DEFAULT, 'securepassword28', TRUE),
    ('clara_cat', 'clara@example.com', DEFAULT, 'securepassword29', TRUE),
    ('david_dog', 'david@example.com', DEFAULT, 'securepassword30', TRUE),
    ('elaine_emu', 'elaine@example.com', DEFAULT, 'securepassword31', TRUE),
    ('frank_frog', 'frank@example.com', DEFAULT, 'securepassword32', TRUE),
    ('gina_goose', 'gina@example.com', DEFAULT, 'securepassword33', TRUE),
    ('harry_hedgehog', 'harry@example.com', DEFAULT, 'securepassword34', TRUE),
    ('irene_ibis', 'irene@example.com', DEFAULT, 'securepassword35', TRUE),
    ('john_jellyfish', 'john@example.com', DEFAULT, 'securepassword36', TRUE),
    ('kelly_kangaroo', 'kelly@example.com', DEFAULT, 'securepassword37', TRUE),
    ('leo_leopard', 'leo@example.com', DEFAULT, 'securepassword38', TRUE),
    ('mona_monkey', 'mona@example.com', DEFAULT, 'securepassword39', TRUE),
    ('nora_narwhal', 'nora@example.com', DEFAULT, 'securepassword40', TRUE),
    ('olga_octopus', 'olga@example.com', DEFAULT, 'securepassword41', TRUE),
    ('paul_parrot', 'paul@example.com', DEFAULT, 'securepassword42', TRUE),
    ('quinn_quail', 'quinn@example.com', DEFAULT, 'securepassword43', TRUE),
    ('rachel_raccoon', 'rachel@example.com',DEFAULT, 'securepassword44', TRUE),
    ('sammy_seal', 'sammy@example.com', DEFAULT, 'securepassword45', TRUE),
    ('tina_tortoise', 'tina@example.com', DEFAULT, 'securepassword46', TRUE),
    ('ursula_unicorn', 'ursula@example.com', DEFAULT, 'securepassword47', TRUE),
    ('vince_viper', 'vince@example.com',DEFAULT, 'securepassword48', TRUE),
    ('willow_wolf', 'willow@example.com', DEFAULT, 'securepassword49', TRUE),
    ('xander_xerus', 'xander@example.com', DEFAULT, 'securepassword50', TRUE);

INSERT INTO administrator (user_id)
VALUES
    (1), (3), (5), (9), (10), (18);

INSERT INTO group_ (owner_id, group_name, description, is_public)
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
    (1, NULL, 'Just finished reading a fantastic book!','images/1.1.jpg', NULL, NULL, TRUE, '2023-01-01 12:00:00'),
    (2, NULL, 'Building a new project, check it out!', 'images/2.1.jpg', NULL, NULL, TRUE, '2023-01-02 12:01:00'),
    (3, NULL, 'Check out my latest painting!', 'images/3.1.jpg', 'images/3.2.jpg',  NULL,TRUE, '2023-01-03 12:02:00'),
    (4, NULL, 'Paris!', 'images/4.1.jpg', NULL, NULL, TRUE, '2023-01-04 12:03:00'),
    (5, NULL, 'Did you know that octopuses have three hearts? Two pump blood to the gills, and one pumps it to the rest of the body. What’s even more fascinating is that the heart that supplies the body stops beating when the octopus swims! Nature is incredible, isn’t it?', NULL, NULL, NULL, TRUE, '2023-01-05 12:04:00'),
    (6, NULL, 'New family member!', 'images/6.1.jpg', NULL, NULL, TRUE, '2023-01-06 12:05:00'),
    (7, NULL, 'Just got back from my trip to Italy!', 'images/7.1.jpg', NULL, NULL, TRUE, '2023-01-07 12:06:00'),
    (8, NULL, 'Tried a new recipe today, it was delicious!', 'images/8.1.jpg', NULL, NULL, TRUE, '2023-01-08 12:07:00'),
    (9, NULL, 'Just finished a 5k run, feeling great!', 'images/9.1.jpg', NULL, NULL, TRUE, '2023-01-09 12:08:00'),
    (10, NULL, 'I love listening to music!', 'images/10.1.jpg', NULL, NULL, TRUE, '2023-01-10 12:09:00'),
    (1, NULL, 'Any recommendations for good books?', NULL, NULL, NULL, TRUE, '2023-01-11 12:10:00'),
    (2, NULL, 'Love building stuff together!', 'images/12.1.jpg', NULL, NULL, TRUE, '2023-01-12 12:11:00'),
    (3, NULL, 'Art online class coming up!', 'images/13.1.jpg', NULL, NULL, TRUE, '2023-01-13 12:12:00'),
    (4, NULL, NULL , 'images/14.1.jpg', NULL, NULL, TRUE, '2023-01-14 12:13:00'),
    (5, NULL, 'Amazing coffe with an amazing view', 'images/15.1.jpg', NULL, NULL, TRUE, '2023-01-15 12:14:00'),
    (6, NULL, 'Happy birthday mom!', 'images/16.1.jpg', NULL, NULL, TRUE, '2023-01-16 12:15:00'),
    (7, NULL, NULL, 'images/17.1.jpg', NULL, NULL, TRUE, '2023-01-17 12:16:00'),
    (8, NULL, 'Food festival this weekend!', 'images/18.1.jpg', 'images/18.2.jpg', NULL, TRUE, '2023-01-18 12:17:00'),
    (9, NULL, 'Join my fitness challenge!', 'images/19.1.jpg', NULL, NULL, TRUE, '2023-01-19 12:18:00'),
    (10, NULL, 'Rock n roll!!!','images/20.1.jpg', NULL, NULL, TRUE, '2023-01-20 12:19:00'),
    (1, NULL, 'Finally got my first job as a software engineer! Wish me luck :)', NULL, NULL, NULL, TRUE, '2023-01-21 12:20:00'),
    (2, NULL, 'First day at college','images/22.1.jpg', NULL, NULL, TRUE, '2023-01-22 12:21:00'),
    (3, NULL, NULL,'images/23.1.jpg', NULL, NULL, TRUE, '2023-01-23 12:22:00'),
    (4, NULL, 'Today in Porto', 'images/24.1.jpg', NULL, NULL, TRUE, '2023-01-24 12:23:00'),
    (5, NULL, 'Adopt, dont shop!', 'images/25.1.jpg', NULL, NULL, TRUE, '2023-01-25 12:24:00'),
    (6, NULL, 'Whats your favorite travel destination? I`ve been to some cities all over the world but no city has the same fun and vibe as Lisbon!', NULL, NULL, NULL, TRUE, '2023-01-26 12:25:00'),
    (7, NULL, 'Family time :)', 'images/27.1.jpg', NULL, NULL, TRUE, '2023-01-27 12:26:00'),
    (8, NULL, 'Who wants to join me for a workout?', 'images/28.1.jpg', NULL, NULL, TRUE, '2023-01-28 12:27:00'),
    (9, NULL, 'Whats your favourite song? I love Despacito', 'images/29.1.jpg', NULL, NULL, TRUE, '2023-01-29 12:28:00');


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
    (1, 7, 'My favourite!'),
    (1, 4, 'Glad you followed my recommendation'),
    (2, 1, 'That looks amazing!'),
    (2, 15, 'Can’t wait to see the final result!'),
    (2, 5, 'I love working on projects like this with you!'),
    (3, 4, 'Your art is inspiring!'),
    (3, 10, 'So cool!'),
    (3, 6, 'Art is truly a reflection of the soul!'),
    (4, 5, 'LOVE!'),
    (4, 7, 'wow, in the city of romance'),
    (5, 6, 'I love fun facts!'),
    (5, 8, 'So interesting!'),
    (6, 7, 'What a cute puppy!'),
    (6, 9, 'Dogs are the best companions!'),
    (7, 10, 'Traveling is such a rewarding experience!'),
    (7, 8, 'Italy is wonderful!'),
    (8, 9, 'That recipe sounds delicious!'),
    (8, 2, 'I have to try it!'),
    (8, 1, 'Food is an art form in itself!'),
    (9, 2, 'Congrats!'),
    (9, 10, 'Running is so rewarding!'),
    (10, 1, 'What’s your favoutite song!'),
    (10, 3, 'I love discovering new music!'),
    (11, 4, 'Any from Nicholas Spark is great!'),
    (11, 9, 'Little women!'),
    (13, 6, 'I’ll be there!'),
    (14, 7, 'Love it'),
    (15, 8, 'I’m jealous!!!'),
    (16, 9, 'Happy birthday <3'),
    (17, 10, 'wow'),
    (18, 1, 'Food is an art form in itself!'),
    (19, 2, 'Fitness is a journey, not a destination!'),
    (20, 3, 'omg i was there too!'),
    (21, 4, 'Congratulations!!!!'),
    (22, 5, 'So nice to meet youu'),
    (24, 7, 'So beautiful!'),
    (25, 8, 'That’s right!!'),
    (26, 9, 'Dubai and NY!!!!'),
    (26, 1, 'I agree, London is the best!'),
    (27, 10, 'Family goals!'),
    (28, 1, 'Not me xD'),
    (29, 2, 'Great song!'),
    (29, 30, 'My favourite is Hey Jude!');



INSERT INTO reaction (user_id, target_id, target_type, reaction_type, reaction_date)
VALUES
    (1, 1, 'post', 'like', '2023-01-30 10:00:00'),
    (2, 2, 'post', 'laugh', '2023-01-31 10:00:00'),
    (3, 3, 'post', 'applause', '2023-02-01 10:00:00'),
    (4, 4, 'post', 'like', '2023-02-02 10:00:00'),
    (5, 5, 'post', 'applause', '2023-02-03 10:00:00'),
    (6, 6, 'post', 'like', '2023-02-04 10:00:00'),
    (7, 7, 'post', 'applause', '2023-02-05 10:00:00'),
    (8, 8, 'post', 'like', '2023-02-06 10:00:00'),
    (9, 9, 'post', 'like', '2023-02-07 10:00:00'),
    (10, 10, 'post', 'applause', '2023-02-08 10:00:00'),
    (1, 2, 'comment', 'shocked', '2023-02-09 10:00:00'),
    (2, 3, 'comment', 'like', '2023-02-10 10:00:00'),
    (3, 4, 'comment', 'like', '2023-02-11 10:00:00'),
    (4, 5, 'comment', 'shocked', '2023-02-12 10:00:00'),
    (5, 6, 'comment', 'like', '2023-02-13 10:00:00'),
    (6, 7, 'comment', 'shocked', '2023-02-14 10:00:00'),
    (7, 8, 'comment', 'like', '2023-02-15 10:00:00'),
    (8, 9, 'comment', 'shocked', '2023-02-16 10:00:00'),
    (9, 10, 'comment', 'like', '2023-02-17 10:00:00'),
    (10, 1, 'comment', 'shocked', '2023-02-18 10:00:00');




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
    (1,2),
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

INSERT INTO notification (content, is_read, notification_date, user_id)
VALUES
    ('Comment', FALSE, DEFAULT, 1),
    ('Reaction', FALSE, DEFAULT, 2),
    ('Friend Request', FALSE, DEFAULT, 3),
    ('Group Request', FALSE, DEFAULT, 4),
    ('Group Post', FALSE, DEFAULT, 5),
    ('Comment', FALSE, DEFAULT, 6),
    ('Reaction', FALSE, DEFAULT, 7),
    ('Friend Request', FALSE, DEFAULT, 8),
    ('Group Request', FALSE, DEFAULT, 9),
    ('Group Post', FALSE, DEFAULT, 10),
    ('Comment', FALSE, DEFAULT, 1),
    ('Reaction', FALSE, DEFAULT, 2),
    ('Friend Request', FALSE, DEFAULT, 3),
    ('Group Request', FALSE, DEFAULT, 4),
    ('Group Post', FALSE, DEFAULT, 5),
    ('Comment', FALSE, DEFAULT, 6),
    ('Reaction', FALSE, DEFAULT, 7),
    ('Friend Request', FALSE, DEFAULT, 8),
    ('Group Request', FALSE, DEFAULT, 9),
    ('Group Post', FALSE, DEFAULT, 10);


INSERT INTO comment_notification (notification_id, comment_id)
VALUES
    (1, 1),
    (6, 2),
    (11, 3),
    (16, 4);

INSERT INTO reaction_notification (notification_id, reaction_id)
VALUES
    (2, 1),
    (7, 2),
    (12, 3),
    (17, 4);

INSERT INTO friend_request_notification (notification_id, friend_request_id)
VALUES
    (3, 1),
    (8, 2),
    (13, 3),
    (18, 4);

INSERT INTO group_request_notification (notification_id, group_request_id)
VALUES
    (4, 1),
    (9, 2),
    (14, 3),
    (19, 4);

INSERT INTO group_post_notification (notification_id, post_id)
VALUES
    (5, 1),
    (10, 2),
    (15, 3),
    (20, 4);

-- INSERT INTO tagged_posts (user_id, post_id, tagged_by, created_at)
--  VALUES
-- (2, 1, 1), 
-- (3, 1, 1), 
-- (1, 2, 2); 