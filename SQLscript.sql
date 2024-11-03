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

CREATE TYPE statusGroup_request AS ENUM ('requested', 'accepted', 'denied');
CREATE TYPE statusFriendship_request AS ENUM ('requested', 'accepted', 'denied');
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
    CHECK (content IS NOT NULL OR IMAGE1 IS NOT NULL)
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
    reactionType reactionType NOT NULL,
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

CREATE INDEX idx_user_posts ON Post(user_id);
CREATE INDEX idx_post_postdate ON post(postDate);
CREATE INDEX idx_notification_user_date ON notification (userId, notificationDate);

-- Full-text Search Indices

-- User FTS index
ALTER TABLE user_
ADD COLUMN tsvectors TSVECTOR;

CREATE FUNCTION user_search_update() RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN 
        NEW.tsvectors = to_tsvector('portuguese', NEW.username);
    END IF;
    IF TG_OP = 'UPDATE' THEN 
        IF (NEW.username <> OLD.username) THEN
            NEW.tsvectors = to_tsvector('portuguese', NEW.username); 
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
ALTER TABLE post
ADD COLUMN tsvectors TSVECTOR;

CREATE FUNCTION post_search_update() RETURNS TRIGGER AS $$
BEGIN
  IF TG_OP = 'INSERT' THEN 
    NEW.tsvectors = to_tsvector('portuguese', NEW.content);
  END IF
  IF TG_OP = 'UPDATE' THEN 
    IF (NEW.content <> OLD.content) THEN
      NEW.tsvectors = to_tsvector('portuguese', NEW.content); 
    END IF;
  END IF;
  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER post_search_update
BEFORE INSERT OR UPDATE ON post
FOR EACH ROW
EXECUTE PROCEDURE post_search_update();

CREATE INDEX idx_post_content ON post USING GIN (tsvectors);

-- Group FTS index
ALTER TABLE group_
ADD COLUMN tsvectors TSVECTOR;

CREATE FUNCTION group_search_update() RETURNS TRIGGER AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN 
      NEW.tsvectors = (
        setweight(to_tsvector('portuguese', NEW.groupName), 'A') ||
        setweight(to_tsvector('portuguese', NEW.description), 'B')
      );
    END IF
    IF TG_OP = 'UPDATE' THEN
      IF (NEW.groupName <> OLD.groupName OR NEW.description <> OLD.description) THEN
        NEW.tsvectors = (
          setweight(to_tsvector('portuguese', NEW.groupName), 'A') ||
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
EXECUTE PROCEDURE group_search_update();

CREATE INDEX idx_group_description ON group_ USING GIN (tsvectors);

-- Triggers 

-- TRIGGER01: Enforces that only approved friends can view private profiles (BR01, BR07)
CREATE OR REPLACE FUNCTION enforce_profile_visibility()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.isPublic = FALSE THEN
        IF NOT EXISTS (
            SELECT 1 FROM friendship
            WHERE (userId1 = NEW.id AND userId2 = current_user)
               OR (userId2 = NEW.id AND userId1 = current_user)
        ) THEN
            RAISE EXCEPTION 'Private profile. Access denied.';
        END IF;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_enforce_profile_visibility
BEFORE SELECT ON user
FOR EACH ROW
EXECUTE FUNCTION enforce_profile_visibility();


-- TRIGGER02: Ensures users cannot send duplicate friend requests (BR02)
CREATE OR REPLACE FUNCTION enforce_friend_request_limit()
RETURNS TRIGGER AS $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM friend_request
        WHERE senderId = NEW.senderId
          AND receiverId = NEW.receiverId
          AND requestStatus NOT IN ('Declined', 'Canceled')
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
        WHERE postId = NEW.postId
          AND userId = NEW.userId
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
    UPDATE comment SET userId = NULL WHERE userId = OLD.id;
    UPDATE reaction SET userId = NULL WHERE userId = OLD.id;
    UPDATE post SET userId = NULL WHERE userId = OLD.id;
    RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_anonymize_user_data
AFTER DELETE ON user
FOR EACH ROW
EXECUTE FUNCTION anonymize_user_data();


-- TRIGGER06: Ensures users can only post in groups they belong to (BR11)
CREATE OR REPLACE FUNCTION enforce_group_posting()
RETURNS TRIGGER AS $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM group_member
        WHERE groupId = NEW.groupId AND userId = NEW.userId
    ) THEN
        RAISE EXCEPTION 'User must be a member of the group to post.';
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
    IF (SELECT isPublic FROM group WHERE id = NEW.groupId) = FALSE THEN
        IF NEW.requestStatus = 'Pending' THEN
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

