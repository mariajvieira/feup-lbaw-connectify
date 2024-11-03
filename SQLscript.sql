DROP TABLE IF EXISTS user_ CASCADE;
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

CREATE TABLE user_ (
    user_id SERIAL PRIMARY KEY,
    username TEXT UNIQUE NOT NULL,
    email TEXT NOT NULL,
    profile_picture TEXT,
    user_password TEXT NOT NULL,
    is_public BOOLEAN DEFAULT TRUE NOT NULL
);

CREATE TABLE administrator (
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id)
);

CREATE TABLE group_ (
    group_id SERIAL PRIMARY KEY,
    owner_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    group_name TEXT NOT NULL,
    description TEXT,
    visibility BOOLEAN NOT NULL,
    is_public BOOLEAN DEFAULT TRUE NOT NULL
);

CREATE TABLE post (
    post_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    group_id INT REFERENCES group_(group_id) ON UPDATE CASCADE,
    content TEXT,
    IMAGE1 TEXT,
    IMAGE2 TEXT,
    IMAGE3 TEXT,
    is_public BOOLEAN DEFAULT TRUE NOT NULL,
    post_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK ((content IS NOT NULL OR IMAGE1 IS NOT NULL) OR group_id IS NULL)
);

CREATE TABLE saved_post (
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    post_id INT NOT NULL REFERENCES post(post_id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id, post_id)
);

CREATE TABLE comment_ (
    id SERIAL PRIMARY KEY,
    post_id INT NOT NULL REFERENCES post(post_id) ON UPDATE CASCADE,
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    comment_content TEXT NOT NULL,
    commentDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reaction (
    reaction_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    post_id INT NOT NULL REFERENCES post(post_id) ON UPDATE CASCADE,
    reaction_type reactionType NOT NULL,
    reaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE friend_request (
    request_id SERIAL PRIMARY KEY,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    request_status statusFriendship_request NOT NULL,
    sender_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    receiver_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE
);

CREATE TABLE friendship (
    user_id1 INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    user_id2 INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id1, user_id2)
);

CREATE TABLE join_group_request (
    request_id SERIAL PRIMARY KEY,
    group_id INT NOT NULL REFERENCES group_(group_id) ON UPDATE CASCADE,
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    request_status statusGroup_request NOT NULL,
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE group_member (
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    group_id INT NOT NULL REFERENCES group_(group_id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id, group_id)
);

CREATE TABLE group_owner (
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    group_id INT NOT NULL REFERENCES group_(group_id) ON UPDATE CASCADE,
    PRIMARY KEY (user_id, group_id)
);

CREATE TABLE notification (
    notification_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES user_(user_id) ON UPDATE CASCADE,
    related_id INT,
    is_read BOOLEAN DEFAULT false,
    notification_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE comment_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(notification_id) ON UPDATE CASCADE,
    comment_id INT NOT NULL REFERENCES comment_(id) ON UPDATE CASCADE
);

CREATE TABLE reaction_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(notification_id) ON UPDATE CASCADE,
    reaction_id INT NOT NULL REFERENCES reaction(reaction_id) ON UPDATE CASCADE
);

CREATE TABLE friend_request_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(notification_id) ON UPDATE CASCADE,
    friend_request_id INT NOT NULL REFERENCES friend_request(request_id) ON UPDATE CASCADE
);

CREATE TABLE group_request_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(notification_id) ON UPDATE CASCADE,
    group_request_id INT NOT NULL REFERENCES join_group_request(request_id) ON UPDATE CASCADE
);

CREATE TABLE group_post_notification (
    notification_id INT PRIMARY KEY REFERENCES notification(notification_id) ON UPDATE CASCADE,
    post_id INT NOT NULL REFERENCES post(post_id) ON UPDATE CASCADE
);

-- Perfomance Indices

CREATE INDEX idx_user_posts ON post(user_id);
CLUSTER Post USING idx_user_posts;

CREATE INDEX idx_post_postdate ON post(post_date);
CREATE INDEX idx_notification_user_date ON notification (user_id, notification_date);

-- Full-text Search Indices

-- User FTS index
DROP FUNCTION IF EXISTS user_search_update() CASCADE;
ALTER TABLE user_
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
BEFORE INSERT OR UPDATE ON user_
FOR EACH ROW
EXECUTE PROCEDURE user_search_update();

CREATE INDEX idx_user_search ON user_ USING GIN (tsvectors);

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
BEFORE UPDATE ON user_
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
AFTER DELETE ON user_
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
    IF (SELECT is_public FROM group_ WHERE group_id = NEW.group_id) = FALSE THEN
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
    UPDATE user_ 
    SET username = newName, email = newEmail, profile_picture = newProfilePicture 
    WHERE user_id = userId;
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

    DELETE FROM "user" 
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
    member_id INT;  -- Declare member_id here
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